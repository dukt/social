<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\loginproviders;

use dukt\oauth\models\Token;
use dukt\social\Plugin as Social;

class Google extends BaseProvider
{
	/**
	 * @inheritdoc
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'Google';
	}

    public function getOauthProviderConfig()
    {
        $providerInfos = Social::$plugin->oauth->getProviderInfos('google');

        $config = [
            'clientId' => (isset($providerInfos['clientId']) ? $providerInfos['clientId'] : ''),
            'clientSecret' => (isset($providerInfos['clientSecret']) ? $providerInfos['clientSecret'] : ''),
            'redirectUri' => $this->getRedirectUri(),
        ];

        return $config;
    }

	/**
	 * @inheritdoc
	 *
	 * @return string
	 */
	public function getOauthProviderHandle()
	{
		return 'google';
	}

	public function getOauthProviderClass()
    {
        return '\Dukt\OAuth2\Client\Provider\Google';
    }

	/**
	 * @inheritDoc
     *
     * @return array|null
	 */
	public function getDefaultScope()
	{
		return [
			'https://www.googleapis.com/auth/userinfo.profile',
			'https://www.googleapis.com/auth/userinfo.email'
		];
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
