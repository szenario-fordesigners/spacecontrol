<?php

namespace szenario\craftspacecontrol\records;

use craft\db\ActiveRecord;

class SettingsRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%spacecontrol_settings}}';
    }

    public function rules(): array
    {
        return [
            [['setting'], 'required'],
            [['setting'], 'string', 'max' => 255],
            [['value'], 'string'],
            [['setting'], 'unique'],
        ];
    }
}


