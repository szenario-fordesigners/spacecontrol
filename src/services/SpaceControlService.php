<?php

namespace szenario\craftspacecontrol\services;

use Craft;
use szenario\craftspacecontrol\records\SettingsRecord;

/**
 * SpaceControl service
 */
class SpaceControlService
{
    /**
     * Get a setting value
     */
    public function getSetting(string $key, $default = null)
    {
        $record = SettingsRecord::findOne(['setting' => $key]);

        if (!$record) {
            return $default;
        }

        // Handle JSON-encoded arrays
        if (in_array($key, ['emailRecipients'])) {
            return json_decode($record->value, true) ?: [];
        }

        // Handle boolean values
        if (in_array($key, ['addDatabaseToTotalSize', 'isInitialized', 'emailNotificationsEnabled', 'notificationLowTriggered', 'notificationMediumTriggered', 'notificationHighTriggered'])) {
            return (bool) $record->value;
        }

        // Handle numeric values
        if (in_array($key, ['diskTotalSpace', 'diskUsageAbsolute', 'diskUsagePercent', 'lastCalculationTime', 'notificationLimitLow', 'notificationLimitMedium', 'notificationLimitHigh'])) {
            return is_numeric($record->value) ? (float) $record->value : 0;
        }

        return $record->value;
    }

    /**
     * Set a setting value
     */
    public function setSetting(string $key, $value): bool
    {
        $record = SettingsRecord::findOne(['setting' => $key]);

        if (!$record) {
            $record = new SettingsRecord();
            $record->setting = $key;
        }

        // Handle JSON-encoded arrays
        if (in_array($key, ['emailRecipients'])) {
            $record->value = json_encode($value);
        } else {
            $record->value = (string) $value;
        }

        return $record->save();
    }

    /**
     * Set multiple settings
     */
    public function setSettings(array $settings): bool
    {
        $success = true;
        foreach ($settings as $key => $value) {
            if (!$this->setSetting($key, $value)) {
                $success = false;
                Craft::error("Failed to save setting: {$key}", 'spacecontrol');
            }
        }
        return $success;
    }

    /**
     * Get all settings as an object (for compatibility)
     */
    public function getAllSettings(): object
    {
        $records = SettingsRecord::find()->all();
        $settingsObject = new \stdClass();

        foreach ($records as $record) {
            $key = $record->setting;
            $settingsObject->$key = $this->getSetting($key);
        }

        // Set default values for any missing settings
        $defaults = [
            'diskTotalSpace' => 0.0,
            'diskUsageAbsolute' => 0,
            'diskUsagePercent' => 0.0,
            'addDatabaseToTotalSize' => false,
            'isInitialized' => false,
            'lastCalculationTime' => 0,
            'notificationLimitLow' => 90,
            'notificationLimitMedium' => 95,
            'notificationLimitHigh' => 99,
            'notificationLowTriggered' => false,
            'notificationMediumTriggered' => false,
            'notificationHighTriggered' => false,
            'emailNotificationsEnabled' => false,
            'emailRecipients' => [],
        ];

        foreach ($defaults as $key => $defaultValue) {
            if (!isset($settingsObject->$key)) {
                $settingsObject->$key = $defaultValue;
            }
        }

        return $settingsObject;
    }

    /**
     * Migrate settings from Craft plugin settings
     */
    public function migrateFromCraftSettings(): bool
    {
        try {
            $plugin = Craft::$app->getPlugins()->getPlugin('spacecontrol');
            if (!$plugin) {
                return false;
            }

            $craftSettings = $plugin->getSettings();
            if (!$craftSettings) {
                return true;
            }

            $settingsToMigrate = [
                'diskTotalSpace',
                'diskUsageAbsolute',
                'diskUsagePercent',
                'addDatabaseToTotalSize',
                'isInitialized',
                'lastCalculationTime',
                'notificationLimitLow',
                'notificationLimitMedium',
                'notificationLimitHigh',
                'notificationLowTriggered',
                'notificationMediumTriggered',
                'notificationHighTriggered',
                'emailNotificationsEnabled',
                'emailRecipients',
            ];

            foreach ($settingsToMigrate as $key) {
                if (isset($craftSettings->$key)) {
                    $this->setSetting($key, $craftSettings->$key);
                }
            }

            return true;

        } catch (\Throwable $e) {
            Craft::error('Failed to migrate settings: ' . $e->getMessage(), 'spacecontrol');
            return false;
        }
    }
}

