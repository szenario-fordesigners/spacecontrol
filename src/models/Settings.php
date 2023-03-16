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

    public function defineRules(): array
    {
        return [
            [['lastSent', 'mailTimeTreshold'], 'required'],
        ];
    }
}
