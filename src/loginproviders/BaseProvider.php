<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\loginproviders;

use Craft;
use dukt\social\base\LoginProviderInterface;
use dukt\oauth\models\Token;

abstract class BaseProvider implements LoginProviderInterface
{
	/**
	 * Get the provider handle.
	 *
	 * @return string
	 */
	public function getHandle()
	{
		$class = $this->getClass();

		$handle = strtolower($class);

		return $handle;
	}

	/**
	 * Get the class name, stripping all the namespaces.
	 *
	 * For example, "Dukt\Social\LoginProviders\Dribbble" becomes "Dribbble"
	 *
	 * @return string
	 */
	public function getClass()
	{
		$nsClass = get_class($this);

		$class = substr($nsClass, strrpos($nsClass, "\\") + 1);

		return $class;
	}

	/**
	 * Get the icon URL.
	 *
	 * @return mixed
	 */
	public function getIconUrl()
	{
		return $this->getOauthProvider()->getIconUrl();
	}

	/**
	 * Get the OAuth provider.
	 *
	 * @return mixed
	 */
	public function getOauthProvider()
	{
        \dukt\social\Plugin::getInstance()->social->checkPluginRequirements();

		return \dukt\oauth\Plugin::getInstance()->oauth->getProvider($this->getHandle(), false);
	}

	/**
	 * Get the default scope.
	 *
	 * @return array|null
	 */
	public function getDefaultScope()
	{
	}

	/**
	 * Get the default authorization options.
	 *
	 * @return mixed
	 */
	public function getDefaultAuthorizationOptions()
	{
	}

	/**
	 * Returns the `scope` from login provider class by default, or the `scope` overridden by the config
	 *
	 * @return mixed
	 */
	public function getScope()
	{
		$providerConfig = Craft::$app->config->get($this->getHandle(), 'social');

		if ($providerConfig && isset($providerConfig['scope']))
		{
			return $providerConfig['scope'];
		}
		else
		{
			return $this->getDefaultScope();
		}
	}

	/**
	 * Returns the `authorizationOptions` from login provider class by default, or `authorizationOptions` overridden by the config
	 *
	 * @return mixed
	 */
	public function getAuthorizationOptions()
	{
		$providerConfig = Craft::$app->config->get($this->getHandle(), 'social');

		if ($providerConfig && isset($providerConfig['authorizationOptions']))
		{
			return $providerConfig['authorizationOptions'];
		}
		else
		{
			return $this->getDefaultAuthorizationOptions();
		}
	}

	/**
	 * Returns the `enabled` setting from login provider class by default, or `enabled` overridden by the config.
	 *
	 * @return bool
	 */
	public function getIsEnabled()
	{
		// get plugin settings
        $plugin = Craft::$app->plugins->getPlugin('social');
		$settings = $plugin->getSettings();
		$loginProviders = $settings->loginProviders;

		if (isset($loginProviders[$this->getHandle()]['enabled']) && $loginProviders[$this->getHandle()]['enabled'])
		{
			return true;
		}

		return false;
	}

	/**
	 * Returns the remote profile.
	 *
	 * @param $token
	 *
	 * @return array|null
	 */
	public function getRemoteProfile(Token $token)
	{
		return $this->getOauthProvider()->getRemoteResourceOwner($token);
	}
}
