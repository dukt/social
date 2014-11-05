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

require_once(CRAFT_PLUGINS_PATH.'social/vendor/autoload.php');

class SocialPlugin extends BasePlugin
{
    public function init()
    {
        // delete social user when craft user is deleted

        craft()->on('users.onBeforeDeleteUser', function(Event $event) {
            $user = $event->params['user'];

            craft()->social->deleteSocialUserByUserId($user->id);
        });


        // update hasEmail and hasPassword when user is saved

        craft()->on('users.onSaveUser', function(Event $event) {
            $user = $event->params['user'];

            $socialAccount = craft()->social->getAccountByUserId($user->id);

            if($socialAccount)
            {
                if(!$socialAccount->hasEmail || !$socialAccount->hasPassword)
                {
                    if($socialAccount->temporaryEmail != $user->email)
                    {
                        $socialAccount->hasEmail = true;
                    }

                    $currentHashedPassword = $user->password;
                    $currentPassword = $socialAccount->temporaryPassword;

                    if(!craft()->users->validatePassword($currentHashedPassword, $currentPassword))
                    {
                        $socialAccount->hasPassword = true;
                    }

                    craft()->social->saveAccount($socialAccount);
                }
            }
        });


        // update hasEmail when user is activated

        craft()->on('users.onActivateUser', function(Event $event) {
            $user = $event->params['user'];

            $socialAccount = craft()->social->getAccountByUserId($user->id);

            if($socialAccount)
            {
                if(!$socialAccount->hasEmail)
                {
                    if($socialAccount->temporaryEmail != $user->email || $socialAccount->temporaryEmail != $user->unverifiedEmail)
                    {
                        $socialAccount->hasEmail = true;
                    }

                    craft()->social->saveAccount($socialAccount);
                }
            }
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
        return '0.9.26';
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
            'autoFillProfile' => array(AttributeType::Bool, 'default' => true),
            'requireEmailAddress' => array(AttributeType::Bool, 'default' => true),
            'completeRegistrationTemplate' => array(AttributeType::String),
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

        return craft()->templates->render('social/_settingsRedirect');
    }

    /**
     * Has CP Section
     */
    public function hasCpSection()
    {
        return false;
    }

    /**
     * Hook Register CP Routes
     */
    public function registerCpRoutes()
    {
        return array(
            "social" => array('action' => "social/users"),
            "social\/users" => array('action' => "social/users"),
            "social\/users\/(?P<id>\d+)" => array('action' => "social/userProfile"),
            'social\/settings' => array('action' => "social/settings"),
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