<?php

namespace szenario\craftspacecontrol\models;

use craft\base\Model;

/**
 * spacecontrol settings
 */
class Settings extends Model
{
//    public $lastSent = 0;
//    // 1 day
//    public $mailTimeThreshold = 86400;
//    // 90 %
//    public $diskLimitPercent = 90;
//
//    public $adminRecipients = '';
//    public $clientRecipients = '';
//
//    private $admins = [];


    public $diskTotalSpace = 1;
    public $diskUsageAbsolute = 0;
    public $diskUsagePercent = 0;
    public $isInitialized = false;

//    function __construct($config = [])
//    {
//        parent::__construct($config);
//
//        $this->admins = User::find()
//            ->admin(true)
//            ->all();
//
//        foreach ($this->admins as $admin) {
//            $this->adminRecipients .= $admin->email . ', ';
//        }
//
//        $this->adminRecipients = substr($this->adminRecipients, 0, -2);
//    }

    public function defineRules(): array
    {
        return [
            [
                [
//                    'lastSent',
//                    'mailTimeThreshold',
//                    'diskLimitPercent',
                    'diskTotalSpace',
                    'diskUsageAbsolute',
                    'diskUsagePercent'],
                'required'],
        ];
    }
}
