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

    public function getLoginProvider($handle)
    {
        $loginProviders = $this->getLoginProviders();

        foreach($loginProviders as $loginProvider)
        {
            if($loginProvider->getHandle() == $handle)
            {
                return $loginProvider;
            }
        }
    }

    public function getLoginProviders()
    {
        return $this->_getLoginProviders();
    }

    private function _getLoginProviders()
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
            $loginProviders[$socialLoginProviderType] = $this->_createLoginProvider($socialLoginProviderType);
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
