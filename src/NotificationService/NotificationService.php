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
        $diskUsagePercent = $settings->diskUsagePercent;

        if (!$settings === null) {
            Craft::warning("Settings object not found", "spacecontrol");
            return;
        }

        Craft::info("Starting notification service", "spacecontrol");
        // »»---------------------► HIGH LIMIT ◄---------------------««

        if ($diskUsagePercent >= $settings->notificationLimitHigh) {
            Craft::info("Disk usage is above {$settings->notificationLimitHigh}%.", "spacecontrol");

            if (!$settings->notificationHighTriggered) {
                Craft::info("High notification not triggered yet.", "spacecontrol");
                SettingsHelper::setValue("notificationHighTriggered", true);
                SettingsHelper::setValue("notificationMediumTriggered", true);
                SettingsHelper::setValue("notificationLowTriggered", true);
                self::sendNotifications($settings);
                return;
            }
        }

        // reset high trigger
        if ($diskUsagePercent < $settings->notificationLimitHigh && $settings->notificationHighTriggered) {
            Craft::info("Disk usage is below {$settings->notificationLimitHigh}%. Resetting trigger...", "spacecontrol");
            SettingsHelper::setValue("notificationHighTriggered", false);
        }


        // »»---------------------► MEDIUM LIMIT ◄---------------------««
        if ($diskUsagePercent >= $settings->notificationLimitMedium) {
            Craft::info("Disk usage is above {$settings->notificationLimitMedium}%.", "spacecontrol");

            if (!$settings->notificationMediumTriggered) {
                Craft::info("Medium notification not triggered yet.", "spacecontrol");
                SettingsHelper::setValue("notificationMediumTriggered", true);
                SettingsHelper::setValue("notificationLowTriggered", true);
                self::sendNotifications($settings);
                return;
            }
        }

        // reset medium trigger
        if ($diskUsagePercent < $settings->notificationLimitMedium && $settings->notificationMediumTriggered) {
            Craft::info("Disk usage is below {$settings->notificationLimitMedium}%. Resetting trigger...", "spacecontrol");
            SettingsHelper::setValue("notificationMediumTriggered", false);
        }


        // »»---------------------► LOW LIMIT ◄---------------------««
        if ($diskUsagePercent >= $settings->notificationLimitLow) {
            Craft::info("Disk usage is above {$settings->notificationLimitLow}%.", "spacecontrol");

            if (!$settings->notificationLowTriggered) {
                Craft::info("Low notification not triggered yet.", "spacecontrol");
                SettingsHelper::setValue("notificationLowTriggered", true);
                self::sendNotifications($settings);
                return;
            }
        }

        // reset low trigger
        if ($diskUsagePercent < $settings->notificationLimitLow && $settings->notificationLowTriggered) {
            Craft::info("Disk usage is below {$settings->notificationLimitLow}%. Resetting trigger...", "spacecontrol");
            SettingsHelper::setValue("notificationLowTriggered", false);
        }
    }

    private static function notificationTemplate(string $name, int $percentUsed, string $usedDiskSpace, string $totalDiskSpace) {
        $domain = explode('//', App::env('PRIMARY_SITE_URL'))[1];
        return [
            "subject" => "{$percentUsed}% of webspace (martinschnur.com) used",
            "body" => "Notification
            
Webspace: martinschnur.com
{$percentUsed}% of {$totalDiskSpace}GB used

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