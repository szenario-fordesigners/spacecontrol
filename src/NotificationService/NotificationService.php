<?php

namespace szenario\craftspacecontrol\NotificationService;


use Craft;
use craft\helpers\App;
use szenario\craftspacecontrol\NotificationService\EmailNotification;
use szenario\craftspacecontrol\helpers\SettingsHelper;

class NotificationService
{
    public static function start() {
        $settings = SettingsHelper::getPluginSettings();
        $notificationLimit = $settings->notificationLimit;
        $diskUsagePercent = $settings->diskUsagePercent;

        Craft::info("Starting notification service", "spacecontrol");

        // check if disk usage is below the notification limit
        if ($diskUsagePercent < $notificationLimit) {
            Craft::info("Disk usage is below the notification limit. No notifications will be sent.", "spacecontrol");
            return;
        }

        $lastSent = $settings->lastSent;
        $notificationTimeThreshold = $settings->notificationTimeThreshold;

        // check if enough time has passed since the last time we sent notifications
        if (time() - $lastSent < $notificationTimeThreshold) {
            Craft::info("Not enough time has passed since the last notification. No notifications will be sent.", "spacecontrol");
            return;
        }


        // actually send notifications
        self::sendNotifications($settings);
    }

    private static function notificationTemplate(string $name, int $percentUsed, string $usedDiskSpace, string $totalDiskSpace) {
        $domain = explode('//', App::env('PRIMARY_SITE_URL'))[1];
        return [
            "subject" => "{$percentUsed}% of webspace (martinschnur.com) used",
            "body" => "Notification
            
Webspace: martinschnur.com
91% of 1GB used

To maintain optimal website performance please contact your hosting provider.            
          
—

SpaceControl
Webspace Monitoring On Point for Craft CMS
developed by szenario"
        ];
    }


    public static function sendNotifications($settings) {
        Craft::info("Building notification template", "spacecontrol");
        // build notification template
        $template = self::notificationTemplate(
            "",
            $settings->diskUsagePercent,
            $settings->diskUsageAbsolute,
            $settings->diskTotalSpace
        );

        // check if notifications are enabled and send them
        if ($settings->emailNotificationsEnabled) {
            Craft::info("Start sending email notifications", "spacecontrol");
            EmailNotification::sendEmailNotification($settings, $template);
        }

        // TODO: add slack here :))
    }


}