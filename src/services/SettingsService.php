<?php

namespace szenario\craftspacecontrol\services;

use Craft;
use szenario\craftspacecontrol\records\PluginDataRecord;

/**
 * Settings service for managing plugin settings
 */
class SettingsService
{
    private const TABLE_NAME_PLUGIN_DATA = '{{%spacecontrol_plugin_data}}';

    /**
     * Get plugin data value (internal plugin data from spacecontrol_plugin_data table)
     */
    public function getPluginData(string $key)
    {
        if (!$this->pluginDataTableExists()) {
            return null;
        }

        $record = PluginDataRecord::findOne(['key' => $key]);
        return $this->castValue($record);
    }

    /**
     * Set plugin data value (internal plugin data)
     */
    public function setPluginData(string $key, $value): bool
    {
        if (!$this->pluginDataTableExists()) {
            Craft::warning('Cannot save plugin data because spacecontrol_plugin_data table does not exist yet.', 'spacecontrol');
            return false;
        }

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


    public function castValue($record)
    {
        if (!$record) {
            return null;
        }

        // Handle boolean values
        if (in_array($record->key, ['isInitialized', 'notificationLowTriggered', 'notificationMediumTriggered', 'notificationHighTriggered'])) {
            return (bool) $record->value;
        }

        // Handle numeric values
        if (in_array($record->key, ['diskUsageAbsolute', 'diskUsagePercent', 'lastCalculationTime', 'notificationLimitLow', 'notificationLimitMedium', 'notificationLimitHigh'])) {
            return is_numeric($record->value) ? (float) $record->value : 0;
        }

        return $record->value;
    }

    /**
     * Get all plugin data as an object
     */
    public function getAllPluginData(): object
    {
        $dataObject = new \stdClass();

        if (!$this->pluginDataTableExists()) {
            return $this->applyDefaults($dataObject);
        }

        $records = PluginDataRecord::find()->all();

        foreach ($records as $record) {
            $key = $record->key;
            $dataObject->$key = $this->castValue($record);
        }

        return $this->applyDefaults($dataObject);
    }

    private function pluginDataTableExists(): bool
    {
        try {
            return Craft::$app->getDb()->tableExists(self::TABLE_NAME_PLUGIN_DATA);
        } catch (\Throwable $e) {
            Craft::warning('Could not verify plugin data table existence: ' . $e->getMessage(), 'spacecontrol');
            return false;
        }
    }

    private function applyDefaults(object $dataObject): object
    {
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
