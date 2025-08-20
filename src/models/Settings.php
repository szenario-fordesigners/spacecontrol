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
    public float $diskTotalSpace = 0.0;
    public int $diskUsageAbsolute = 0;
    public float $diskUsagePercent = 0.0;
    public bool $addDatabaseToTotalSize = false;
    public bool $isInitialized = false;
    public int $lastCalculationTime = 0;

    // notification settings
    public int $notificationLimitLow = 90;
    public int $notificationLimitMedium = 95;
    public int $notificationLimitHigh = 99;

    public bool $notificationLowTriggered = false;
    public bool $notificationMediumTriggered = false;
    public bool $notificationHighTriggered = false;

    // email notification settings
    public bool $emailNotificationsEnabled = false;
    public array $emailRecipients = [];

    public function defineRules(): array
    {
        return [
            [
                ['diskTotalSpace'],
                'required'
            ],
            [
                ['diskTotalSpace'],
                'number',
                'min' => 0
            ],
            [
                ['notificationLimitLow', 'notificationLimitMedium', 'notificationLimitHigh'],
                'number',
                'min' => 0,
                'max' => 100
            ],
            [
                ['notificationLimitLow'],
                'number',
                'max' => 'notificationLimitMedium'
            ],
            [
                ['notificationLimitMedium'],
                'number',
                'max' => 'notificationLimitHigh'
            ],
            [['addDatabaseToTotalSize', 'emailNotificationsEnabled'], 'boolean'],
            [
                ['emailRecipients'],
                'validateEmailRecipients'
            ],
        ];
    }

    public function validateEmailRecipients($attribute, $params)
    {
        if (!is_array($this->$attribute)) {
            $this->addError($attribute, 'Email recipients must be an array.');
            return;
        }

        foreach ($this->$attribute as $index => $recipient) {
            if (!is_array($recipient) || !isset($recipient[0])) {
                $this->addError($attribute, "Invalid email recipient format at index {$index}.");
                continue;
            }

            $email = $recipient[0];
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addError($attribute, "Invalid email address '{$email}' at index {$index}.");
            }
        }
    }
}