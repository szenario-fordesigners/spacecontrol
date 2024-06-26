<?php

namespace szenario\craftspacecontrol;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\elements\Entry;
use craft\events\ModelEvent;
use craft\helpers\ElementHelper;
use craft\events\PluginEvent;
use craft\helpers\UrlHelper;
use craft\services\Plugins;
use yii\base\Event;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Dashboard;
use szenario\craftspacecontrol\models\Settings;
use szenario\craftspacecontrol\widgets\SpaceControlWidget;
use szenario\craftspacecontrol\jobs\SpaceControlChecker;
use szenario\craftspacecontrol\assetbundles\spacecontrol\SpaceControlSettingsAsset;
use craft\web\View;
use craft\events\TemplateEvent;
use putyourlightson\sprig\Sprig;

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

        Sprig::bootstrap();

        // override crafts 1000 bit base used for formatting
        Craft::$app->formatter->sizeFormatBase = 1024;

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


//        check disk space after file upload
        Event::on(
            Entry::class,
            Entry::EVENT_AFTER_SAVE,
            function (ModelEvent $event) {
                if (
                    !ElementHelper::isDraft($event->sender) &&
                    !ElementHelper::rootElement($event->sender)->isProvisionalDraft
                ) {
                    \craft\helpers\Queue::push(new SpaceControlChecker());
                }
            }
        );

//        check disk space after user logged in. this is no background job.
        Event::on(\yii\web\User::class,
            \yii\web\User::EVENT_AFTER_LOGIN,
            function (\yii\web\UserEvent $event) {
                \craft\helpers\Queue::push(new SpaceControlChecker());
            });


//        check disk space after settings template is rendered
//        this is gets triggered e.g. when the webspace setting is changed
        Event::on(
            View::class,
            View::EVENT_BEFORE_RENDER_TEMPLATE,
            function (TemplateEvent $event) {
                if ($event->template == "assets/_index" && $event->templateMode == "cp") {
                    \craft\helpers\Queue::push(new SpaceControlChecker());
                }
            }
        );


        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    \craft\helpers\Queue::push(new SpaceControlChecker());

                    try {
                        // add widget to dashboard
                        Craft::$app->dashboard->saveWidget(
                            Craft::$app->dashboard->createWidget([
                                'type' => SpaceControlWidget::class,
                                'settings' => [
                                    'colspan' => 1,
                                ]
                            ])
                        );
                    } catch (\Throwable $e) {
                        // when installing via CLI, the dashboard is not available and widget install will fail
                        Craft::warning('Could not save widget: ' . $e->getMessage(), 'spacecontrol');
                    }


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

        // only inject js on control panel requests
        // Remove the "novalidate" attribute from the form to ensure E-Mail validation.
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            // Load JS before page template is rendered


            Event::on(
                View::class,
                View::EVENT_BEFORE_RENDER_PAGE_TEMPLATE,
                function (TemplateEvent $event) {
                    if ($event->template == 'settings/plugins/_settings.twig') {
                        // Get view
                        Craft::$app->getView()->registerAssetBundle(SpaceControlSettingsAsset::class);


                        $view = Craft::$app->getView();



                        //$view->registerJs('let mainForm = document.getElementById("main-form");if (mainForm) {mainForm.removeAttribute("novalidate");}', View::POS_END);
                    }
                }
            );
        }
    }
}