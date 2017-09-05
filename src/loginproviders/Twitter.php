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
 * Twitter represents the Twitter login provider.
 *
 * @author Dukt <support@dukt.net>
 * @since   1.0
 */
class Twitter extends LoginProvider
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Twitter';
    }

    /**
     * @inheritdoc
     */
    public function oauthVersion()
    {
        return 1;
    }

    /**
     * @inheritdoc
     */
    public function getManagerUrl()
    {
        return 'https://dev.twitter.com/apps';
    }

    /**
     * @inheritdoc
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

    // Protected Methods
    // =========================================================================

    /**
     * Returns the login provider’s OAuth provider.
     *
     * @return \League\OAuth1\Client\Server\Twitter
     */
    protected function getOauthProvider()
    {
        $providerInfos = $this->getInfos();

        $config = [
            'identifier' => (isset($providerInfos['clientId']) ? $providerInfos['clientId'] : ''),
            'secret' => (isset($providerInfos['clientSecret']) ? $providerInfos['clientSecret'] : ''),
        ];

        if (!isset($config['callback_uri'])) {
            $config['callback_uri'] = $this->getRedirectUri();
        }

        return new \League\OAuth1\Client\Server\Twitter($config);
    }

    /**
     * @inheritdoc
     */
    protected function getRemoteProfile(Token $token)
    {
        return $this->getOauthProvider()->getUserDetails($token->token);
    }
}
