<?php

namespace szenario\craftspacecontrol\jobs;

use Craft;
use craft\mail\Message;

class SpaceControlChecker extends \craft\queue\BaseJob
{
    public function execute($queue): void
    {
        $message = new Message();

        $message->setTo('s.wesp@gmx.net');
        $message->setSubject('Oh Hai');
        $message->setTextBody('Hello from the queue system! 👋' . $this->getLastSent());

        Craft::$app->getMailer()->send($message);

        $this->setLastSent(time());
    }

    protected function defaultDescription(): string
    {
        return Craft::t('app', 'Sending a worthless email');
    }

    private function getHumanReadableSize($bytes)
    {
        if ($bytes > 0) {
            $base = floor(log($bytes) / log(1024));
            $units = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"); //units of measurement
            return number_format(($bytes / pow(1024, floor($base))), 3) . " $units[$base]";
        } else return "0 bytes";
    }

    private function getMailTimeTreshold()
    {
        $settings = Craft::$app->getPlugins()->getPlugin('spacecontrol')->getSettings();
        return $settings->mailTimeTreshold;
    }

    private function getLastSent()
    {
        $settings = Craft::$app->getPlugins()->getPlugin('spacecontrol')->getSettings();
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
