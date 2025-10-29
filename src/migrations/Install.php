<?php

namespace szenario\craftspacecontrol\migrations;

use craft\db\Migration;

class Install extends Migration
{
    private $tableNamePluginData = 'spacecontrol_plugin_data';
    private $tableNameFileSizes = 'spacecontrol_file_sizes';

    public function safeUp(): bool
    {
        // create plugin data table
        if (!$this->db->tableExists($this->tableNamePluginData)) {
            $this->createTable($this->tableNamePluginData, [
                'id' => $this->primaryKey(),
                'key' => $this->string()->notNull()->unique(),
                'value' => $this->text()->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
            ]);

            // Insert default plugin data
            $this->insert($this->tableNamePluginData, [
                'key' => 'diskUsageAbsolute',
                'value' => '0',
                'dateCreated' => new \yii\db\Expression('NOW()'),
                'dateUpdated' => new \yii\db\Expression('NOW()'),
            ]);

            $this->insert($this->tableNamePluginData, [
                'key' => 'diskUsagePercent',
                'value' => '0.0',
                'dateCreated' => new \yii\db\Expression('NOW()'),
                'dateUpdated' => new \yii\db\Expression('NOW()'),
            ]);

            $this->insert($this->tableNamePluginData, [
                'key' => 'isInitialized',
                'value' => '0',
                'dateCreated' => new \yii\db\Expression('NOW()'),
                'dateUpdated' => new \yii\db\Expression('NOW()'),
            ]);

            $this->insert($this->tableNamePluginData, [
                'key' => 'lastCalculationTime',
                'value' => '0',
                'dateCreated' => new \yii\db\Expression('NOW()'),
                'dateUpdated' => new \yii\db\Expression('NOW()'),
            ]);

            $this->insert($this->tableNamePluginData, [
                'key' => 'notificationLimitLow',
                'value' => '90',
                'dateCreated' => new \yii\db\Expression('NOW()'),
                'dateUpdated' => new \yii\db\Expression('NOW()'),
            ]);

            $this->insert($this->tableNamePluginData, [
                'key' => 'notificationLimitMedium',
                'value' => '95',
                'dateCreated' => new \yii\db\Expression('NOW()'),
                'dateUpdated' => new \yii\db\Expression('NOW()'),
            ]);

            $this->insert($this->tableNamePluginData, [
                'key' => 'notificationLimitHigh',
                'value' => '99',
                'dateCreated' => new \yii\db\Expression('NOW()'),
                'dateUpdated' => new \yii\db\Expression('NOW()'),
            ]);

            $this->insert($this->tableNamePluginData, [
                'key' => 'notificationLowTriggered',
                'value' => '0',
                'dateCreated' => new \yii\db\Expression('NOW()'),
                'dateUpdated' => new \yii\db\Expression('NOW()'),
            ]);

            $this->insert($this->tableNamePluginData, [
                'key' => 'notificationMediumTriggered',
                'value' => '0',
                'dateCreated' => new \yii\db\Expression('NOW()'),
                'dateUpdated' => new \yii\db\Expression('NOW()'),
            ]);

            $this->insert($this->tableNamePluginData, [
                'key' => 'notificationHighTriggered',
                'value' => '0',
                'dateCreated' => new \yii\db\Expression('NOW()'),
                'dateUpdated' => new \yii\db\Expression('NOW()'),
            ]);
        }

        // create file sizes table
        if (!$this->db->tableExists($this->tableNameFileSizes)) {
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
            $this->createIndex(null, $this->tableNameFileSizes, ['pathHash']);
        }

        return true;
    }

    public function safeDown(): bool
    {
        $this->dropTableIfExists($this->tableNamePluginData);
        $this->dropTableIfExists($this->tableNameFileSizes);
        return true;
    }
}