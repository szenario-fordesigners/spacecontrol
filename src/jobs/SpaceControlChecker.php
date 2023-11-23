<?php

namespace szenario\craftspacecontrol\jobs;

use Craft;
use szenario\craftspacecontrol\helpers\FolderSizeHelper;
use szenario\craftspacecontrol\helpers\SettingsHelper;
use szenario\craftspacecontrol\helpers\DatabaseSizeHelper;
use szenario\craftspacecontrol\NotificationService\NotificationService;

class SpaceControlChecker extends \craft\queue\BaseJob implements \yii\queue\RetryableJobInterface
{
    public function execute($queue): void
    {
        self::calculateDiskUsage();

        NotificationService::start();
    }

    public static function executeImmediately(): void
    {
        self::calculateDiskUsage();

        NotificationService::start();
    }

    // 1. get current disk usage
    // 2. save to setting
    private static function calculateDiskUsage()
    {
        $dbSizeInCalc = SettingsHelper::getSetting('dbSizeInCalc');
        $diskTotalSpace = SettingsHelper::getSetting('diskTotalSpace');

        if ($diskTotalSpace == 0) {
            return;
        }

        $diskUsageAbsolute = FolderSizeHelper::getDirectorySize(CRAFT_BASE_PATH);

        if ($dbSizeInCalc) {
            $dbSize = DatabaseSizeHelper::getDBSize();
            $diskUsageAbsolute += $dbSize;
        }

        $diskUsagePercent = ($diskUsageAbsolute / 1024 / 1024 / 1024 * 1000000000) / ($diskTotalSpace  * 1000 * 1000 * 1000) * 100;
        $diskUsagePercent = round($diskUsagePercent);

        SettingsHelper::setValue("diskUsageAbsolute", $diskUsageAbsolute);
        SettingsHelper::setValue("diskUsagePercent", $diskUsagePercent);

        SettingsHelper::setValue("isInitialized", true);
    }

    protected function defaultDescription(): string
    {
        return Craft::t('app', 'SpaceControl Disk Usage Check');
    }

    public function getTtr()
    {
//        max execution time of 30 seconds
        return 30;
    }

    public function canRetry($attempt, $error)
    {
//        2 retries
        return ($attempt < 2);
    }
}
