<?php

namespace szenario\craftspacecontrol\migrations;

use craft\db\Migration;
use szenario\craftspacecontrol\records\PluginDataRecord;
use yii\db\Expression;

class m20241201_120000_migrate_settings_to_database extends Migration
{
    public function safeUp(): bool
    {
        try {
            // Get the plugin instance
            $plugin = \Craft::$app->getPlugins()->getPlugin('spacecontrol');
            if (!$plugin) {
                echo "Plugin not found. Migration skipped.\n";
                return true;
            }

            // Get existing Craft plugin settings from Project Config directly
            // We cannot use $plugin->getSettings() because the Settings model has already been updated
            // and no longer contains the fields we want to migrate.
            $rawSettings = \Craft::$app->getProjectConfig()->get('plugins.spacecontrol.settings') ?? [];

            if (empty($rawSettings)) {
                echo "No existing settings found in Project Config. Migration skipped.\n";
                return true;
            }

            $now = new Expression('NOW()');
            $migratedCount = 0;

            // Define which settings go to which table
            $userSettings = [
                'diskTotalSpace',
                'addDatabaseToTotalSize',
                'emailNotificationsEnabled',
                'emailRecipients',
            ];

            $pluginDataSettings = [
                'diskUsageAbsolute',
                'diskUsagePercent',
                'isInitialized',
                'lastCalculationTime',
                'notificationLimitLow',
                'notificationLimitMedium',
                'notificationLimitHigh',
                'notificationLowTriggered',
                'notificationMediumTriggered',
                'notificationHighTriggered',
            ];

            // Migrate user-editable settings to Craft's settings system
            // (These are already handled by Craft's settings system, so we just log them)
            foreach ($userSettings as $key) {
                if (array_key_exists($key, $rawSettings)) {
                    echo "User setting '{$key}' is already handled by Craft's settings system.\n";
                    $migratedCount++;
                }
            }

            // Migrate internal plugin data to spacecontrol_plugin_data table
            foreach ($pluginDataSettings as $key) {
                if (array_key_exists($key, $rawSettings)) {
                    $value = $rawSettings[$key];

                    // Handle special data types
                    if (in_array($key, ['emailRecipients'])) {
                        $value = json_encode($value);
                    } elseif (in_array($key, ['addDatabaseToTotalSize', 'isInitialized', 'notificationLowTriggered', 'notificationMediumTriggered', 'notificationHighTriggered'])) {
                        $value = $value ? '1' : '0';
                    } else {
                        $value = (string) $value;
                    }

                    // Check if record already exists
                    $existingRecord = PluginDataRecord::findOne(['key' => $key]);

                    if (!$existingRecord) {
                        $record = new PluginDataRecord();
                        $record->key = $key;
                        $record->value = $value;
                        $record->dateCreated = $now;
                        $record->dateUpdated = $now;

                        if ($record->save()) {
                            echo "Migrated plugin data: {$key}\n";
                            $migratedCount++;
                        } else {
                            echo "Failed to migrate plugin data: {$key}\n";
                        }
                    } else {
                        echo "Plugin data '{$key}' already exists in database.\n";
                    }
                }
            }

            echo "Migration completed. {$migratedCount} settings processed.\n";
            return true;

        } catch (\Throwable $e) {
            echo "Migration failed: " . $e->getMessage() . "\n";
            return false;
        }
    }

    public function safeDown(): bool
    {
        // This migration is not reversible as we don't want to lose database settings
        echo "This migration cannot be reversed. Database settings will be preserved.\n";
        return true;
    }
}

