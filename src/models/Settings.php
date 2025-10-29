<?php

namespace szenario\craftspacecontrol\models;

use craft\base\Model;

/**
 * SpaceControl user-editable settings model
 */
class Settings extends Model
{
    // User-editable settings
    public float $diskTotalSpace = 0.0;
    public bool $addDatabaseToTotalSize = false;
    public bool $emailNotificationsEnabled = false;
    public array $emailRecipients = [];

    public function defineRules(): array
    {
        return [
            [['diskTotalSpace'], 'required'],
            [['diskTotalSpace'], 'number', 'min' => 0],
            [['addDatabaseToTotalSize', 'emailNotificationsEnabled'], 'boolean'],
            [['emailRecipients'], 'validateEmailRecipients'],
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
