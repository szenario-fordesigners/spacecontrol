<?php

namespace szenario\craftspacecontrol\assetbundles\spacecontrol;


use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Sean Hill
 * @package   Plausible
 * @since     1.0.0
 */
class SpaceControlAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@szenario/craftspacecontrol/assetbundles/spacecontrol/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/frappe-charts.min.iife.js',
            'js/main.js'
        ];

        $this->css = [
            'css/SpaceControl.css'
        ];

        parent::init();
    }
}
