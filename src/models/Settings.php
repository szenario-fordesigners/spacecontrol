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
    public $lastSent = 0;
    public $notificationTimeThreshold = 86400;
    public $notificationLimit = 90;

    // email notification settings
    public bool $emailNotificationsEnabled = false;
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
                    'lastSent',
                    'notificationTimeThreshold',
                    'notificationLimit',
                    'emailNotificationsEnabled',
                    'emailRecipients',
                    ],
                'required'],
            [['dbSizeInCalc', 'emailNotificationsEnabled'],'boolean'],
        ];
    }
}