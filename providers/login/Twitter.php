<?php
/**
 * @link      https://dukt.net/craft/oauth/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/oauth/docs/license
 */

namespace Dukt\Social\LoginProviders;

use Craft\Craft;
use Craft\Oauth_TokenModel;

class Twitter extends BaseProvider
{
	/**
	 * Get the provider name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'Twitter';
	}

	/**
	 * Get the provider handle.
	 *
	 * @return string
	 */
	public function getOauthProviderHandle()
	{
		return 'twitter';
	}

	public function getProfile(Oauth_TokenModel $token)
	{
		$remoteProfile = $this->getRemoteProfile($token);

		$photoUrl = $remoteProfile->imageUrl;
		$photoUrl = str_replace("_normal.", ".", $photoUrl);

		return [
			'id' => $remoteProfile->uid,
			'email' => $remoteProfile->email,
			'photoUrl' => $photoUrl,

			'nickname' => $remoteProfile->nickname,
			'name' => $remoteProfile->name,
			'location' => $remoteProfile->location,
			'description' => $remoteProfile->description,
		];
	}
}
