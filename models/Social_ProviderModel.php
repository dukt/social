<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class Social_ProviderModel extends BaseModel
{
	// Public Methods
	// =========================================================================

	/**
	 * Define Attributes
	 */
	public function defineAttributes()
	{
		return array(
			'oauthProviderHandle' => AttributeType::Number,
			'customScope' => AttributeType::Mixed,
		);
	}

	/**
	 * Get authorization options for the provider.
	 *
	 * @return mixed
	 */
	public function getAuthorizationOptions()
	{
		$providerConfig = craft()->config->get($this->oauthProviderHandle, 'social');

		if ($providerConfig && isset($providerConfig['authorizationOptions'])) {
			return $providerConfig['authorizationOptions'];
		}
	}

	/**
	 * Get scope for the provider.
	 *
	 * @return array
	 */
	public function getScope()
	{
		$scope = [];
		$defaultScope = $this->getDefaultScope();

		if (is_array($defaultScope))
		{
			$scope = array_merge($scope, $defaultScope);
		}

		if (is_array($this->customScope))
		{
			$scope = array_merge($scope, $this->customScope);
		}

		return $scope;
	}

	/**
	 * Return the default scope for the provider.
	 *
	 * @return mixed
	 */
	public function getDefaultScope()
	{
		return $this->getOauthProvider()->getDefaultScope();
	}

	/**
	 * Get the provider's name.
	 *
	 * @return mixed
	 */
	public function getName()
	{
		return $this->getOauthProvider()->getName();
	}

	/**
	 * Get the provider's handle.
	 *
	 * @return mixed
	 */
	public function getHandle()
	{
		return $this->getOauthProvider()->getHandle();
	}

	/**
	 * Get the provider itself.
	 *
	 * @return mixed
	 */
	public function getOauthProvider()
	{
        Craft::app()->social->checkPluginRequirements();

		return craft()->oauth->getProvider($this->oauthProviderHandle, false);
	}

	/**
	 * Get the URL to the icon.
	 *
	 * @return mixed
	 */
	public function getIconUrl()
	{
		return $this->getOauthProvider()->getIconUrl();
	}

	/**
	 * Get the documentation URL for provider scopes.
	 *
	 * @return mixed
	 */
	public function getScopeDocsUrl()
	{
		return $this->getOauthProvider()->getScopeDocsUrl();
	}
}
