<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\loginproviders;

use Craft\Craft;
use dukt\oauth\models\Token;

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
     * @param Token $token
     *
     * @return array|null
     */
	public function getProfile(Token $token)
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
