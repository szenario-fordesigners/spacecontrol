<?php

namespace szenario\craftspacecontrol\jobs;

use Craft;
use szenario\craftspacecontrol\helpers\SettingsHelper;

class SpaceControlChecker extends \craft\queue\BaseJob
{
    public function execute($queue): void
    {
        // 1. get current disk usage
        // 2. save to setting

        $diskTotalSpace = disk_total_space("/");
        $diskUsageAbsolute = $diskTotalSpace - disk_free_space("/");
        $diskUsagePercent = $diskUsageAbsolute / $diskTotalSpace * 100;
        
        SettingsHelper::setValue("diskTotalSpace", $diskTotalSpace);
        SettingsHelper::setValue("diskUsageAbsolute", $diskUsageAbsolute);
        SettingsHelper::setValue("diskUsagePercent", $diskUsagePercent);
    }

    protected function defaultDescription(): string
    {
        return Craft::t('app', 'Spacecontrol');
    }
}
