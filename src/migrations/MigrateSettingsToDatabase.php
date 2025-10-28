<?php

namespace szenario\craftspacecontrol\migrations;

use craft\db\Migration;
use szenario\craftspacecontrol\services\SpaceControlService;

class MigrateSettingsToDatabase extends Migration
{
    public function safeUp(): bool
    {
        // Initialize the service
        $service = new SpaceControlService();

        // Migrate existing Craft plugin settings to database
        $migrationSuccess = $service->migrateFromCraftSettings();

        if ($migrationSuccess) {
            echo "Successfully migrated settings from Craft plugin settings to database.\n";
        } else {
            echo "Warning: Some settings may not have been migrated successfully.\n";
        }

        return true;
    }

    public function safeDown(): bool
    {
        // This migration is not reversible as we don't want to lose database settings
        echo "This migration cannot be reversed. Database settings will be preserved.\n";
        return true;
    }
}

