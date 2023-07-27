<?php

namespace szenario\craftspacecontrol\jobs;

use Craft;
use szenario\craftspacecontrol\helpers\FolderSizeHelper;
use szenario\craftspacecontrol\helpers\SettingsHelper;

class SpaceControlChecker extends \craft\queue\BaseJob
{
    public function execute($queue): void
    {
        self::calculateDiskUsage();
    }

    public static function executeImmediately(): void
    {
        self::calculateDiskUsage();
    }

    // 1. get current disk usage
    // 2. save to setting
    private static function calculateDiskUsage()
    {
        $diskTotalSpace = SettingsHelper::getSetting('diskTotalSpace');

        if ($diskTotalSpace == 0) {
            return;
        }

        $diskTotalSpaceBytes = $diskTotalSpace * 1024 * 1024 * 1024;
        $diskUsageAbsolute = FolderSizeHelper::getDirectorySize(CRAFT_BASE_PATH);
        $diskUsagePercent = $diskUsageAbsolute / $diskTotalSpaceBytes * 100;

        SettingsHelper::setValue("diskTotalSpace", $diskTotalSpace);
        SettingsHelper::setValue("diskUsageAbsolute", $diskUsageAbsolute);
        SettingsHelper::setValue("diskUsagePercent", $diskUsagePercent);

        SettingsHelper::setValue("isInitialized", true);
    }

    protected function defaultDescription(): string
    {
        return Craft::t('app', 'SpaceControl Disk Usage Check');
    }
}
