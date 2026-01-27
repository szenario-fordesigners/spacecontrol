<?php

namespace szenario\craftspacecontrol\services;

use Craft;
use szenario\craftspacecontrol\records\PluginDataRecord;

/**
 * Settings service for managing plugin settings
 */
class SettingsService
{
    /**
     * Get plugin data value (internal plugin data from spacecontrol_plugin_data table)
     */
    public function getPluginData(string $key, $default = null)
    {
        $record = PluginDataRecord::findOne(['key' => $key]);

        if (!$record) {
            return $default;
        }

        // Handle boolean values
        if (in_array($key, ['isInitialized', 'notificationLowTriggered', 'notificationMediumTriggered', 'notificationHighTriggered'])) {
            return (bool) $record->value;
        }

        // Handle numeric values
        if (in_array($key, ['diskUsageAbsolute', 'diskUsagePercent', 'lastCalculationTime', 'notificationLimitLow', 'notificationLimitMedium', 'notificationLimitHigh'])) {
            return is_numeric($record->value) ? (float) $record->value : 0;
        }

        return $record->value;
    }

    /**
     * Set plugin data value (internal plugin data)
     */
    public function setPluginData(string $key, $value): bool
    {
        $record = PluginDataRecord::findOne(['key' => $key]);

        if (!$record) {
            $record = new PluginDataRecord();
            $record->key = $key;
        }

        $record->value = (string) $value;
        return $record->save();
    }

    /**
     * Set multiple plugin data values
     */
    public function setPluginDataValues(array $values): bool
    {
        $success = true;
        foreach ($values as $key => $value) {
            if (!$this->setPluginData($key, $value)) {
                $success = false;
                Craft::error("Failed to save plugin data: {$key}", 'spacecontrol');
            }
        }
        return $success;
    }

    /**
     * Get all plugin data as an object
     */
    public function getAllPluginData(): object
    {
        $records = PluginDataRecord::find()->all();
        $dataObject = new \stdClass();

        foreach ($records as $record) {
            $key = $record->key;
            $dataObject->$key = $this->getPluginData($key);
        }

        // Set default values for any missing plugin data
        $defaults = [
            'diskUsageAbsolute' => 0,
            'diskUsagePercent' => 0.0,
            'isInitialized' => false,
            'lastCalculationTime' => 0,
            'notificationLimitLow' => 90,
            'notificationLimitMedium' => 95,
            'notificationLimitHigh' => 99,
            'notificationLowTriggered' => false,
            'notificationMediumTriggered' => false,
            'notificationHighTriggered' => false,
        ];

        foreach ($defaults as $key => $defaultValue) {
            if (!isset($dataObject->$key)) {
                $dataObject->$key = $defaultValue;
            }
        }

        return $dataObject;
    }

}
