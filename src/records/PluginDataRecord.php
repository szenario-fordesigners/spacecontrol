<?php

namespace szenario\craftspacecontrol\records;

use craft\db\ActiveRecord;

/**
 * PluginData record
 */
class PluginDataRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%spacecontrol_plugin_data}}';
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['key'], 'required'],
            [['key'], 'string', 'max' => 255],
            [['value'], 'string'],
            [['key'], 'unique'],
        ];
    }
}
