<?php

namespace szenario\craftspacecontrol\models;
use craft\base\Model;
use craft\elements\User;

/**
 * spacecontrol settings
 */
class Settings extends Model
{
    public $lastSent = 0;
    // 1 day
//    public $mailTimeThreshold = 86400;

    public $adminRecipients = [];
    public $clientRecipients = [];

    public $diskTotalSpace = 0;
    public $diskUsageAbsolute = 0;
    public $diskUsagePercent = 0;

    public bool $dbSizeInCalc = false;

    public $isInitialized = false;

    function __construct($config = [])
    {
        parent::__construct($config);

        $admins = User::find()
            ->admin(true)
            ->all();

        foreach ($admins as $admin) {
            $this->adminRecipients[] = $admin->email;
        }
    }

    public function defineRules(): array
    {
        return [
            [
                [
//                    'lastSent',
//                    'mailTimeThreshold',
//                    'diskLimitPercent',
                    'adminRecipients',
                    'diskTotalSpace',
                    'diskUsageAbsolute',
                    'diskUsagePercent',
                    'dbSizeInCalc'],
                'required'],
            [['dbSizeInCalc'],'boolean']
        ];
    }
}
