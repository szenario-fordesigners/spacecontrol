<?php

namespace szenario\craftspacecontrol\migrations;

use craft\db\Migration;

class m260408_120000_migrate_settings_to_database extends Migration
{
    private string $tableNamePluginData = '{{%spacecontrol_plugin_data}}';
    private string $tableNameFileSizes = '{{%spacecontrol_file_sizes}}';

    private const MIGRATED_KEYS = [
        'diskUsageAbsolute' => '0',
        'diskUsagePercent' => '0.0',
        'isInitialized' => '0',
        'lastCalculationTime' => '0',
        'notificationLimitLow' => '90',
        'notificationLimitMedium' => '95',
        'notificationLimitHigh' => '99',
        'notificationLowTriggered' => '0',
        'notificationMediumTriggered' => '0',
        'notificationHighTriggered' => '0',
    ];

    public function safeUp(): bool
    {
        echo "    > starting spacecontrol settings migration to database tables\n";

        $this->createPluginDataTable();
        $this->createFileSizesTable();
        $this->migrateSettingsToPluginData();

        echo "    > spacecontrol settings migration completed\n";
        return true;
    }

    public function safeDown(): bool
    {
        return false;
    }

    private function createPluginDataTable(): void
    {
        if ($this->db->tableExists($this->tableNamePluginData)) {
            echo "    > table spacecontrol_plugin_data already exists, skipping creation\n";
            return;
        }

        $this->createTable($this->tableNamePluginData, [
            'id' => $this->primaryKey(),
            'key' => $this->string()->notNull()->unique(),
            'value' => $this->text()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
        ]);

        echo "    > created table spacecontrol_plugin_data\n";
    }

    private function createFileSizesTable(): void
    {
        if ($this->db->tableExists($this->tableNameFileSizes)) {
            echo "    > table spacecontrol_file_sizes already exists, skipping creation\n";
            return;
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
        $this->createIndex(null, $this->tableNameFileSizes, ['pathHash']);
        $this->createIndex(null, $this->tableNameFileSizes, ['sizeInBytes']);

        echo "    > created table spacecontrol_file_sizes with indexes\n";
    }

    private function migrateSettingsToPluginData(): void
    {
        $projectConfigSettings = \Craft::$app->getProjectConfig()->get('plugins.spacecontrol.settings') ?? [];

        $migratedCount = 0;
        $skippedCount = 0;

        foreach (self::MIGRATED_KEYS as $key => $default) {
            $exists = (new \yii\db\Query())
                ->from($this->tableNamePluginData)
                ->where(['key' => $key])
                ->exists();

            if ($exists) {
                echo "    > skipping '{$key}': already exists in plugin_data table\n";
                $skippedCount++;
                continue;
            }

            $value = $default;
            $source = 'default';
            if (array_key_exists($key, $projectConfigSettings)) {
                $raw = $projectConfigSettings[$key];
                $value = is_bool($raw) ? ($raw ? '1' : '0') : (string) $raw;
                $source = 'project config';
            }

            $this->insert($this->tableNamePluginData, [
                'key' => $key,
                'value' => $value,
                'dateCreated' => new \yii\db\Expression('NOW()'),
                'dateUpdated' => new \yii\db\Expression('NOW()'),
            ]);

            echo "    > migrated '{$key}' = '{$value}' (source: {$source})\n";
            $migratedCount++;
        }

        echo "    > settings migration summary: {$migratedCount} migrated, {$skippedCount} skipped\n";
    }
}
