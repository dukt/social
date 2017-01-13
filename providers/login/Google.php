<?php
/**
 * @link      https://dukt.net/craft/oauth/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/oauth/docs/license
 */

namespace Dukt\Social\LoginProviders;

use Craft\Craft;
use Craft\Oauth_TokenModel;

class Google extends BaseProvider
{
	/**
	 * Get the provider name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'Google';
	}

	/**
	 * Get the provider handle.
	 *
	 * @return string
	 */
	public function getOauthProviderHandle()
	{
		return 'google';
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultScope()
	{
		return [
			'https://www.googleapis.com/auth/userinfo.profile',
			'https://www.googleapis.com/auth/userinfo.email'
		];
	}

	public function getProfile(Oauth_TokenModel $token)
	{
		$remoteProfile = $this->getRemoteProfile($token);

		$photoUrl = $remoteProfile->getAvatar();

		if(strpos($photoUrl, '?') !== false)
		{
			$photoUrl = substr($photoUrl, 0, strpos($photoUrl, "?"));
		}

		return [
			'id' => $remoteProfile->getId(),
			'email' => $remoteProfile->getEmail(),
			'firstName' => $remoteProfile->getFirstName(),
			'lastName' => $remoteProfile->getLastName(),
			'photoUrl' => $photoUrl,

			'name' => $remoteProfile->getName(),
		];
	}
}
