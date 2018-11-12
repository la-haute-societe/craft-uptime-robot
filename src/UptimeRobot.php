<?php
/**
 * Uptime Robot plugin for Craft CMS 3.x
 *
 * Connect your Craft CMS sites to Uptime Robot monitoring service.
 *
 * @link      https://www.lahautesociete.com
 * @copyright Copyright (c) 2018 La Haute Société
 */

namespace lhs\uptimerobot;

use Craft;
use craft\base\Plugin;
use craft\console\Application as ConsoleApplication;
use craft\events\PluginEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\services\Dashboard;
use craft\services\Plugins;
use craft\web\Application;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use lhs\uptimerobot\helpers\UptimeRobotHelper;
use lhs\uptimerobot\models\Settings;
use lhs\uptimerobot\services\UptimeRobotService as UptimeRobotService;
use lhs\uptimerobot\twigextensions\UptimeRobotTwigExtension;
use lhs\uptimerobot\variables\UptimeRobotVariable;
use lhs\uptimerobot\widgets\UptimeRobotWidget as UptimeRobotWidget;
use yii\base\Event;
use yii\httpclient\debug\HttpClientPanel;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://craftcms.com/docs/plugins/introduction
 *
 * @author    La Haute Société
 * @package   UptimeRobot
 * @since     1.0.0
 *
 * @property  UptimeRobotService $service
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class UptimeRobot extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * UptimeRobot::$plugin
     *
     * @var UptimeRobot
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * UptimeRobot::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();

        $this->setComponents([
            'service' => new UptimeRobotService(['apiKey' => $this->getSettings()->apiKey])
        ]);

        self::$plugin = $this;

        // Add in our Twig extensions
        Craft::$app->view->registerTwigExtension(new UptimeRobotTwigExtension());

        // Add in our console commands
        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'lhs\uptimerobot\console\controllers';
        }

        // Register our CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                Craft::debug(
                    'UrlManager::EVENT_REGISTER_SITE_URL_RULES',
                    __METHOD__
                );
                $event->rules['uptime-robot/add-monitor/<handle>'] = ['route' => $this->handle . '/cp/add-monitor'];
                $event->rules['uptime-robot/add-monitor'] = ['route' => $this->handle . '/cp/add-monitor'];
                $event->rules['uptime-robot/edit-monitor/<id:\d+>'] = ['route' => $this->handle . '/cp/edit-monitor'];
                $event->rules['uptime-robot/remove-monitor/<id:\d+>'] = ['route' => $this->handle . '/cp/remove-monitor'];
                $event->rules['uptime-robot'] = ['route' => $this->handle . '/cp'];
            }
        );

        // Register our widgets
        Event::on(
            Dashboard::class,
            Dashboard::EVENT_REGISTER_WIDGET_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = UptimeRobotWidget::class;
            }
        );

        // TODO: Add a listener when entry is updated or deleted to update related monitors accordingly
        // TODO: Handle user permissions
        // TODO: Add a check on the number of available monitors

        // Register our variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('uptimeRobot', UptimeRobotVariable::class);
            }
        );

        // Redirect user to main CP panel after settings been saved
        Event::on(
            Plugin::class,
            Plugin::EVENT_AFTER_SAVE_SETTINGS,
            function (Event $event) {
                if ($event->sender === $this) {
                    Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('uptime-robot'))->send();
                }
            }
        );

        // Do something after we're installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('settings/plugins/uptime-robot'))->send();
                }
            }
        );

        if(YII_DEBUG) {
            // Add the HttpClient Panel to the Yii debug bar
            Event::on(
                Application::class,
                Application::EVENT_BEFORE_REQUEST,
                function () {
                    /** @var \yii\debug\Module $debugModule */
                    $debugModule = Craft::$app->getModule('debug');
                    $debugModule->panels['httpclient'] = new HttpClientPanel(['module' => $debugModule]);
                }
            );
        }

        /**
         * Logging in Craft involves using one of the following methods:
         *
         * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
         * Craft::info(): record a message that conveys some useful information.
         * Craft::warning(): record a warning message that indicates something unexpected has happened.
         * Craft::error(): record a fatal error that should be investigated as soon as possible.
         *
         * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
         *
         * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
         * the category to the method (prefixed with the fully qualified class name) where the constant appears.
         *
         * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
         * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
         *
         * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
         */
        Craft::info(
            Craft::t(
                'uptime-robot',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'uptime-robot/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
