<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\loginproviders;

use Craft;
use craft\helpers\UrlHelper;
use dukt\social\base\LoginProvider;
use dukt\social\models\Token;
use dukt\social\Plugin as Social;

class Twitter extends LoginProvider
{
    public function oauthVersion()
    {
        return 1;
    }

    public function getOauthProviderClass()
    {
        return '\League\OAuth1\Client\Server\Twitter';
    }

    /**
     * Get the OAuth provider.
     *
     * @return mixed
     */
    public function getOauthProvider()
    {
        $providerClass = $this->getOauthProviderClass();

        $config = $this->getOauthProviderConfig();

        if(!isset($config['callback_uri']))
        {
            $config['callback_uri'] = UrlHelper::actionUrl('social/oauth/callback');
        }

        return new $providerClass($config);
    }

    public function getOauthProviderConfig()
    {
        $providerInfos = Social::$plugin->oauth->getProviderInfos('twitter');
        $oauthProviderOptions = $providerInfos['oauthProviderOptions'];

        $config = [
            'identifier' => (isset($oauthProviderOptions['consumerKey']) ? $oauthProviderOptions['consumerKey'] : ''),
            'secret' => (isset($oauthProviderOptions['consumerSecret']) ? $oauthProviderOptions['consumerSecret'] : ''),
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
}
