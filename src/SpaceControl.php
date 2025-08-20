<?php

namespace szenario\craftspacecontrol;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
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
 * @license proprietary
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


        // Check disk space after asset operations (upload, move, delete)
        Event::on(
            \craft\elements\Asset::class,
            \craft\elements\Asset::EVENT_AFTER_PROPAGATE,
            function (ModelEvent $event) {
                $asset = $event->sender;

                // Check if it's a draft or provisional draft
                if (ElementHelper::isDraft($asset)) {
                    return;
                }

                if (ElementHelper::rootElement($asset)->isProvisionalDraft) {
                    return;
                }

                $job = new SpaceControlChecker();
                $job->skipThrottling = true;
                \craft\helpers\Queue::push($job);
            }
        );

        Event::on(
            \craft\elements\Asset::class,
            \craft\elements\Asset::EVENT_AFTER_DELETE,
            function (\yii\base\Event $event) {
                $job = new SpaceControlChecker();
                $job->skipThrottling = true;
                \craft\helpers\Queue::push($job);
            }
        );

        // Check disk space after CP admin login (but not front-end logins)
        Event::on(
            \yii\web\User::class,
            \yii\web\User::EVENT_AFTER_LOGIN,
            function (\yii\web\UserEvent $event) {
                $request = Craft::$app->getRequest();
                if ($request->isCpRequest && $event->identity->admin) {
                    \craft\helpers\Queue::push(new SpaceControlChecker());
                }
            }
        );


        // Check disk space after plugin settings are saved
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_SAVE_PLUGIN_SETTINGS,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    $job = new SpaceControlChecker();
                    $job->skipThrottling = true;
                    \craft\helpers\Queue::push($job);
                }
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
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


        // Register settings assets only for our plugin's settings page
        Event::on(
            View::class,
            View::EVENT_BEFORE_RENDER_PAGE_TEMPLATE,
            function (TemplateEvent $event) {
                if ($event->template == 'settings/plugins/_settings.twig') {
                    // Check if this is our plugin's settings page
                    $request = Craft::$app->getRequest();
                    if ($request->getSegment(3) === 'spacecontrol') {
                        Craft::$app->getView()->registerAssetBundle(SpaceControlSettingsAsset::class);
                    }
                }
            }
        );
    }
}