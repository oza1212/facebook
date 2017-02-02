<?php

namespace dukt\facebook;

use craft\web\UrlManager;
use yii\base\Event;
use craft\events\RegisterUrlRulesEvent;
use dukt\facebook\models\Settings;

class Plugin extends \craft\base\Plugin
{
    public $hasSettings = true;

    // Public Methods
    // =========================================================================
    public function init()
    {
        parent::init();

        $this->setComponents([
            'facebook' => \dukt\facebook\services\Facebook::class,
            'facebook_api' => \dukt\facebook\services\Api::class,
            'facebook_cache' => \dukt\facebook\services\Cache::class,
            'facebook_oauth' => \dukt\facebook\services\Oauth::class,
        ]);

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, [$this, 'registerCpUrlRules']);
    }

    public function registerCpUrlRules(RegisterUrlRulesEvent $event)
    {
        $rules = [
            'facebook/settings' => 'facebook/settings/index',
            'facebook/install' => 'facebook/install/index',
        ];

        $event->rules = array_merge($event->rules, $rules);
    }

    /**
     * Get Required Plugins
     */
    public function getRequiredPlugins()
    {
        return array(
            array(
                'name' => "OAuth",
                'handle' => 'oauth',
                'url' => 'https://dukt.net/craft/oauth',
                'version' => '2.0.2'
            )
        );
    }

    /**
     * Get Settings URL
     */
    public function getSettingsUrl()
    {
        return 'facebook/settings';
    }

    /**
     * On Before Uninstall
     */
    public function onBeforeUninstall()
    {
        if(isset(craft()->oauth))
        {
            craft()->oauth->deleteTokensByPlugin('facebook');
        }
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
     */
    public function getSettingsResponse()
    {
        $url = \craft\helpers\UrlHelper::cpUrl('facebook/settings');

        \Craft::$app->controller->redirect($url);

        return '';
    }

    /**
     * Defined Settings
     */
    protected function defineSettings()
    {
        return array(
            'tokenId' => array(AttributeType::Number),
            'facebookInsightsObjectId' => array(AttributeType::String),
        );
    }
}
