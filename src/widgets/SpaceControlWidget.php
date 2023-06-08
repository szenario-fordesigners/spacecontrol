<?php

namespace szenario\craftspacecontrol\widgets;


use Craft;
use craft\base\Widget;
use szenario\craftspacecontrol\assetbundles\spacecontrol\SpaceControlAsset;
use szenario\craftspacecontrol\helpers\SettingsHelper;
use szenario\craftspacecontrol\helpers\ConversionHelper;
use szenario\craftspacecontrol\helpers\FolderSizeHelper;

class SpaceControlWidget extends Widget
{

    // Public Properties
    // =========================================================================
    public $lastSent = null;
    public $mailTimeThreshold = null;
    public $diskLimitPercent = null;
    public $adminRecipients = null;
    public $clientRecipients = null;

    public int $limit = 5;
    public int $timePeriod = 5;

    public $settingz;

    function __construct($config = [])
    {
        $lastSent = SettingsHelper::getLastSent();
        $mailTimeThreshold = SettingsHelper::getMailTimeThreshold();
        $diskLimitPercent = SettingsHelper::getDiskLimitPercent();
        $adminRecipients = SettingsHelper::getAdminRecipientsString();
        $clientRecipients = SettingsHelper::getClientRecipientsString();

        $this->settingz = $this->getSettings();

        parent::__construct($config);
    }


    // Static Methods
    // =========================================================================

    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = [['diskLimitPercent'], 'integer', 'min' => 1];
        $rules[] = [['diskLimitPercent'], 'integer', 'max' => 100];
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
        return Craft::getAlias("@szenario/craftspacecontrol/assetbundles/spacecontrol/dist/img/icon.svg");
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
        return "";
    }


    /**
     * @inheritdoc
     */
    public function getBodyHtml(): ?string
    {
        Craft::$app->getView()->registerAssetBundle(SpaceControlAsset::class);

        $view = Craft::$app->getView();
        $view->registerJs(
            "new Craft.SpaceControlWidget($this->id);"
        );

        $diskUsageAbsolute = SettingsHelper::getSetting("diskUsageAbsolute");
        $diskUsagePercent = SettingsHelper::getSetting("diskUsagePercent");

        return Craft::$app->getView()->renderTemplate(
            'spacecontrol/_components/widgets/SpaceControlWidget/body',
            [
                "diskUsageAbsolute" => $diskUsageAbsolute,
                "diskUsagePercent" => $diskUsagePercent,
            ]
        );
    }
}
