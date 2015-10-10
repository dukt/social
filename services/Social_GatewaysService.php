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

class Social_GatewaysService extends BaseApplicationComponent
{
    // Public Methods
    // =========================================================================

	public function getGateway($handle, $configuredOnly = true)
	{
		craft()->social->checkRequirements();

		$className = '\\Dukt\\Social\\Gateway\\'.ucfirst($handle);

		if (class_exists($className))
		{
			$gateway = new $className;

			$oauthProvider = craft()->oauth->getProvider($handle, $configuredOnly);

			if ($oauthProvider)
			{
				return $gateway;
			}
		}
	}

	public function getGateways($configuredOnly = true)
	{
		craft()->social->checkRequirements();

		$oauthProviders = craft()->oauth->getProviders($configuredOnly);

		$gateways = [];

		foreach ($oauthProviders as $oauthProvider)
		{
			$gateway = $this->getGateway($oauthProvider->getHandle(), $configuredOnly);

			if ($gateway)
			{
				array_push($gateways, $gateway);
			}
		}

		return $gateways;
	}

	public function getGatewayScopes($handle)
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

	public function getGatewayParams($handle)
	{
		$gateway = $this->getGateway($handle, false);

		if ($gateway)
		{
			return $gateway->getParams();
		}
		else
		{
			return [];
		}
	}
}
