<?php

namespace szenario\craftspacecontrol\migrations;

use craft\db\Migration;
class Install extends Migration
{
    private $tableNameSettings = 'spacecontrol_settings';
    private $tableNameFileSizes = 'spacecontrol_file_sizes';
    public function safeUp(): bool
    {
        // create settings table
        if ($this->db->tableExists($this->tableNameSettings)) {
            return true;
        }

        $this->createTable($this->tableNameSettings, [
            'id' => $this->primaryKey(),
            'setting' => $this->string()->notNull()->unique(),
            'value' => $this->text()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
        ]);


        // Insert default settings
        $this->insert($this->tableNameSettings, [
            'setting' => 'diskTotalSpace',
            'value' => '0.0',
            'dateCreated' => new \yii\db\Expression('NOW()'),
            'dateUpdated' => new \yii\db\Expression('NOW()'),
        ]);

        $this->insert($this->tableNameSettings, [
            'setting' => 'diskUsageAbsolute',
            'value' => '0',
            'dateCreated' => new \yii\db\Expression('NOW()'),
            'dateUpdated' => new \yii\db\Expression('NOW()'),
        ]);

        $this->insert($this->tableNameSettings, [
            'setting' => 'diskUsagePercent',
            'value' => '0.0',
            'dateCreated' => new \yii\db\Expression('NOW()'),
            'dateUpdated' => new \yii\db\Expression('NOW()'),
        ]);

        $this->insert($this->tableNameSettings, [
            'setting' => 'addDatabaseToTotalSize',
            'value' => '0',
            'dateCreated' => new \yii\db\Expression('NOW()'),
            'dateUpdated' => new \yii\db\Expression('NOW()'),
        ]);

        $this->insert($this->tableNameSettings, [
            'setting' => 'isInitialized',
            'value' => '0',
            'dateCreated' => new \yii\db\Expression('NOW()'),
            'dateUpdated' => new \yii\db\Expression('NOW()'),
        ]);

        $this->insert($this->tableNameSettings, [
            'setting' => 'lastCalculationTime',
            'value' => '0',
            'dateCreated' => new \yii\db\Expression('NOW()'),
            'dateUpdated' => new \yii\db\Expression('NOW()'),
        ]);

        $this->insert($this->tableNameSettings, [
            'setting' => 'notificationLimitLow',
            'value' => '90',
            'dateCreated' => new \yii\db\Expression('NOW()'),
            'dateUpdated' => new \yii\db\Expression('NOW()'),
        ]);

        $this->insert($this->tableNameSettings, [
            'setting' => 'notificationLimitMedium',
            'value' => '95',
            'dateCreated' => new \yii\db\Expression('NOW()'),
            'dateUpdated' => new \yii\db\Expression('NOW()'),
        ]);

        $this->insert($this->tableNameSettings, [
            'setting' => 'notificationLimitHigh',
            'value' => '99',
            'dateCreated' => new \yii\db\Expression('NOW()'),
            'dateUpdated' => new \yii\db\Expression('NOW()'),
        ]);

        $this->insert($this->tableNameSettings, [
            'setting' => 'notificationLowTriggered',
            'value' => '0',
            'dateCreated' => new \yii\db\Expression('NOW()'),
            'dateUpdated' => new \yii\db\Expression('NOW()'),
        ]);

        $this->insert($this->tableNameSettings, [
            'setting' => 'notificationMediumTriggered',
            'value' => '0',
            'dateCreated' => new \yii\db\Expression('NOW()'),
            'dateUpdated' => new \yii\db\Expression('NOW()'),
        ]);

        $this->insert($this->tableNameSettings, [
            'setting' => 'notificationHighTriggered',
            'value' => '0',
            'dateCreated' => new \yii\db\Expression('NOW()'),
            'dateUpdated' => new \yii\db\Expression('NOW()'),
        ]);

        $this->insert($this->tableNameSettings, [
            'setting' => 'emailNotificationsEnabled',
            'value' => '0',
            'dateCreated' => new \yii\db\Expression('NOW()'),
            'dateUpdated' => new \yii\db\Expression('NOW()'),
        ]);

        $this->insert($this->tableNameSettings, [
            'setting' => 'emailRecipients',
            'value' => '[]',
            'dateCreated' => new \yii\db\Expression('NOW()'),
            'dateUpdated' => new \yii\db\Expression('NOW()'),
        ]);




        // create file sizes table
        if ($this->db->tableExists($this->tableNameFileSizes)) {
            return true;
        }

        $this->createTable($this->tableNameFileSizes, [
            'id' => $this->primaryKey(),
            'pathHash' => $this->string(64)->notNull()->unique(),
            'path' => $this->text()->notNull(),
            'sizeInBytes' => $this->bigInteger()->notNull(),
            'mtime' => $this->integer(),
            'lastChecked' => $this->dateTime()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex(null, $this->tableNameFileSizes, ['lastChecked']);

        return true;
    }

    public function safeDown(): bool
    {
        $this->dropTableIfExists($this->tableNameSettings);
        $this->dropTableIfExists($this->tableNameFileSizes);
        return true;
    }
}