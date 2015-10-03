<?php

/**
 * Social plugin for Craft
 *
 * @package   Craft Social
 * @author    Benjamin David
 * @copyright Copyright (c) 2015, Dukt
 * @link      https://dukt.net/craft/social/
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'social/Info.php');
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
    public function getVersion()
    {
        return SOCIAL_VERSION;
    }

    /**
     * Get Required Dependencies
     */
    function getRequiredPlugins()
    {
        return array(
            array(
                'name' => "OAuth",
                'handle' => 'oauth',
                'url' => 'https://dukt.net/craft/oauth',
                'version' => '0.9.70'
            )
        );
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

    /* ------------------------------------------------------------------------- */

    /**
     * Get Plugin Dependencies
     */
    public function getPluginDependencies($missingOnly = true)
    {
        $dependencies = array();

        $plugins = $this->getRequiredPlugins();

        foreach($plugins as $key => $plugin)
        {
            $dependency = $this->getPluginDependency($plugin);

            if($missingOnly)
            {
                if($dependency['isMissing'])
                {
                    $dependencies[] = $dependency;
                }
            }
            else
            {
                $dependencies[] = $dependency;
            }
        }

        return $dependencies;
    }

    /**
     * Get Plugin Dependency
     */
    private function getPluginDependency($dependency)
    {
        $isMissing = true;
        $isInstalled = true;

        $plugin = craft()->plugins->getPlugin($dependency['handle'], false);

        if($plugin)
        {
            $currentVersion = $plugin->version;


            // requires update ?

            if(version_compare($currentVersion, $dependency['version']) >= 0)
            {
                // no (requirements OK)

                if($plugin->isInstalled && $plugin->isEnabled)
                {
                    $isMissing = false;
                }
            }
            else
            {
                // yes (requirement not OK)
            }
        }
        else
        {
            // not installed
        }

        $dependency['isMissing'] = $isMissing;
        $dependency['plugin'] = $plugin;

        return $dependency;
    }
}
