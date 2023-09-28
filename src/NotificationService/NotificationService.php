<?php

namespace szenario\craftspacecontrol\NotificationService;

use Craft;
use craft\helpers\App;
use craft\mail\Message;

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
        if ($lastSent > time() -  $notificationTimeThreshold) {
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
            self::sendEmailNotification($settings, $template);
        }

        // TODO: add slack here :))
    }

    private static function sendEmailNotification($settings, $template) {
        // get email addresses
        $recipients = $settings->emailRecipients;

        foreach ($recipients as $recipient) {
            $email = $recipient[0];
            if (empty($email)) {
                continue;
            }

            $domain = explode('//', App::env('PRIMARY_SITE_URL'))[1];

            $message = new Message();
            $message->setFrom('spacecontrol@' . $domain);
            $message->setTo($email);
            $message->setSubject($template->subject);
            $message->setTextBody($template->body);

            Craft::$app->getMailer()->send($message);
        }
    }
}