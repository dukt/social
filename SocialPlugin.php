<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'social/vendor/autoload.php');

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
        return '1.0.42';
    }

    /**
     * Get Developer
     */
    function getDeveloper()
    {
        return 'Dukt';
    }

    /**
     * Get Developer URL
     */
    function getDeveloperUrl()
    {
        return 'https://dukt.net/';
    }

    /**
     * Define Settings
     */
    protected function defineSettings()
    {
        return [
            'allowSocialRegistration' => [AttributeType::Bool, 'default' => true],
            'allowSocialLogin'        => [AttributeType::Bool, 'default' => true],
            'defaultGroup'            => [AttributeType::Number, 'default' => null],
            'autoFillProfile'         => [AttributeType::Bool, 'default' => true],
        ];
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
        return true;
    }

    /**
     * Hook Register CP Routes
     */
    public function registerCpRoutes()
    {
        return [
            "social" => ['action' => "social/settings"],

            'social/install' => ['action' => "social/plugin/install"],

            "social/accounts" => ['action' => "social/accounts/index"],
            "social/accounts/(?P<id>\d+)" => ['action' => "social/accounts/view"],

            'social/providers' => ['action' => "social/providers/index"],
            'social/providers/(?P<handle>.*)' => ['action' => "social/providers/edit"],

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

    public function initEventListeners()
    {
        // delete social user when craft user is deleted

        craft()->on('users.onBeforeDeleteUser', function (Event $event)
        {
            $user = $event->params['user'];

            craft()->social_accounts->deleteAccountByUserId($user->id);
        });
    }
}
