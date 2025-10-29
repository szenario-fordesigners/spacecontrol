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
        return self::getService()->getSetting($key);
    }

    // PLUGIN SETTINGS SETTER
    public static function setValue($key, $value)
    {
        try {
            $success = self::getService()->setSetting($key, $value);
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
            $success = self::getService()->setSettings($values);
            if (!$success) {
                Craft::error('Failed to save some SpaceControl settings', 'spacecontrol');
            }
        } catch (\Throwable $e) {
            Craft::error('Failed to save SpaceControl settings: ' . $e->getMessage(), 'spacecontrol');
        }
    }
}