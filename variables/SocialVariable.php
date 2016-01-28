<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class SocialVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Get login URL
     *
     * @param $providerHandle
     * @param array  $params
     *
     * @return string
     */
    public function getLoginUrl($providerHandle, $params = array())
    {
        return craft()->social->getLoginUrl($providerHandle, $params);
    }

    /**
     * Get logout URL
     *
     * @param string|null $redirect
     *
     * @return string
     */
    public function getLogoutUrl($redirect = null)
    {
        return craft()->social->getLogoutUrl($redirect);
    }

    /**
     * Get login account by provider handle
     *
     * @param string $loginProviderHandle
     *
     * @return Social_LoginAccountModel|null
     */
    public function getLoginAccountByLoginProvider($loginProviderHandle)
    {
        return craft()->social_loginAccounts->getLoginAccountByLoginProvider($loginProviderHandle);
    }

    /**
     * Get login account connect URL
     *
     * @param $handle
     *
     * @return string
     */
    public function getLoginAccountConnectUrl($providerHandle)
    {
        return craft()->social->getLoginAccountConnectUrl($providerHandle);
    }

    /**
     * Get login account disconnect URL
     *
     * @param $handle
     *
     * @return string
     */
    public function getLoginAccountDisconnectUrl($providerHandle)
    {
        return craft()->social->getLoginAccountDisconnectUrl($providerHandle);
    }

    /**
     * Get login providers
     *
     * @param bool|true $enabledOnly
     *
     * @return array
     */
    public function getLoginProviders($enabledOnly = true)
    {
        return craft()->social_loginProviders->getLoginProviders($enabledOnly);
    }
}
