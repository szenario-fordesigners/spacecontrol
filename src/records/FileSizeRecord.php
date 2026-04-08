<?php

namespace szenario\craftspacecontrol\records;

use craft\db\ActiveRecord;

class FileSizeRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%spacecontrol_file_sizes}}';
    }

    public function rules(): array
    {
        return [
            [['pathHash', 'path', 'sizeInBytes', 'lastChecked'], 'required'],
            [['path'], 'string'],
            [['sizeInBytes', 'mtime'], 'integer'],
            [['lastChecked'], 'safe'],
            [['pathHash'], 'string', 'max' => 64],
            [['pathHash'], 'unique'],
        ];
    }
}


