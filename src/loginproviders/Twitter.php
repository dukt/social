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

class Twitter extends LoginProvider
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
     * OAuth version
     *
     * @return int
     */
    public function oauthVersion()
    {
        return 1;
    }

    /**
     * Get the OAuth provider.
     *
     * @return mixed
     */
    protected function getOauthProvider()
    {
        $providerInfos = $this->getInfos();
        $oauthProviderOptions = $providerInfos['oauthProviderOptions'];

        $config = [
            'identifier' => (isset($oauthProviderOptions['clientId']) ? $oauthProviderOptions['clientId'] : ''),
            'secret' => (isset($oauthProviderOptions['clientSecret']) ? $oauthProviderOptions['clientSecret'] : ''),
        ];

        if(!isset($config['callback_uri']))
        {
            $config['callback_uri'] = UrlHelper::actionUrl('social/login-accounts/callback');
        }

        return new \League\OAuth1\Client\Server\Twitter($config);
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

    /**
     * Returns the remote profile.
     *
     * @param $token
     *
     * @return array|null
     */
    public function getRemoteProfile(Token $token)
    {
        return $this->getOauthProvider()->getUserDetails($token->token);
    }

    /**
     * Get API Manager URL
     *
     * @return string
     */
    public function getManagerUrl()
    {
        return 'https://dev.twitter.com/apps';
    }
}
