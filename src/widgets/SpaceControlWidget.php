<?php

namespace szenario\craftspacecontrol\widgets;


use Craft;
use craft\base\Widget;
use szenario\craftspacecontrol\assetbundles\spacecontrol\SpaceControlAsset;
use szenario\craftspacecontrol\helpers\ConversionHelper;
use szenario\craftspacecontrol\helpers\SettingsHelper;

class SpaceControlWidget extends Widget
{
    public static function displayName(): string
    {
        return Craft::t('spacecontrol', 'Space Control');
    }

    public static function icon(): ?string
    {
        return Craft::getAlias("@szenario/craftspacecontrol/assetbundles/spacecontrol/dist/img/icon.svg");
    }

    public static function maxColspan(): ?int
    {
        return null;
    }

    public function getTitle(): ?string
    {
        return "";
    }

    public function getBodyHtml(): ?string
    {
        Craft::$app->getView()->registerAssetBundle(SpaceControlAsset::class);

        $view = Craft::$app->getView();
        $view->registerJs(
            "new Craft.SpaceControlWidget($this->id);"
        );

        $diskUsageAbsoluteRaw = SettingsHelper::getSetting("diskUsageAbsolute");
        $diskUsageAbsoluteHumanReadable = ConversionHelper::getHumanReadableSize($diskUsageAbsoluteRaw);

        $diskUsagePercentRaw = SettingsHelper::getSetting("diskUsagePercent");
        $diskUsagePercentRounded = round($diskUsagePercentRaw);

        return Craft::$app->getView()->renderTemplate(
            'spacecontrol/_components/widgets/SpaceControlWidget/body',
            [
                "diskUsageAbsolute" => $diskUsageAbsoluteHumanReadable,
                "diskUsagePercent" => $diskUsagePercentRounded,
            ]
        );
    }
}
