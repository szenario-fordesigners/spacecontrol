<?php

namespace szenario\craftspacecontrol\models;

use craft\base\Model;

/**
 * SpaceControl settings model
 */
class Settings extends Model
{
    // General settings
    public float $diskTotalSpace = 0.0;
    public int $diskUsageAbsolute = 0;
    public float $diskUsagePercent = 0.0;
    public bool $addDatabaseToTotalSize = false;
    public bool $isInitialized = false;
    public int $lastCalculationTime = 0;

    // Notification settings
    public int $notificationLimitLow = 90;
    public int $notificationLimitMedium = 95;
    public int $notificationLimitHigh = 99;
    public bool $notificationLowTriggered = false;
    public bool $notificationMediumTriggered = false;
    public bool $notificationHighTriggered = false;

    // Email notification settings
    public bool $emailNotificationsEnabled = false;
    public array $emailRecipients = [];

    public function __construct($config = [])
    {
        parent::__construct($config);

        // Load settings from database after parent constructor
        $this->loadFromDatabase();
    }

    /**
     * Load settings from database
     */
    private function loadFromDatabase(): void
    {
        try {
            $plugin = \Craft::$app->getPlugins()->getPlugin('spacecontrol');
            if ($plugin) {
                $service = $plugin->get('spaceControl');
                $dbSettings = $service->getAllSettings();

                // Set properties from database
                foreach ($dbSettings as $key => $value) {
                    if (property_exists($this, $key)) {
                        $this->$key = $value;
                    }
                }
            }
        } catch (\Throwable $e) {
            // If database isn't ready, use default values
            \Craft::warning('Could not load SpaceControl settings from database: ' . $e->getMessage(), 'spacecontrol');
        }
    }
}
