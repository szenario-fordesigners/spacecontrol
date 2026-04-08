<?php

namespace szenario\craftspacecontrol\NotificationService;


use Craft;
use szenario\craftspacecontrol\NotificationService\EmailNotification;
use szenario\craftspacecontrol\helpers\SettingsHelper;

class NotificationService
{
    public static function start()
    {
        $settings = SettingsHelper::getPluginSettings();

        if ($settings === null) {
            Craft::warning("Settings object not found", "spacecontrol");
            return;
        }

        $diskUsagePercent = $settings->diskUsagePercent;

        Craft::info("Starting notification service", "spacecontrol");
        // »»---------------------► HIGH LIMIT ◄---------------------««

        if ($diskUsagePercent >= $settings->notificationLimitHigh) {
            Craft::info("Disk usage is above {$settings->notificationLimitHigh}%.", "spacecontrol");

            if (!$settings->notificationHighTriggered) {
                Craft::info("High notification not triggered yet.", "spacecontrol");
                SettingsHelper::setValue("notificationHighTriggered", true);
                SettingsHelper::setValue("notificationMediumTriggered", true);
                SettingsHelper::setValue("notificationLowTriggered", true);
                self::sendNotifications($settings, 'Critical', $settings->notificationLimitHigh);
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
                self::sendNotifications($settings, 'Warning', $settings->notificationLimitMedium);
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
                self::sendNotifications($settings, 'Notice', $settings->notificationLimitLow);
                return;
            }
        }

        // reset low trigger
        if ($diskUsagePercent < $settings->notificationLimitLow && $settings->notificationLowTriggered) {
            Craft::info("Disk usage is below {$settings->notificationLimitLow}%. Resetting trigger...", "spacecontrol");
            SettingsHelper::setValue("notificationLowTriggered", false);
        }
    }

    private static function notificationTemplate(int $percentUsed, string $usedDiskSpace, string $totalDiskSpace, string $severity, int $threshold)
    {
        try {
            $parsedUrl = parse_url(\craft\helpers\UrlHelper::siteUrl());
            $truncatedDomain = $parsedUrl['host'] ?? 'unknown-domain';
        } catch (\Exception $e) {
            Craft::error("Could not get domain", "spacecontrol");
            return null;
        }

        return [
            "subject" => "[{$severity}] {$percentUsed}% of webspace ({$truncatedDomain}) used",
            "body" => "{$severity}: Disk usage exceeded {$threshold}% threshold

Webspace: {$truncatedDomain}
Usage:    {$percentUsed}% of {$totalDiskSpace} GB used

To maintain optimal website performance please contact your hosting provider.

—

spacecontrol
Webspace Monitoring On Point for Craft CMS
developed by szenario"
        ];
    }


    public static function sendNotifications($settings, string $severity, int $threshold)
    {
        Craft::info("Building notification template (severity: {$severity}, threshold: {$threshold}%)", "spacecontrol");

        $template = self::notificationTemplate(
            min($settings->diskUsagePercent, 100),
            $settings->diskUsageAbsolute,
            $settings->diskTotalSpace,
            $severity,
            $threshold
        );

        if ($template === null) {
            Craft::warning("Notification Template object not found", "spacecontrol");
            return;
        }

        // check if notifications are enabled and send them
        if ($settings->emailNotificationsEnabled) {
            Craft::info("Start sending email notifications", "spacecontrol");
            EmailNotification::sendEmailNotification($settings, $template);
        }

        // TODO: add slack here :))
    }
}