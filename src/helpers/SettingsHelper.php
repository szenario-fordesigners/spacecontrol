<?php

namespace szenario\craftspacecontrol\helpers;

use Craft;
use szenario\craftspacecontrol\services\SettingsService;

class SettingsHelper
{
    private static ?SettingsService $_service = null;

    private static function getService(): SettingsService
    {
        if (self::$_service === null) {
            self::$_service = new SettingsService();
        }
        return self::$_service;
    }

    // PLUGIN SETTINGS GETTER
    public static function getPluginSettings()
    {
        return self::getService()->getAllSettings();
    }

    public static function getSetting($key)
    {
        // Check if this is a plugin data key
        $pluginDataKeys = [
            'diskUsageAbsolute',
            'diskUsagePercent',
            'isInitialized',
            'lastCalculationTime',
            'notificationLimitLow',
            'notificationLimitMedium',
            'notificationLimitHigh',
            'notificationLowTriggered',
            'notificationMediumTriggered',
            'notificationHighTriggered',
        ];

        if (in_array($key, $pluginDataKeys)) {
            return self::getService()->getPluginData($key);
        }

        // Otherwise, try to get from Craft's built-in settings first
        $plugin = Craft::$app->getPlugins()->getPlugin('spacecontrol');
        if ($plugin) {
            $userSettings = $plugin->getSettings();
            if (isset($userSettings->$key)) {
                return $userSettings->$key;
            }
        }

        // Fall back to database settings (for legacy support)
        return self::getService()->getSetting($key);
    }

    // PLUGIN SETTINGS SETTER
    public static function setValue($key, $value)
    {
        try {
            // Check if this is a plugin data key
            $pluginDataKeys = [
                'diskUsageAbsolute',
                'diskUsagePercent',
                'isInitialized',
                'lastCalculationTime',
                'notificationLimitLow',
                'notificationLimitMedium',
                'notificationLimitHigh',
                'notificationLowTriggered',
                'notificationMediumTriggered',
                'notificationHighTriggered',
            ];

            if (in_array($key, $pluginDataKeys)) {
                $success = self::getService()->setPluginData($key, $value);
            } else {
                // For user settings, Craft handles saving automatically
                // But we can also save to database for legacy support
                $success = self::getService()->setSetting($key, $value);
            }

            if (!$success) {
                Craft::error('Failed to save SpaceControl setting: ' . $key, 'spacecontrol');
            }
        } catch (\Throwable $e) {
            Craft::error('Failed to save SpaceControl setting ' . $key . ': ' . $e->getMessage(), 'spacecontrol');
        }
    }

    // PLUGIN SETTINGS BATCH SETTER
    public static function setValues(array $values)
    {
        try {
            // Separate user settings from plugin data
            $pluginDataKeys = [
                'diskUsageAbsolute',
                'diskUsagePercent',
                'isInitialized',
                'lastCalculationTime',
                'notificationLimitLow',
                'notificationLimitMedium',
                'notificationLimitHigh',
                'notificationLowTriggered',
                'notificationMediumTriggered',
                'notificationHighTriggered',
            ];

            $userSettings = [];
            $pluginData = [];

            foreach ($values as $key => $value) {
                if (in_array($key, $pluginDataKeys)) {
                    $pluginData[$key] = $value;
                } else {
                    $userSettings[$key] = $value;
                }
            }

            $success = true;

            // Save user settings
            if (!empty($userSettings)) {
                if (!self::getService()->setSettings($userSettings)) {
                    $success = false;
                    Craft::error('Failed to save some SpaceControl settings', 'spacecontrol');
                }
            }

            // Save plugin data
            if (!empty($pluginData)) {
                if (!self::getService()->setPluginDataValues($pluginData)) {
                    $success = false;
                    Craft::error('Failed to save some SpaceControl plugin data', 'spacecontrol');
                }
            }

            return $success;
        } catch (\Throwable $e) {
            Craft::error('Failed to save SpaceControl settings: ' . $e->getMessage(), 'spacecontrol');
            return false;
        }
    }
}