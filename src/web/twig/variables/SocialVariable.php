<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/social/docs/license
 */

namespace dukt\social\web\twig\variables;

use dukt\social\Plugin as Social;

class SocialVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Returns an ElementCriteriaModell for Social_LoginAccount elements.
     *
     * @param array|null $criteria
     *
     * @return ElementCriteriaModel
     */
    public function loginAccounts($criteria = null)
    {
        return craft()->elements->getCriteria('Social_LoginAccount', $criteria);
    }

    /**
     * Get the login URL
     *
     * @param $providerHandle
     * @param array  $params
     *
     * @return string
     */
    public function getLoginUrl($providerHandle, $params = array())
    {
        return Social::$plugin->getLoginAccounts()->getLoginUrl($providerHandle, $params);
    }

    /**
     * Get the logout URL
     *
     * @param string|null $redirect
     *
     * @return string
     */
    public function getLogoutUrl($redirect = null)
    {
        return Social::$plugin->getLoginAccounts()->getLogoutUrl($redirect);
    }

    /**
     * Get the login account by provider handle
     *
     * @param string $loginProviderHandle
     *
     * @return Social_LoginAccountModel|null
     */
    public function getLoginAccountByLoginProvider($loginProviderHandle)
    {
        return Social::$plugin->getLoginAccounts()->getLoginAccountByLoginProvider($loginProviderHandle);
    }

    /**
     * Get the login account connect URL
     *
     * @param string $providerHandle
     *
     * @return string
     */
    public function getLoginAccountConnectUrl($providerHandle)
    {
        return Social::$plugin->getLoginAccounts()->getLoginAccountConnectUrl($providerHandle);
    }

    /**
     * Get the login account disconnect URL
     *
     * @param string $providerHandle
     *
     * @return string
     */
    public function getLoginAccountDisconnectUrl($providerHandle)
    {
        return Social::$plugin->getLoginAccounts()->getLoginAccountDisconnectUrl($providerHandle);
    }

    /**
     * Get a list of login providers
     *
     * @param bool|true $enabledOnly
     *
     * @return array
     */
    public function getLoginProviders($enabledOnly = true)
    {
        return Social::$plugin->getLoginProviders()->getLoginProviders($enabledOnly);
    }
}
