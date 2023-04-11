<?php

namespace szenario\craftspacecontrol\models;

use Craft;
use craft\base\Model;
use craft\elements\User;
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

    public $adminRecipients = '';
    public $clientRecipients = '';

    private $admins = [];

    function __construct($config = [])
    {
        parent::__construct($config);

        $this->admins = User::find()
            ->admin(true)
            ->all();

        foreach($this->admins as $admin) {
            $this->adminRecipients .= $admin->email . ', ';
        }

        $this->adminRecipients = substr($this->adminRecipients, 0, -2);
    }

    public function defineRules(): array
    {
        return [
            [['lastSent', 'mailTimeThreshold', 'diskLimitPercent'], 'required'],
        ];
    }
}
