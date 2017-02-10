<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\variables;

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
		return \dukt\social\Plugin::getInstance()->social->getLoginUrl($providerHandle, $params);
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
		return \dukt\social\Plugin::getInstance()->social->getLogoutUrl($redirect);
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
		return \dukt\social\Plugin::getInstance()->social_loginAccounts->getLoginAccountByLoginProvider($loginProviderHandle);
	}

	/**
	 * Get the login account connect URL
	 *
	 * @param $handle
	 *
	 * @return string
	 */
	public function getLoginAccountConnectUrl($providerHandle)
	{
		return \dukt\social\Plugin::getInstance()->social->getLoginAccountConnectUrl($providerHandle);
	}

	/**
	 * Get the login account disconnect URL
	 *
	 * @param $handle
	 *
	 * @return string
	 */
	public function getLoginAccountDisconnectUrl($providerHandle)
	{
		return \dukt\social\Plugin::getInstance()->social->getLoginAccountDisconnectUrl($providerHandle);
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
		return \dukt\social\Plugin::getInstance()->social_loginProviders->getLoginProviders($enabledOnly);
	}
}
