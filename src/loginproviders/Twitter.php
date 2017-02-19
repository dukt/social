<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\loginproviders;

use Craft\Craft;
use dukt\oauth\models\Token;
use dukt\social\Plugin as Social;

class Twitter extends BaseProvider
{
    public function getOauthProviderClass()
    {
        return '\League\OAuth1\Client\Server\Twitter';
    }


    public function getOauthProviderConfig()
    {
        $providerInfos = Social::$plugin->oauth->getProviderInfos('google');

        $config = [
            'identifier' => (isset($providerInfos['clientId']) ? $providerInfos['clientId'] : ''),
            'secret' => (isset($providerInfos['clientSecret']) ? $providerInfos['clientSecret'] : ''),
            'redirectUri' => $this->getRedirectUri(),
        ];

        return $config;
    }

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
