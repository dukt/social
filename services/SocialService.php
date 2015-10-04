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

	public function getConnectUrl($handle)
	{
		return UrlHelper::getActionUrl('social/connect', [
			'provider' => $handle
		]);
	}

	public function getDisconnectUrl($handle)
	{
		return UrlHelper::getActionUrl('social/disconnect', [
			'provider' => $handle
		]);
	}

	public function getLoginUrl($providerClass, $params = [])
	{
		$params['provider'] = $providerClass;

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
}
