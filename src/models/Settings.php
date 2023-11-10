<?php

namespace szenario\craftspacecontrol\models;
use craft\base\Model;
use craft\elements\User;

/**
 * spacecontrol settings
 */
class Settings extends Model
{
    // general settings
    public $diskTotalSpace = 0;
    public $diskUsageAbsolute = 0;
    public $diskUsagePercent = 0;
    public bool $dbSizeInCalc = false;
    public $isInitialized = false;


    // notification settings
    public $notificationLimitLow = 90;
    public $notificationLimitMedium = 95;
    public $notificationLimitHigh = 99;

    public $notificationLowTriggered = false;
    public $notificationMediumTriggered = false;
    public $notificationHighTriggered = false;

    // email notification settings
    public $emailNotificationsEnabled = false;
    public $emailRecipients = [];

    public function defineRules(): array
    {
        return [
            [
                [
                    'diskTotalSpace',
                    'diskUsageAbsolute',
                    'diskUsagePercent',
                    'dbSizeInCalc',
                    'isInitialized',
                    'emailNotificationsEnabled',
                    'emailRecipients',
                    ],
                'required'],
            [['dbSizeInCalc', 'emailNotificationsEnabled'],'boolean'],
        ];
    }
}