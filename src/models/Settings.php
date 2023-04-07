<?php

namespace szenario\craftspacecontrol\models;

use Craft;
use craft\base\Model;

/**
 * spacecontrol settings
 */
class Settings extends Model
{
    public $lastSent = 0;
    // 1 day
    public $mailTimeThreshold = 86400;
    // 90 %
    public $diskLimitPercent = 90;

    public $adminRecipients = [];
    public $clientRecipients = [];

    public function defineRules(): array
    {
        return [
            [['lastSent', 'mailTimeThreshold', 'diskLimitPercent', 'adminRecipients', 'clientRecipients'], 'required'],
        ];
    }
}
