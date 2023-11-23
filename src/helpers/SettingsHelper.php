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

    // PLUGIN SETTINGS SETTER
    public static function setValue($key, $value)
    {
        $plugin = Craft::$app->getPlugins()->getPlugin('spacecontrol');
        if ($plugin === null) return;
        $settings = $plugin->getSettings();
        $settings->$key = $value;

        Craft::$app->getPlugins()->savePluginSettings($plugin, $settings->toArray());
    }
}