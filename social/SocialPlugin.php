<?php

/**
 * Social Login for Craft
 *
 * @package   Social Login
 * @author    Benjamin David
 * @copyright Copyright (c) 2014, Dukt
 * @link      https://dukt.net/craft/social/
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'oauth/vendor/autoload.php');
require_once(CRAFT_PLUGINS_PATH.'oauth/providers/BaseOAuthProviderSource.php');

class SocialPlugin extends BasePlugin
{
    public function init()
    {
        craft()->on('users.onBeforeDeleteUser', function(Event $event) {
            $user = $event->params['user'];

            craft()->social->deleteSocialUserByUserId($user->id);
        });
        parent::init();
    }
    /**
     * Get Name
     */
    function getName()
    {
        return Craft::t('Social Login');
    }

    /**
     * Get Version
     */
    function getVersion()
    {
        return '0.9.23';
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
        return array(
            'allowSocialRegistration' => array(AttributeType::Bool, 'default' => true),
            'allowSocialLogin' => array(AttributeType::Bool, 'default' => true),
            'defaultGroup' => array(AttributeType::Number, 'default' => null),
            'requireEmailAddress' => array(AttributeType::Bool, 'default' => true),
            'askEmailTemplate' => array(AttributeType::String),
        );
    }

    /**
     * Get Settings HTML
     */
    public function getSettingsHtml()
    {
        if(craft()->request->getPath() == 'settings/plugins')
        {
            return true;
        }

        $variables = array(
            'settings' => $this->getSettings()
        );

        return craft()->templates->render('social/settings', $variables);
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
        return array(

            "social\/users\/(?P<id>\d+)" => array('action' => "social/userProfile"),
            'social\/settings\/(?P<serviceProviderClass>.*)' => 'social/settings/_provider',
        );
    }

    /**
     * On Before Uninstall
     */
    public function onBeforeUninstall()
    {
        if(isset(craft()->oauth))
        {
            craft()->oauth->deleteTokensByPlugin('social');
        }
    }
}