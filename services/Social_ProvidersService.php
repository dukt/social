<?php

/**
 * Social plugin for Craft
 *
 * @package   Craft Social
 * @author    Benjamin David
 * @copyright Copyright (c) 2015, Dukt
 * @link      https://dukt.net/craft/social/
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class Social_ProvidersService extends BaseApplicationComponent
{
    // Public Methods
    // =========================================================================

	public function getProviderScopes($handle)
	{
		$scopes = craft()->config->get($handle.'Scopes', 'social');

		if ($scopes)
		{
			return $scopes;
		}
		else
		{
			return [];
		}
	}

	public function getProviderParams($handle)
	{
		$socialProvider = $this->getProvider($handle, false);

		if ($socialProvider)
		{
			return $socialProvider->getParams();
		}
		else
		{
			return [];
		}
	}

	public function getProviders($configuredOnly = true)
	{
		craft()->social->checkRequirements();

		$allProviders = craft()->oauth->getProviders($configuredOnly);

		$providers = [];

		foreach ($allProviders as $provider)
		{
			$socialProvider = $this->getProvider($provider->getHandle(), $configuredOnly);

			if ($socialProvider)
			{
				array_push($providers, $socialProvider);
			}
		}

		return $providers;
	}

	public function getProvider($handle, $configuredOnly = true)
	{
		craft()->social->checkRequirements();

		$className = '\\Dukt\\Social\\Provider\\'.ucfirst($handle);

		if (class_exists($className))
		{
			$socialProvider = new $className;

			$oauthProvider = craft()->oauth->getProvider($handle, $configuredOnly);

			if ($oauthProvider)
			{
				return $socialProvider;
			}
		}
	}
}
