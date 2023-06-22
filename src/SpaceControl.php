<?php

namespace szenario\craftspacecontrol;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\controllers\DashboardController;
use craft\events\LocateUploadedFilesEvent;
use craft\events\PluginEvent;
use craft\fields\Assets;
use craft\helpers\UrlHelper;
use craft\services\Plugins;
use szenario\craftspacecontrol\models\Settings;
use yii\base\ActionEvent;
use yii\base\Event;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Dashboard;
use szenario\craftspacecontrol\widgets\SpaceControlWidget;
use szenario\craftspacecontrol\jobs\SpaceControlChecker;

/**
 * spacecontrol plugin
 *
 * @author szenario.design <support@szenario.design>
 * @copyright szenario.design
 * @license MIT
 */
class SpaceControl extends Plugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;

    protected function settingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('spacecontrol/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }

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
            DashboardController::class,
            DashboardController::EVENT_AFTER_ACTION,
            function (ActionEvent $event) {
                \craft\helpers\Queue::push(new SpaceControlChecker());
            }
        );

        Event::on(
            Assets::class,
            Assets::EVENT_LOCATE_UPLOADED_FILES,
            function (LocateUploadedFilesEvent $event) {
                \craft\helpers\Queue::push(new SpaceControlChecker());
            }
        );

//        TODO: after login, after save CP run jon


        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    // Send them to our welcome screen
                    $request = Craft::$app->getRequest();
                    if ($request->isCpRequest) {
                        Craft::$app->getResponse()->redirect(UrlHelper::cpUrl(
                            'settings/plugins/spacecontrol',
                            [
                                'showWelcome' => true,
                            ]
                        ))->send();
                    }
                }
            }
        );
    }
}
