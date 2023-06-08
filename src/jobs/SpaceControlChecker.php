<?php

namespace szenario\craftspacecontrol\jobs;

use Craft;
use craft\mail\Message;
use craft\helpers\App;
use szenario\craftspacecontrol\helpers\SettingsHelper;

class SpaceControlChecker extends \craft\queue\BaseJob
{
    public function execute($queue): void
    {
        // 1. get current disk usage
        // 2. save to setting

        $diskUsageAbsolute = disk_total_space("/") - disk_free_space("/");
        $diskUsagePercent = $diskUsageAbsolute / disk_total_space("/") * 100;

        SettingsHelper::setValue("diskUsageAbsolute", $diskUsageAbsolute);
        SettingsHelper::setValue("diskUsagePercent", $diskUsagePercent);
    }

    protected function defaultDescription(): string
    {
        return Craft::t('app', 'Spacecontrol');
    }
}
