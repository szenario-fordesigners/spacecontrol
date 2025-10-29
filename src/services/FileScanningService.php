<?php

namespace szenario\craftspacecontrol\services;

use Craft;
use szenario\craftspacecontrol\records\FileSizeRecord;
use yii\db\Expression;

/**
 * File scanning service for managing file size tracking
 */
class FileScanningService
{
    /**
     * Scan files and sync with database
     * 
     * @param string $basePath Base path to scan
     * @param int $chunkSize Number of files to process per batch
     * @return array Statistics about the scan
     */
    public function scanAndSyncFiles(string $basePath, int $chunkSize = 1000): array
    {
        $startTime = microtime(true);
        $scannedFiles = [];
        $totalSize = 0;

        // Scan all files
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($basePath, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        $now = new \DateTime();
        $scannedPathHashes = [];

        foreach ($iterator as $file) {
            // Skip symlinks to prevent infinite loops
            if ($file->isLink()) {
                continue;
            }

            $path = $file->getRealPath();
            if ($path === false) {
                continue;
            }

            // Skip directories
            if ($file->isDir()) {
                continue;
            }

            $stats = stat($path);
            if ($stats === false) {
                continue;
            }

            $pathHash = hash('sha256', $path);
            $fileSize = $stats['size'] ?? $file->getSize();

            $scannedFiles[] = [
                'pathHash' => $pathHash,
                'path' => $path,
                'sizeInBytes' => $fileSize,
                'mtime' => $stats['mtime'] ?? 0,
                'lastChecked' => $now->format('Y-m-d H:i:s'),
            ];

            $scannedPathHashes[] = $pathHash;
            $totalSize += $fileSize;

            // Process in chunks to avoid memory issues
            if (count($scannedFiles) >= $chunkSize) {
                $this->batchUpsertFiles($scannedFiles);
                $scannedFiles = [];
            }
        }

        // Process remaining files
        if (!empty($scannedFiles)) {
            $this->batchUpsertFiles($scannedFiles);
        }

        // Clean up deleted files
        $deletedCount = $this->cleanupDeletedFiles($scannedPathHashes);

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        return [
            'totalFiles' => count($scannedPathHashes),
            'totalSize' => $totalSize,
            'deletedFiles' => $deletedCount,
            'duration' => $duration,
        ];
    }

    /**
     * Batch upsert files to database using direct SQL for performance
     * 
     * @param array $files Array of file data to upsert
     */
    private function batchUpsertFiles(array $files): void
    {
        if (empty($files)) {
            return;
        }

        $db = Craft::$app->getDb();
        $tableName = FileSizeRecord::tableName();
        $now = new Expression('NOW()');

        // Get existing path hashes for this batch to determine inserts vs updates
        $pathHashes = array_column($files, 'pathHash');
        $existingHashes = FileSizeRecord::find()
            ->select('pathHash')
            ->where(['in', 'pathHash', $pathHashes])
            ->column();

        $existingHashesSet = array_flip($existingHashes);

        // Separate inserts and updates
        $inserts = [];
        $updates = [];

        foreach ($files as $file) {
            $fileData = [
                'pathHash' => $file['pathHash'],
                'path' => $file['path'],
                'sizeInBytes' => $file['sizeInBytes'],
                'mtime' => $file['mtime'] ?? null,
                'lastChecked' => $file['lastChecked'],
                'dateCreated' => $now,
                'dateUpdated' => $now,
            ];

            if (isset($existingHashesSet[$file['pathHash']])) {
                // Update existing record
                $updates[] = $fileData;
            } else {
                // Insert new record
                $inserts[] = $fileData;
            }
        }

        // Batch insert new records
        if (!empty($inserts)) {
            $db->createCommand()
                ->batchInsert($tableName, [
                    'pathHash',
                    'path',
                    'sizeInBytes',
                    'mtime',
                    'lastChecked',
                    'dateCreated',
                    'dateUpdated'
                ], $inserts)
                ->execute();
        }

        // Batch update existing records
        if (!empty($updates)) {
            foreach ($updates as $updateData) {
                $db->createCommand()
                    ->update($tableName, [
                        'path' => $updateData['path'],
                        'sizeInBytes' => $updateData['sizeInBytes'],
                        'mtime' => $updateData['mtime'],
                        'lastChecked' => $updateData['lastChecked'],
                        'dateUpdated' => $updateData['dateUpdated'],
                    ], ['pathHash' => $updateData['pathHash']])
                    ->execute();
            }
        }
    }

    /**
     * Clean up files that no longer exist
     * 
     * @param array $scannedPathHashes Array of path hashes from current scan
     * @return int Number of deleted records
     */
    private function cleanupDeletedFiles(array $scannedPathHashes): int
    {
        if (empty($scannedPathHashes)) {
            // If no files scanned, delete all records (likely a fresh scan or error)
            return FileSizeRecord::deleteAll();
        }

        // For large datasets, delete in chunks to avoid memory issues
        $chunkSize = 10000;
        $totalDeleted = 0;

        // Get all existing path hashes from database
        $existingHashes = FileSizeRecord::find()
            ->select('pathHash')
            ->column();

        // Find hashes that exist in DB but not in scanned files
        $hashesToDelete = array_diff($existingHashes, $scannedPathHashes);

        if (empty($hashesToDelete)) {
            return 0;
        }

        // Delete in chunks using direct SQL
        $chunks = array_chunk($hashesToDelete, $chunkSize);
        $db = Craft::$app->getDb();
        $tableName = FileSizeRecord::tableName();

        foreach ($chunks as $chunk) {
            $deleted = $db->createCommand()
                ->delete($tableName, ['in', 'pathHash', $chunk])
                ->execute();
            $totalDeleted += $deleted;
        }

        return $totalDeleted;
    }

    /**
     * Get total file count from database
     */
    public function getTotalFileCount(): int
    {
        return (int) Craft::$app->getDb()
            ->createCommand()
            ->select('COUNT(*)')
            ->from(FileSizeRecord::tableName())
            ->queryScalar();
    }

    /**
     * Get total size from database
     */
    public function getTotalSize(): int
    {
        return (int) Craft::$app->getDb()
            ->createCommand()
            ->select('SUM(sizeInBytes)')
            ->from(FileSizeRecord::tableName())
            ->queryScalar() ?: 0;
    }

    /**
     * Get files by path pattern
     */
    public function getFilesByPath(string $pattern): array
    {
        return FileSizeRecord::find()
            ->where(['like', 'path', $pattern])
            ->all();
    }

    /**
     * Get largest files
     */
    public function getLargestFiles(int $limit = 10): array
    {
        return FileSizeRecord::find()
            ->orderBy(['sizeInBytes' => SORT_DESC])
            ->limit($limit)
            ->all();
    }
}
