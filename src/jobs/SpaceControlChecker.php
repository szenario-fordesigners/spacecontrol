<?php

namespace szenario\craftspacecontrol\jobs;

use Craft;
use szenario\craftspacecontrol\helpers\FolderSizeHelper;
use szenario\craftspacecontrol\helpers\SettingsHelper;
use szenario\craftspacecontrol\helpers\DatabaseSizeHelper;
use szenario\craftspacecontrol\NotificationService\NotificationService;
use szenario\craftspacecontrol\services\FileScanningService;

class SpaceControlChecker extends \craft\queue\BaseJob implements \yii\queue\RetryableJobInterface
{
    public bool $skipThrottling = false;

    public function getTtr()
    {
        // Max execution time of 60 seconds to handle large directories
        return 60;
    }

    public function canRetry($attempt, $error)
    {
        // 2 retries
        return ($attempt < 2);
    }
    public function execute($queue): void
    {
        self::calculateDiskUsage($this->skipThrottling);

        NotificationService::start();
    }

    public static function executeImmediately(): void
    {
        self::calculateDiskUsage(true); // Force calculation

        NotificationService::start();
    }

    // 1. get current disk usage
    // 2. save to setting
    private static function calculateDiskUsage(bool $force = false)
    {
        // Check for potential symlink issues
        $rootPath = Craft::getAlias('@root');
        if (is_link($rootPath)) {
            Craft::warning('Root path is a symlink, skipping disk usage calculation to prevent deadlock', 'spacecontrol');
            return;
        }

        $addDatabaseToTotalSize = SettingsHelper::getSetting('addDatabaseToTotalSize');
        $diskTotalSpace = SettingsHelper::getSetting('diskTotalSpace');

        if ($diskTotalSpace == 0) {
            return;
        }

        $currentTime = time();

        // Check if we should skip calculation due to throttling (every 5 minutes)
        $lastCalculation = SettingsHelper::getSetting('lastCalculationTime') ?? 0;
        $throttleInterval = 300; // 5 minutes

        if (!$force && ($currentTime - $lastCalculation) < $throttleInterval) {
            Craft::info("Skipping disk usage calculation due to throttling", "spacecontrol");
            return;
        }

        $basePath = Craft::getAlias('@root');

        // Scan files and sync with database
        $fileScanningService = Craft::$app->getPlugins()->getPlugin('spacecontrol')->get('fileScanning');
        $scanResults = $fileScanningService->scanAndSyncFiles($basePath, 1000);

        $diskUsageAbsolute = $scanResults['totalSize'];

        if ($addDatabaseToTotalSize) {
            $dbSize = DatabaseSizeHelper::getDBSize();
            $diskUsageAbsolute += $dbSize;
        }

        $diskUsagePercent = ($diskUsageAbsolute / 1024 / 1024 / 1024 * 1000000000) / ($diskTotalSpace * 1000 * 1000 * 1000) * 100;
        $diskUsagePercent = round($diskUsagePercent);

        SettingsHelper::setValues([
            "diskUsageAbsolute" => $diskUsageAbsolute,
            "diskUsagePercent" => $diskUsagePercent,
            "lastCalculationTime" => $currentTime,
            "isInitialized" => true,
        ]);

        Craft::info("SpaceControl scan completed: {$scanResults['totalFiles']} files, {$scanResults['deletedFiles']} deleted, {$scanResults['duration']}s", 'spacecontrol');
    }

    protected function defaultDescription(): string
    {
        return Craft::t('app', 'SpaceControl Disk Usage Check');
    }
}
