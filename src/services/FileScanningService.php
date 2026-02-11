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
        $fileCount = 0;

        // Scan all files
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($basePath, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        // This timestamp acts as our "Mark"
        $now = new \DateTime();

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

            $totalSize += $fileSize;
            $fileCount++;

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

        // Clean up deleted files using the "Sweep" strategy (Timestamp based)
        $deletedCount = $this->cleanupDeletedFiles($now, $basePath);

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        return [
            'totalFiles' => $fileCount,
            'totalSize' => $totalSize,
            'deletedFiles' => $deletedCount,
            'duration' => $duration,
        ];
    }

    /**
     * Batch upsert files to database with optimization for unchanged files
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

        // 1. Get existing records to determine what needs to be done
        $pathHashes = array_column($files, 'pathHash');
        $existingRecords = FileSizeRecord::find()
            ->select(['pathHash', 'mtime'])
            ->where(['in', 'pathHash', $pathHashes])
            ->asArray()
            ->all();

        // Index by hash for fast lookup
        $existingMap = [];
        foreach ($existingRecords as $record) {
            $existingMap[$record['pathHash']] = (int) $record['mtime'];
        }

        $inserts = [];
        $updates = [];
        $touchOnlyHashes = [];

        foreach ($files as $file) {
            $hash = $file['pathHash'];

            if (!isset($existingMap[$hash])) {
                // New file -> Insert
                $inserts[] = [
                    $file['pathHash'],
                    $file['path'],
                    $file['sizeInBytes'],
                    $file['mtime'] ?? 0,
                    $file['lastChecked'],
                    new Expression('NOW()'),
                    new Expression('NOW()'),
                ];
            } else {
                // Existing file
                $existingMtime = $existingMap[$hash];
                $newMtime = (int) ($file['mtime'] ?? 0);

                if ($existingMtime === $newMtime) {
                    // File hasn't changed -> Just update lastChecked (Batchable)
                    $touchOnlyHashes[] = $hash;
                } else {
                    // File changed -> Full Update needed
                    $updates[] = $file;
                }
            }
        }

        // 2. Perform Batch Insert
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

        // 3. Perform Batch "Touch" (Update lastChecked only)
        if (!empty($touchOnlyHashes)) {
            // We can update all these in ONE query
            $db->createCommand()
                ->update(
                    $tableName,
                    ['lastChecked' => $files[0]['lastChecked']], // Use current batch timestamp
                    ['in', 'pathHash', $touchOnlyHashes]
                )
                ->execute();
        }

        // 4. Perform Full Updates (Only for actually modified files)
        if (!empty($updates)) {
            foreach ($updates as $file) {
                $db->createCommand()
                    ->update($tableName, [
                        'path' => $file['path'],
                        'sizeInBytes' => $file['sizeInBytes'],
                        'mtime' => $file['mtime'] ?? 0,
                        'lastChecked' => $file['lastChecked'],
                        'dateUpdated' => new Expression('NOW()'),
                    ], ['pathHash' => $file['pathHash']])
                    ->execute();
            }
        }
    }

    /**
     * Clean up files that no longer exist using Mark and Sweep
     * 
     * @param \DateTime $scanTime The timestamp of the current scan
     * @param string $basePath The base path that was scanned
     * @return int Number of deleted records
     */
    private function cleanupDeletedFiles(\DateTime $scanTime, string $basePath): int
    {
        $tableName = FileSizeRecord::tableName();

        // Ensure base path ends with a directory separator for the LIKE query
        $basePath = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $formattedTime = $scanTime->format('Y-m-d H:i:s');

        // Delete files within the scanned path that have an older timestamp
        // (meaning they were not found/updated in the current scan)
        return Craft::$app->getDb()->createCommand()
            ->delete($tableName, [
                'and',
                ['<', 'lastChecked', $formattedTime],
                ['like', 'path', $basePath . '%']
            ])
            ->execute();
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
