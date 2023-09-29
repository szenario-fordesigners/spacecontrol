<?php

namespace szenario\craftspacecontrol\assetbundles\spacecontrol;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class SpaceControlSettingsAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = __DIR__ . '/dist';

    /**
     * @inheritdoc
     */
    public $depends = [
        CpAsset::class,
    ];

    /**
     * @inheritdoc
     */
    public $js = [
        'js/SpaceControlSettings.js'
    ];

    public $css = [
      //  'css/SpaceControl.css',
        'css/SpaceControlSettings.css'
    ];
}