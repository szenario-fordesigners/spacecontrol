<?php

namespace szenario\craftspacecontrol\jobs;

use Craft;
use craft\mail\Message;

class SpaceControlChecker extends \craft\queue\BaseJob
{
    public function execute($queue): void
    {
        // 1. get current disk usage
        // 2. get current disk limit
        // 3. compare
        // 4. if limit is reached:
        // 5. get mailTimeTreshold
        // 6. get lastSent
        // 7. compare
        // 8. if lastSent is smaller than time() - mailTimeTreshold, send mail
        // 9. set lastSent to time()

        $diskUsageAbsolute = disk_total_space("/") - disk_free_space("/");
        $diskUsagePercent = $diskUsageAbsolute / disk_total_space("/") * 100;
        $diskLimits = $this->diskLimits();

        // if we are below the usage limit, do not do anything
        if ($diskUsagePercent < $diskLimits['diskLimitPercent']) {
            return;
        }

        // if lastSent is close to now than specified in mailTimeThreshold, do not do anything
        if ($this->getLastSent() > time() - $this->getMailTimeThreshold()) {
            return;
        }

        // send the notification mail
        $message = new Message();

        $message->setTo($this->getAdminRecipients());
        $message->setSubject('Oh Hai');
        $message->setTextBody('Hello from the queue system! 👋' . " " . $diskUsagePercent . ": " . $diskLimits['diskLimitPercent'] . ' | ' . $this->getAdminRecipients()[0]);

        Craft::$app->getMailer()->send($message);

        // set lastSent to now
        $this->setLastSent(time());
    }

    protected function defaultDescription(): string
    {
        return Craft::t('app', 'Spacecontrol');
    }

    private function getHumanReadableSize($bytes)
    {
        if ($bytes > 0) {
            $base = floor(log($bytes) / log(1024));
            $units = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"); //units of measurement
            return number_format(($bytes / pow(1024, floor($base))), 3) . " $units[$base]";
        } else return "0 bytes";
    }


    // SETTINGS - SETTERS AND GETTERS
    private function getSettings() {
        return Craft::$app->getPlugins()->getPlugin('spacecontrol')->getSettings();
    }

    private function diskLimits()
    {
        $settings = $this->getSettings();
        return [
            'diskLimitPercent' => $settings->diskLimitPercent
        ];
    }

    private function getAdminRecipients()
    {
        $settings = $this->getSettings();
        $adminRecipients = $settings->adminRecipients;
        return explode(', ', $adminRecipients);
    }

    private function getMailTimeThreshold()
    {
        $settings = $this->getSettings();
        return $settings->mailTimeThreshold;
    }

    private function getLastSent()
    {
        $settings = $this->getSettings();
        return $settings->lastSent;
    }

    private function setLastSent($time)
    {
        $plugin = Craft::$app->getPlugins()->getPlugin('spacecontrol');
        $settings = $plugin->getSettings();
        $settings->lastSent = $time;

        Craft::$app->getPlugins()->savePluginSettings($plugin, $settings->toArray());
    }
}
