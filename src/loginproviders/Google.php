<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/social/docs/license
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
    protected function getOauthProvider()
    {
        $providerInfos = $this->getInfos();

        $config = [
            'clientId' => (isset($providerInfos['clientId']) ? $providerInfos['clientId'] : ''),
            'clientSecret' => (isset($providerInfos['clientSecret']) ? $providerInfos['clientSecret'] : ''),
            'redirectUri' => $this->getRedirectUri(),
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

    /**
     * Get API Manager URL
     *
     * @return string
     */
    public function getManagerUrl()
    {
        return 'https://code.google.com/apis/console/';
    }
    /**
     * Get Scope Docs URL
     *
     * @return string
     */
    public function getScopeDocsUrl()
    {
        return 'https://developers.google.com/identity/protocols/googlescopes';
    }
}
