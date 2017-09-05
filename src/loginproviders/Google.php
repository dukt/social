<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/social/docs/license
 */

namespace dukt\social\loginproviders;

use dukt\social\base\LoginProvider;
use dukt\social\models\Token;

/**
 * Google represents the Google login provider.
 *
 * @author Dukt <support@dukt.net>
 * @since   1.0
 */
class Google extends LoginProvider
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Google';
    }

    /**
     * @inheritdoc
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
     */
    public function getManagerUrl()
    {
        return 'https://code.google.com/apis/console/';
    }

    /**
     * @inheritdoc
     */
    public function getScopeDocsUrl()
    {
        return 'https://developers.google.com/identity/protocols/googlescopes';
    }

    /**
     * @inheritdoc
     */
    public function getProfile(Token $token)
    {
        $remoteProfile = $this->getRemoteProfile($token);

        $photoUrl = $remoteProfile->getAvatar();

        if (strpos($photoUrl, '?') !== false) {
            $photoUrl = substr($photoUrl, 0, strpos($photoUrl, "?"));
        }

        return [
            'id' => $remoteProfile->getId(),
            'email' => $remoteProfile->getEmail(),
            'name' => $remoteProfile->getName(),
            'firstName' => $remoteProfile->getFirstName(),
            'lastName' => $remoteProfile->getLastName(),
            'photoUrl' => $photoUrl,
        ];
    }

    // Protected Methods
    // =========================================================================

    /**
     * Returns the login provider’s OAuth provider.
     *
     * @return \Dukt\OAuth2\Client\Provider\Google
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
}
