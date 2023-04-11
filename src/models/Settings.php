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

    private $admins = [];
    private $adminEmails = [];


    public $adminRecipients = '';
    public $clientRecipients = '';

    function __construct($config = [])
    {
        parent::__construct($config);

        $this->admins = User::find()
            ->admin(true)
            ->all();

        array_walk($this->admins, function($value, $key) {
            $this->adminEmails[] = $value->email;
            $this->adminRecipients .= $value->email . ', ';
        });

        $this->adminRecipients = substr($this->adminRecipients, 0, -2);
    }

    public function defineRules(): array
    {
        return [
            [['lastSent', 'mailTimeThreshold', 'diskLimitPercent', 'adminRecipients', 'clientRecipients'], 'required'],
        ];
    }
}
