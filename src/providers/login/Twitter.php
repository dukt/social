<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Dukt\Social\LoginProviders;

use Craft\Craft;
use Craft\Oauth_TokenModel;

class Twitter extends BaseProvider
{
	/**
	 * @inheritdoc
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'Twitter';
	}

	/**
	 * @inheritdoc
	 *
	 * @return string
	 */
	public function getOauthProviderHandle()
	{
		return 'twitter';
	}

    /**
     * @inheritdoc
     *
     * @param Oauth_TokenModel $token
     *
     * @return array|null
     */
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
