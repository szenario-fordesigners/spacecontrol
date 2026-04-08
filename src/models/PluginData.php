<?php

namespace szenario\craftspacecontrol\models;

use craft\base\Model;

/**
 * spacecontrol internal plugin data model
 */
class PluginData extends Model
{
    // Calculated data (read-only for users)
    public int $diskUsageAbsolute = 0;
    public float $diskUsagePercent = 0.0;
    public bool $isInitialized = false;
    public int $lastCalculationTime = 0;

    // Notification thresholds (could be user-editable in future)
    public int $notificationLimitLow = 90;
    public int $notificationLimitMedium = 95;
    public int $notificationLimitHigh = 99;

    // Notification states
    public bool $notificationLowTriggered = false;
    public bool $notificationMediumTriggered = false;
    public bool $notificationHighTriggered = false;

    public function defineRules(): array
    {
        return [
            [['diskUsageAbsolute', 'lastCalculationTime'], 'integer', 'min' => 0],
            [['diskUsagePercent'], 'number', 'min' => 0, 'max' => 100],
            [['isInitialized', 'notificationLowTriggered', 'notificationMediumTriggered', 'notificationHighTriggered'], 'boolean'],
            [['notificationLimitLow', 'notificationLimitMedium', 'notificationLimitHigh'], 'integer', 'min' => 0, 'max' => 100],
        ];
    }
}
