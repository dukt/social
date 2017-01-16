<?php
/**
 * @link      https://dukt.net/craft/oauth/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/oauth/docs/license
 */

namespace Dukt\Social\LoginProviders;

use Craft\Craft;
use Craft\ISocial_Provider;
use Craft\Oauth_TokenModel;

abstract class BaseProvider implements ISocial_Provider
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
        Craft::app()->social->checkPluginRequirements();

		return Craft::app()->oauth->getProvider($this->getHandle(), false);
	}

	/**
	 * Get the default scope.
	 *
	 * @return mixed
	 */
	public function getDefaultScope()
	{
	}

	/**
	 * Get the defaul authorization options.
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
		$providerConfig = Craft::app()->config->get($this->getHandle(), 'social');

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
		$providerConfig = Craft::app()->config->get($this->getHandle(), 'social');

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
		$pluginSettings = \Craft\Craft::app()->plugins->getPlugin('social')->getSettings();
		$loginProviders = $pluginSettings->loginProviders;

		if (isset($loginProviders[$this->getHandle()]['enabled']) && $loginProviders[$this->getHandle()]['enabled'])
		{
			return true;
		}

		return false;
	}

	/**
	 * Returns the remote profile
	 *
	 * @param $token
	 *
	 * @return mixed
	 */
	public function getRemoteProfile(Oauth_TokenModel $token)
	{
		return $this->getOauthProvider()->getRemoteResourceOwner($token);
	}
}
