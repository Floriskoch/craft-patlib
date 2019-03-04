<?php
/**
 * Pattern Library plugin for Craft CMS 3.x
 *
 * CraftCMS Pattern Library
 *
 * @link      floriskoch.com
 * @copyright Copyright (c) 2019 Floris S. Koch
 */

namespace floriskoch\craftpatlib;


use Craft;
use craft\base\Plugin;
use craft\web\UrlManager;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\events\RegisterUrlRulesEvent;
use floriskoch\craftpatlib\models\Settings;
use yii\base\Event;

/**
 * Class PatternLibrary
 *
 * @author    Floris S. Koch
 * @package   PatternLibrary
 * @since     1.0.0
 *
 */
class CraftPatlib extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var CraftPatlib
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Craft::info(
            Craft::t(
                'craft-patlib',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );

        $this->registerRoutes();
        $this->copyComponentRendererTemplate();
    }

    // Protected Methods
    // =========================================================================

    protected function registerRoutes()
    {
        // Register pattern library routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['craft-patlib'] = 'craft-patlib/default/index';
                $event->rules['craft-patlib/component/<component:.+?>'] = 'craft-patlib/default/show';
            }
        );
    }

    protected function copyComponentRendererTemplate()
    {
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    copy(
                        $this->basePath . '/templates/componentRenderer.twig',
                        CRAFT_BASE_PATH . '/templates/componentRenderer.twig'
                    );
                }
            }
        );
    }

    protected function createSettingsModel()
    {
        return new Settings();
    }
}
