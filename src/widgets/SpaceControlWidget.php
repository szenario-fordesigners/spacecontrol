<?php

namespace szenario\craftspacecontrol\widgets;


use Craft;
use craft\base\Widget;
use szenario\craftspacecontrol\assetbundles\spacecontrol\SpaceControlAsset;

class SpaceControlWidget extends Widget
{

    // Public Properties
    // =========================================================================

    public $limit = 4;
    public $timePeriod = '30d';

    // Static Methods
    // =========================================================================

    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = [['limit'], 'integer', 'max' => 20];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('spacecontrol', 'Space Control');
    }

    public static function icon(): ?string
    {
        return Craft::getAlias("@szenario/craftspacecontrol/assetbundles/spacecontrol/dist/img/Plausible-icon.svg");
    }

    /**
     * @inheritdoc
     */
    public static function maxColspan(): ?int
    {
        return null;
    }

    // Public Methods
    // =========================================================================

    public function getTitle(): ?string
    {
        if (!isset($title)) {
            $title = "Space Control";
        }

        return $title;
    }


    /**
     * @inheritdoc
     */
    public function getBodyHtml(): ?string
    {
        Craft::$app->getView()->registerAssetBundle(SpaceControlAsset::class);

        return Craft::$app->getView()->renderTemplate(
            'spacecontrol/_components/widgets/SpaceControlWidget/body',
            [
                "disk_free_space" => disk_free_space("/"),
                "disk_total_space" => disk_total_space("/")
            ]
        );
    }
}
