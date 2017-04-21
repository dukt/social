<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/social/docs/license
 */

namespace dukt\social\web\twig\variables;

use Craft;
use dukt\social\elements\db\LoginAccountQuery;
use dukt\social\elements\LoginAccount;
use dukt\social\Plugin as Social;

class SocialVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Returns a new EntryQuery instance.
     *
     * @param mixed $criteria
     *
     * @return LoginAccountQuery
     */
    public function loginAccounts($criteria = null): LoginAccountQuery
    {
        $query = LoginAccount::find();
        if ($criteria) {
            Craft::configure($query, $criteria);
        }

        return $query;
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
