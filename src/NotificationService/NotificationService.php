<?php

namespace szenario\craftspacecontrol\NotificationService;


use szenario\craftspacecontrol\NotificationService\EmailNotification;
use szenario\craftspacecontrol\helpers\SettingsHelper;

class NotificationService
{
    public static function start() {
        $settings = SettingsHelper::getPluginSettings();
        $notificationLimit = $settings->notificationLimit;
        $diskUsagePercent = $settings->diskUsagePercent;

        // check if disk usage is below the notification limit
        if ($diskUsagePercent < $notificationLimit) {
            return;
        }

        $lastSent = $settings->lastSent;
        $notificationTimeThreshold = $settings->notificationTimeThreshold;

        // check if enough time has passed since the last time we sent notifications
        if (time() - $lastSent < $notificationTimeThreshold) {
            return;
        }



        // actually send notifications
        self::sendNotifications($settings);
    }

    private static function notificationTemplate(string $name, int $percentUsed, string $usedDiskSpace, string $totalDiskSpace) {
        return [
            "subject" => "Your disk space is {$percentUsed}% full",
            "body" => "Hey {$name},
                your disk space is {$percentUsed}% full. You have {$usedDiskSpace} of {$totalDiskSpace} left.

                Best Regards,
                spacecontrol"
        ];
    }

    public static function sendNotifications($settings) {
        // build notification template
        $template = self::notificationTemplate(
            "admin",
            $settings->diskUsagePercent,
            $settings->diskUsageAbsolute,
            $settings->diskTotalSpace
        );

        // check if notifications are enabled and send them
        if ($settings->emailNotificationsEnabled) {
            EmailNotification::sendEmailNotification($settings, $template);
        }

        // TODO: add slack here :))
    }


}