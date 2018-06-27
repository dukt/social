<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2018, Dukt
 * @license   https://github.com/dukt/social/blob/v2/LICENSE.md
 */

namespace dukt\social\web\twig\variables;

use Craft;
use dukt\social\elements\db\LoginAccountQuery;
use dukt\social\elements\LoginAccount;
use dukt\social\Plugin as Social;

/**
 * Class SocialVariable variable.
 *
 * @author  Dukt <support@dukt.net>
 * @since   1.0
 */
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
     * @param       $providerHandle
     * @param array $params
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getLoginUrl($providerHandle, array $params = [])
    {
        return Social::getInstance()->getLoginAccounts()->getLoginUrl($providerHandle, $params);
    }

    /**
     * Get the login account by provider handle
     *
     * @param string $loginProviderHandle
     *
     * @return LoginAccount|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getLoginAccountByLoginProvider($loginProviderHandle)
    {
        return Social::getInstance()->getLoginAccounts()->getLoginAccountByLoginProvider($loginProviderHandle);
    }

    /**
     * Get the login account connect URL
     *
     * @param string $providerHandle
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getLoginAccountConnectUrl($providerHandle)
    {
        return Social::getInstance()->getLoginAccounts()->getLoginAccountConnectUrl($providerHandle);
    }

    /**
     * Get the login account disconnect URL
     *
     * @param string $providerHandle
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getLoginAccountDisconnectUrl($providerHandle)
    {
        return Social::getInstance()->getLoginAccounts()->getLoginAccountDisconnectUrl($providerHandle);
    }

    /**
     * Get a list of login providers
     *
     * @param bool|true $enabledOnly
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getLoginProviders($enabledOnly = true)
    {
        return Social::getInstance()->getLoginProviders()->getLoginProviders($enabledOnly);
    }
}
