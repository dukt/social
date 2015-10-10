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

class SocialService extends BaseApplicationComponent
{
    // Public Methods
    // =========================================================================

	public function getLoginUrl($gatewayHandle, $params = [])
	{
		$params['gateway'] = $gatewayHandle;

		if (isset($params['scopes']) && is_array($params['scopes']))
		{
			$params['scopes'] = urlencode(base64_encode(serialize($params['scopes'])));
		}

		$url = UrlHelper::getSiteUrl(craft()->config->get('actionTrigger').'/social/login', $params);

		Craft::log(__METHOD__." : Authenticate : ".$url, LogLevel::Info, true);

		return $url;
	}

	public function getLogoutUrl($redirect = null)
	{
		$params = ['redirect' => $redirect];

		return UrlHelper::getActionUrl('social/logout', $params);
	}

	public function getLinkAccountUrl($handle)
	{
		return UrlHelper::getActionUrl('social/link', [
			'gateway' => $handle
		]);
	}

	public function getUnlinkAccountUrl($handle)
	{
		return UrlHelper::getActionUrl('social/unlink', [
			'gateway' => $handle
		]);
	}

	/**
	 * Check Requirements
	 */
	public function checkRequirements()
	{
		$plugin = craft()->plugins->getPlugin('social');

		$pluginDependencies = $plugin->getPluginDependencies();

		if (count($pluginDependencies) > 0)
		{
			throw new \Exception("Social is not configured properly. Check Social settings for more informations.");
		}
	}
}
