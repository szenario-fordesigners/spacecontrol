<?php

namespace szenario\craftspacecontrol\helpers;

use Craft;

class SettingsHelper
{
    // PLUGIN SETTINGS GETTER
    public static function getPluginSettings()
    {
        return Craft::$app->getPlugins()->getPlugin('spacecontrol')->getSettings();
    }

    public static function getSetting($key)
    {
        $settings = SettingsHelper::getPluginSettings();
        return $settings->$key;
    }

    public static function getDiskLimitPercent()
    {
        $settings = SettingsHelper::getPluginSettings();
        return [
            'diskLimitPercent' => $settings->diskLimitPercent
        ];
    }

    public static function getAdminRecipientsString()
    {
        $settings = SettingsHelper::getPluginSettings();
        return $settings->adminRecipients;

    }

    public static function getAdminRecipientsArray()
    {
        $settings = SettingsHelper::getPluginSettings();
        $adminRecipientsStr = $settings->adminRecipients;
        if (!strlen($adminRecipientsStr)) return [];

        $adminRecipients = explode(', ', $adminRecipientsStr);
        return ValidationHelper::validateEmailAddresses($adminRecipients);
    }

    public static function getClientRecipientsString()
    {
        $settings = SettingsHelper::getPluginSettings();
        return $settings->clientRecipients;
    }

    public static function getClientRecipientsArray()
    {
        $settings = SettingsHelper::getPluginSettings();
        $clientRecipientsStr = $settings->clientRecipients;
        if (!strlen($clientRecipientsStr)) return [];

        $clientRecipients = explode(', ', $clientRecipientsStr);
        return ValidationHelper::validateEmailAddresses($clientRecipients);
    }

    public static function getMailTimeThreshold()
    {
        $settings = SettingsHelper::getPluginSettings();
        return $settings->mailTimeThreshold;
    }

    public static function getLastSent()
    {
        $settings = SettingsHelper::getPluginSettings();
        return $settings->lastSent;
    }

    // PLUGIN SETTINGS SETTER
//    public static function setLastSent($time)
//    {
//        $plugin = Craft::$app->getPlugins()->getPlugin('spacecontrol');
//        if ($plugin === null) return;
//        $settings = $plugin->getSettings();
//        $settings->lastSent = $time;
//
//        Craft::$app->getPlugins()->savePluginSettings($plugin, $settings->toArray());
//    }

    public static function setValue($key, $value)
    {
        $plugin = Craft::$app->getPlugins()->getPlugin('spacecontrol');
        if ($plugin === null) return;
        $settings = $plugin->getSettings();
        $settings->$key = $value;

        Craft::$app->getPlugins()->savePluginSettings($plugin, $settings->toArray());
    }
}