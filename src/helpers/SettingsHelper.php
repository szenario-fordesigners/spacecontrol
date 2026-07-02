<?php

namespace szenario\craftspacecontrol\helpers;

use Craft;
use szenario\craftspacecontrol\services\SettingsService;
use szenario\craftspacecontrol\SpaceControl;

class SettingsHelper
{
    private const PLUGIN_DATA_KEYS = [
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
        // Start with default/user settings
        $settings = new \stdClass();

        $plugin = SpaceControl::getInstance();
        if ($plugin) {
            $userSettings = $plugin->getSettings();
            foreach ($userSettings as $key => $value) {
                $settings->$key = $value;
            }
        }

        // Merge in dynamic plugin data
        $pluginData = self::getService()->getAllPluginData();
        foreach ($pluginData as $key => $value) {
            $settings->$key = $value;
        }

        return $settings;
    }

    public static function getSetting($key)
    {
        // Check if this is a plugin data key
        if (in_array($key, self::PLUGIN_DATA_KEYS)) {
            return self::getService()->getPluginData($key);
        }

        // Otherwise, get from Craft's built-in settings
        $plugin = SpaceControl::getInstance();
        if ($plugin) {
            $userSettings = $plugin->getSettings();
            if (isset($userSettings->$key)) {
                return $userSettings->$key;
            }
        }

        return null;
    }

    // PLUGIN SETTINGS SETTER
    public static function setValue($key, $value)
    {
        try {
            // Check if this is a plugin data key
            if (in_array($key, self::PLUGIN_DATA_KEYS)) {
                $success = self::getService()->setPluginData($key, $value);
            } else {
                // For user settings, use Craft's savePluginSettings
                $plugin = SpaceControl::getInstance();
                $settings = $plugin->getSettings()->toArray();
                $settings[$key] = $value;

                $success = Craft::$app->getPlugins()->savePluginSettings($plugin, $settings);
            }

            if (!$success) {
                Craft::error('Failed to save spacecontrol setting: ' . $key, 'spacecontrol');
            }
        } catch (\Throwable $e) {
            Craft::error('Failed to save spacecontrol setting ' . $key . ': ' . $e->getMessage(), 'spacecontrol');
        }
    }

    // PLUGIN SETTINGS BATCH SETTER
    public static function setValues(array $values)
    {
        try {
            // Separate user settings from plugin data
            $userSettings = [];
            $pluginData = [];

            foreach ($values as $key => $value) {
                if (in_array($key, self::PLUGIN_DATA_KEYS)) {
                    $pluginData[$key] = $value;
                } else {
                    $userSettings[$key] = $value;
                }
            }

            $success = true;

            // Save user settings
            if (!empty($userSettings)) {
                $plugin = SpaceControl::getInstance();
                $currentSettings = $plugin->getSettings()->toArray();
                $newSettings = array_merge($currentSettings, $userSettings);

                if (!Craft::$app->getPlugins()->savePluginSettings($plugin, $newSettings)) {
                    $success = false;
                    Craft::error('Failed to save some spacecontrol settings', 'spacecontrol');
                }
            }

            // Save plugin data
            if (!empty($pluginData)) {
                if (!self::getService()->setPluginDataValues($pluginData)) {
                    $success = false;
                    Craft::error('Failed to save some spacecontrol plugin data', 'spacecontrol');
                }
            }

            return $success;
        } catch (\Throwable $e) {
            Craft::error('Failed to save spacecontrol settings: ' . $e->getMessage(), 'spacecontrol');
            return false;
        }
    }
}
