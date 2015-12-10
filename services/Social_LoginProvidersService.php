<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class Social_LoginProvidersService extends BaseApplicationComponent
{
    // Public Methods
    // =========================================================================

    public function disableLoginProvider($handle)
    {
        $plugin = craft()->plugins->getPlugin('social');
        $settings = $plugin->getSettings();

        $loginProviders = $settings->loginProviders;
        $loginProviders[$handle]['enabled'] = false;

        $settings->loginProviders = $loginProviders;

        return craft()->plugins->savePluginSettings($plugin, $settings);
    }

    public function enableLoginProvider($handle)
    {
        $plugin = craft()->plugins->getPlugin('social');
        $settings = $plugin->getSettings();

        $loginProviders = $settings->loginProviders;
        $loginProviders[$handle]['enabled'] = true;

        $settings->loginProviders = $loginProviders;

        return craft()->plugins->savePluginSettings($plugin, $settings);
    }

    public function getLoginProvider($handle, $enabledOnly = true)
    {
        $loginProviders = $this->getLoginProviders($enabledOnly);

        foreach($loginProviders as $loginProvider)
        {
            if($loginProvider->getHandle() == $handle)
            {
                return $loginProvider;
            }
        }
    }

    public function getLoginProviders($enabledOnly = true)
    {
        return $this->_getLoginProviders($enabledOnly);
    }

    private function _getLoginProviders($enabledOnly)
    {
        // fetch all OAuth provider types

        $socialLoginProviderTypes = array();

        foreach(craft()->plugins->call('getSocialLoginProviders', [], true) as $pluginSocialLoginProviderTypes)
        {
            $socialLoginProviderTypes = array_merge($socialLoginProviderTypes, $pluginSocialLoginProviderTypes);
        }


        // instantiate providers

        $loginProviders = [];

        foreach($socialLoginProviderTypes as $socialLoginProviderType)
        {
            $loginProvider = $this->_createLoginProvider($socialLoginProviderType);

            if(!$enabledOnly || $enabledOnly && $loginProvider->getIsEnabled())
            {
                $loginProviders[$socialLoginProviderType] = $loginProvider;
            }
        }

        ksort($loginProviders);

        return $loginProviders;
    }


    /**
     * Create OAuth provider
     */
    private function _createLoginProvider($socialLoginProviderType)
    {
        $socialLoginProvider = new $socialLoginProviderType;

        return $socialLoginProvider;
    }
}
