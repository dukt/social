<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\loginproviders;

use craft\helpers\UrlHelper;
use dukt\social\base\LoginProvider;
use dukt\social\models\Token;
use dukt\social\Plugin as Social;

class Google extends LoginProvider
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

    /**
     * Get the OAuth provider.
     *
     * @return mixed
     */
    public function getOauthProvider()
    {
        $providerInfos = Social::$plugin->getOauth()->getProviderInfos($this->getHandle());
        $oauthProviderOptions = $providerInfos['oauthProviderOptions'];

        $config = [
            'clientId' => (isset($oauthProviderOptions['clientId']) ? $oauthProviderOptions['clientId'] : ''),
            'clientSecret' => (isset($oauthProviderOptions['clientSecret']) ? $oauthProviderOptions['clientSecret'] : ''),
            'redirectUri' => UrlHelper::actionUrl('social/oauth/callback'),
        ];

        return new \Dukt\OAuth2\Client\Provider\Google($config);
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
