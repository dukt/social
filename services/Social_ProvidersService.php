<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class Social_ProvidersService extends BaseApplicationComponent
{
    // Public Methods
    // =========================================================================

    public function getProvider($handle, $configuredOnly = true)
    {
        $providers = $this->getProviders($configuredOnly);

        foreach($providers as $provider)
        {
            if($provider->getHandle() == $handle)
            {
                return $provider;
            }
        }
    }

    public function getProviders($configuredOnly = true)
    {
        $providers = [];

        $oauthProviders = craft()->oauth->getProviders($configuredOnly);
        $oauthOptions = craft()->config->get('oauthOptions', 'social');

        foreach($oauthProviders as $oauthProvider)
        {
            $provider = new Social_ProviderModel;
            $provider->oauthProviderHandle = $oauthProvider->getHandle();
            $customScope = null;

            if(isset($oauthOptions[$provider->oauthProviderHandle]['scope']))
            {
                $customScope = $oauthOptions[$provider->oauthProviderHandle]['scope'];
            }

            $provider->customScope = $customScope;

            $providers[] = $provider;
        }

        return $providers;
    }
}
