<?php

namespace szenario\craftspacecontrol\assetbundles\spacecontrol;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class SpaceControlAsset extends AssetBundle
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
        'js/SpaceControl.js'
    ];

    public $css = [
        'css/SpaceControl.css'
    ];
}