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
    public $mailTimeTreshold = 86400;

    // 5 GB
    public $diskLimitAbsolute = 5 * 1024 * 1024 * 1024;
    // 10 %
    public $diskLimitPercent = 10;

    public function defineRules(): array
    {
        return [
            [['lastSent', 'mailTimeTreshold', 'diskLimitAbsolute', 'diskLimitPercent'], 'required'],
        ];
    }
}
