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

require_once(CRAFT_PLUGINS_PATH.'social/vendor/autoload.php');

class SocialPlugin extends BasePlugin
{
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
        return '0.10.1';
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
        return false;
    }

    /**
     * Hook Register CP Routes
     */
    public function registerCpRoutes()
    {
        return [
            "social"                   => ['action' => "social/settings"],
            'social/install'         => ['action' => "social/install"],
            'social/gateways'         => ['action' => "social/gateways/index"],
            'social/settings'          => ['action' => "social/settings/index"],
            "social/accounts"             => ['action' => "social/accounts/index"],
            "social/accounts/(?P<id>\d+)" => ['action' => "social/accounts/view"],
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
                'version' => '0.9.70'
            ]
        ];
    }

    public function init()
    {
        // delete social user when craft user is deleted

        craft()->on('users.onBeforeDeleteUser', function (Event $event)
        {
            $user = $event->params['user'];

            craft()->social_accounts->deleteAccountByUserId($user->id);
        });


        // update hasEmail and hasPassword when user is saved

        craft()->on('users.onSaveUser', function (Event $event)
        {
            $user = $event->params['user'];

            $socialAccount = craft()->social_users->getSocialUserByUserId($user->id);

            if ($socialAccount)
            {
                if (!$socialAccount->hasEmail || !$socialAccount->hasPassword)
                {
                    if ($socialAccount->temporaryEmail != $user->email)
                    {
                        $socialAccount->hasEmail = true;
                    }

                    $currentHashedPassword = $user->password;
                    $currentPassword = $socialAccount->temporaryPassword;

                    if (!craft()->users->validatePassword($currentHashedPassword, $currentPassword))
                    {
                        $socialAccount->hasPassword = true;
                    }

                    craft()->social_users->saveSocialUser($socialAccount);
                }
            }
        });


        // update hasEmail when user is activated

        craft()->on('users.onActivateUser', function (Event $event)
        {
            $user = $event->params['user'];

            $socialAccount = craft()->social_users->getSocialUserByUserId($user->id);

            if ($socialAccount)
            {
                if (!$socialAccount->hasEmail)
                {
                    if ($socialAccount->temporaryEmail != $user->email || $socialAccount->temporaryEmail != $user->unverifiedEmail)
                    {
                        $socialAccount->hasEmail = true;
                    }

                    craft()->social_users->saveSocialUser($socialAccount);
                }
            }
        });

        parent::init();
    }

    /* ------------------------------------------------------------------------- */

    /**
     * Get Plugin Dependencies
     */
    public function getPluginDependencies($missingOnly = true)
    {
        $dependencies = [];

        $plugins = $this->getRequiredPlugins();

        foreach ($plugins as $key => $plugin)
        {
            $dependency = $this->getPluginDependency($plugin);

            if ($missingOnly)
            {
                if ($dependency['isMissing'])
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

        if ($plugin)
        {
            $currentVersion = $plugin->version;


            // requires update ?

            if (version_compare($currentVersion, $dependency['version']) >= 0)
            {
                // no (requirements OK)

                if ($plugin->isInstalled && $plugin->isEnabled)
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
