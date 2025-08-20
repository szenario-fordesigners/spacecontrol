<?php

namespace szenario\craftspacecontrol\helpers;

use Craft;

class SettingsHelper
{
    // PLUGIN SETTINGS GETTER
    public static function getPluginSettings()
    {
        return Craft::$app->getPlugins()->getPlugin('spacecontrol')->getSettings();
    }

    public static function getSetting($key)
    {
        $settings = SettingsHelper::getPluginSettings();
        return $settings->$key;
    }

    // PLUGIN SETTINGS SETTER
    public static function setValue($key, $value)
    {
        $plugin = Craft::$app->getPlugins()->getPlugin('spacecontrol');
        if ($plugin === null) {
            Craft::warning('SpaceControl plugin not found when trying to save setting: ' . $key, 'spacecontrol');
            return;
        }

        $settings = $plugin->getSettings();
        $settings->$key = $value;

        // Create a safe array with only the properties we want to save
        $settingsArray = [
            'diskTotalSpace' => $settings->diskTotalSpace,
            'diskUsageAbsolute' => $settings->diskUsageAbsolute,
            'diskUsagePercent' => $settings->diskUsagePercent,
            'addDatabaseToTotalSize' => $settings->addDatabaseToTotalSize,
            'isInitialized' => $settings->isInitialized,
            'lastCalculationTime' => $settings->lastCalculationTime,
            'notificationLimitLow' => $settings->notificationLimitLow,
            'notificationLimitMedium' => $settings->notificationLimitMedium,
            'notificationLimitHigh' => $settings->notificationLimitHigh,
            'notificationLowTriggered' => $settings->notificationLowTriggered,
            'notificationMediumTriggered' => $settings->notificationMediumTriggered,
            'notificationHighTriggered' => $settings->notificationHighTriggered,
            'emailNotificationsEnabled' => $settings->emailNotificationsEnabled,
            'emailRecipients' => $settings->emailRecipients,
        ];

        try {
            Craft::$app->getPlugins()->savePluginSettings($plugin, $settingsArray);
        } catch (\Throwable $e) {
            Craft::error('Failed to save SpaceControl setting ' . $key . ': ' . $e->getMessage(), 'spacecontrol');
        }
    }

    // PLUGIN SETTINGS BATCH SETTER
    public static function setValues(array $values)
    {
        $plugin = Craft::$app->getPlugins()->getPlugin('spacecontrol');
        if ($plugin === null) {
            Craft::warning('SpaceControl plugin not found when trying to save settings', 'spacecontrol');
            return;
        }

        $settings = $plugin->getSettings();

        // Update the settings object with new values
        foreach ($values as $key => $value) {
            $settings->$key = $value;
        }

        // Create a safe array with only the properties we want to save
        $settingsArray = [
            'diskTotalSpace' => $settings->diskTotalSpace,
            'diskUsageAbsolute' => $settings->diskUsageAbsolute,
            'diskUsagePercent' => $settings->diskUsagePercent,
            'addDatabaseToTotalSize' => $settings->addDatabaseToTotalSize,
            'isInitialized' => $settings->isInitialized,
            'lastCalculationTime' => $settings->lastCalculationTime,
            'notificationLimitLow' => $settings->notificationLimitLow,
            'notificationLimitMedium' => $settings->notificationLimitMedium,
            'notificationLimitHigh' => $settings->notificationLimitHigh,
            'notificationLowTriggered' => $settings->notificationLowTriggered,
            'notificationMediumTriggered' => $settings->notificationMediumTriggered,
            'notificationHighTriggered' => $settings->notificationHighTriggered,
            'emailNotificationsEnabled' => $settings->emailNotificationsEnabled,
            'emailRecipients' => $settings->emailRecipients,
        ];

        try {
            Craft::$app->getPlugins()->savePluginSettings($plugin, $settingsArray);
        } catch (\Throwable $e) {
            Craft::error('Failed to save SpaceControl settings: ' . $e->getMessage(), 'spacecontrol');
        }
    }
}