<?php

namespace szenario\craftspacecontrol\migrations;

use craft\db\Migration;

class Install extends Migration
{
    private $tableNamePluginData = '{{%spacecontrol_plugin_data}}';
    private $tableNameFileSizes = '{{%spacecontrol_file_sizes}}';

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
            // Defaults are applied on read by SettingsService::applyDefaults(), no seed rows needed
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
            $this->createIndex(null, $this->tableNameFileSizes, ['sizeInBytes']);
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