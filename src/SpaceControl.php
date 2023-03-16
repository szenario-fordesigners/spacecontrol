<?php

namespace szenario\craftspacecontrol;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use szenario\craftspacecontrol\models\Settings;
use yii\base\Event;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Dashboard;
use szenario\craftspacecontrol\widgets\SpaceControlWidget;
use szenario\craftspacecontrol\jobs\SpaceControlChecker;
use craft\web\View;
use craft\events\TemplateEvent;

/**
 * spacecontrol plugin
 *
 * @method static SpaceControl getInstance()
 * @method Settings getSettings()
 * @author szenario.design <support@szenario.design>
 * @copyright szenario.design
 * @license MIT
 */
class SpaceControl extends Plugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;

    public static function config(): array
    {
        return [
            'components' => [
                // Define component configs here...
            ],
        ];
    }

    public function init()
    {
        parent::init();

        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function () {
            $this->attachEventHandlers();
        });
    }

    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('spacecontrol/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }

    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/4.x/extend/events.html to get started)
        Event::on(
            Dashboard::class,
            Dashboard::EVENT_REGISTER_WIDGET_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = SpaceControlWidget::class;
            }
        );

        Event::on(
            View::class,
            View::EVENT_AFTER_RENDER_TEMPLATE,
            function (TemplateEvent $event) {
                \craft\helpers\Queue::push(new SpaceControlChecker());
            }
        );
    }
}
