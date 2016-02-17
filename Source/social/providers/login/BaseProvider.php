<?php
/**
 * @link      https://dukt.net/craft/oauth/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/oauth/docs/license
 */

namespace Dukt\Social\LoginProviders;

use Craft\Craft;

abstract class BaseProvider
{
    /**
     * Get Handle
     */
    public function getHandle()
    {
        $class = $this->getClass();

        $handle = strtolower($class);

        return $handle;
    }

    /**
     * Get provider class
     *
     * from : Dukt\Social\LoginProviders\Dribbble
     * to : Dribbble
     */
    public function getClass()
    {
        $nsClass = get_class($this);

        $class = substr($nsClass, strrpos($nsClass, "\\") + 1);

        return $class;
    }

    public function getIconUrl()
    {
        return $this->getOauthProvider()->getIconUrl();
    }

    /**
     * Get OAuth provider
     */
    public function getOauthProvider()
    {
        return Craft::app()->oauth->getProvider($this->getHandle(), false);
    }

    /**
     * Default Scope
     */
    public function getDefaultScope()
    {
    }

    /**
     * Default Authorization Options
     */
    public function getDefaultAuthorizationOptions()
    {
    }

    /**
     * Returns `scope` from login provider class by default, or `scope` overidden by the config
     */
    public function getScope()
    {
        $loginProvidersConfig = Craft::app()->config->get($this->getHandle().'LoginProvider', 'social');

        if(isset($loginProvidersConfig['scope']))
        {
            return $loginProvidersConfig['scope'];
        }
        else
        {
            return $this->getDefaultScope();
        }
    }

    /**
     * Returns `authorizationOptions` from login provider class by default, or `authorizationOptions` overidden by the config
     */
    public function getAuthorizationOptions()
    {
        $loginProvidersConfig = Craft::app()->config->get($this->getHandle().'LoginProvider', 'social');

        if(isset($loginProvidersConfig['authorizationOptions']))
        {
            return $loginProvidersConfig['authorizationOptions'];
        }
        else
        {
            return $this->getDefaultAuthorizationOptions();
        }
    }

    /**
     * Returns `enabled` from login provider class by default, or `enabled` overidden by the config
     */
    public function getIsEnabled()
    {
        // get plugin settings
        $pluginSettings = \Craft\Craft::app()->plugins->getPlugin('social')->getSettings();
        $loginProviders = $pluginSettings->loginProviders;

        if(isset($loginProviders[$this->getHandle()]['enabled']) && $loginProviders[$this->getHandle()]['enabled'])
        {
            return true;
        }

        return false;
    }

    public function getAccount($token)
    {
        return $this->getOauthProvider()->getAccount($token);
    }
}