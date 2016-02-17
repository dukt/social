<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class SocialPlugin extends BasePlugin
{
    // Public Methods
    // =========================================================================

    /**
     * Initialization
     */
    public function init()
    {
        $this->initEventListeners();

        parent::init();
    }

    /**
     * Get Social Login Providers
     */
    public function getSocialLoginProviders()
    {
        require_once(CRAFT_PLUGINS_PATH.'social/providers/login/BaseProvider.php');
        require_once(CRAFT_PLUGINS_PATH.'social/providers/login/Facebook.php');
        require_once(CRAFT_PLUGINS_PATH.'social/providers/login/Google.php');
        require_once(CRAFT_PLUGINS_PATH.'social/providers/login/Twitter.php');

        return [
            'Dukt\Social\LoginProviders\Facebook',
            'Dukt\Social\LoginProviders\Google',
            'Dukt\Social\LoginProviders\Twitter',
        ];
    }


    /**
     * Get Required Dependencies
     */
    public function getRequiredPlugins()
    {
        return [
            [
                'name'    => "OAuth",
                'handle'  => 'oauth',
                'url'     => 'https://dukt.net/craft/oauth',
                'version' => '1.0.0'
            ]
        ];
    }

    /**
     * Get Name
     */
    public function getName()
    {
        return Craft::t('Social Login');
    }

    /**
     * Get Version
     */
    public function getVersion()
    {
        $path = CRAFT_PLUGINS_PATH.'social/Info.php';

        if(IOHelper::fileExists($path))
        {
            require_once($path);

            return SOCIAL_VERSION;
        }

        return '1.0.0';
    }


    /**
     * Get SchemaVersion
     */
    public function getSchemaVersion()
    {
        return '1.0.1';
    }

    /**
     * Get Developer
     */
    public function getDeveloper()
    {
        return 'Dukt';
    }

    /**
     * Get Developer URL
     */
    public function getDeveloperUrl()
    {
        return 'https://dukt.net/';
    }

    /**
     * Get Documentation URL
     */
    public function getDocumentationUrl()
    {
        return 'https://dukt.net/craft/social/docs/';
    }

    /**
     * Get Release Feed URL
     */
    public function getReleaseFeedUrl()
    {
        return 'https://dukt.net/craft/social/updates.json';
    }

    /**
     * Get Settings URL
     */
    public function getSettingsUrl()
    {
        return 'social/settings';
    }

    /**
     * Has CP Section
     */
    public function hasCpSection()
    {
        if(craft()->config->get('showCpSection', 'social') === true)
        {
            return true;
        }

        return false;
    }

    /**
     * Hook Register CP Routes
     */
    public function registerCpRoutes()
    {
        return [
            "social" => ['action' => "social/loginAccounts/index"],

            'social/install' => ['action' => "social/plugin/install"],

            "social/loginaccounts" => ['action' => "social/loginAccounts/index"],
            "social/loginaccounts/(?P<userId>\d+)" => ['action' => "social/loginAccounts/edit"],

            'social/loginproviders' => ['action' => "social/loginProviders/index"],
            'social/loginproviders/(?P<handle>.*)' => ['action' => "social/loginProviders/edit"],

            'social/settings' => ['action' => "social/settings/index"],
        ];
    }

    /**
     * On Before Uninstall
     */
    public function onBeforeUninstall()
    {
        if (isset(craft()->oauth))
        {
            craft()->oauth->deleteTokensByPlugin('social');
        }
    }

    // Protected Methods
    // =========================================================================

    /**
     * Define Settings
     */
    protected function defineSettings()
    {
        return [
            'enableSocialRegistration' => [AttributeType::Bool, 'default' => true],
            'enableSocialLogin' => [AttributeType::Bool, 'default' => true],
            'loginProviders' => [AttributeType::Mixed],
            'defaultGroup' => [AttributeType::Number, 'default' => null],
            'autoFillProfile' => [AttributeType::Bool, 'default' => true],
        ];
    }

    // Private Methods
    // =========================================================================

    /**
     * Initialize event listeners
     */
    private function initEventListeners()
    {
        // delete social user when craft user is deleted

        craft()->on('users.onBeforeDeleteUser', function (Event $event)
        {
            $user = $event->params['user'];

            craft()->social_loginAccounts->deleteLoginAccountByUserId($user->id);
        });
    }
}
