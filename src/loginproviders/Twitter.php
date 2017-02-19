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
    /**
     * OAuth Connect
     *
     * @return null
     */
    public function oauthConnect()
    {
        // OAuth provider
        $provider = $this->getOauthProvider();

        // Obtain temporary credentials
        $temporaryCredentials = $provider->getTemporaryCredentials();

        // Store credentials in the session
        Craft::$app->getSession()->set('oauth.temporaryCredentials', $temporaryCredentials);

        // Redirect to login screen
        $authorizationUrl = $provider->getAuthorizationUrl($temporaryCredentials);

        return Craft::$app->getResponse()->redirect($authorizationUrl);
    }

    /**
     * OAuth Callback
     *
     * @return null
     */
    public function oauthCallback()
    {
        $provider = $this->getOauthProvider();

        $oauthToken = Craft::$app->request->getParam('oauth_token');
        $oauthVerifier = Craft::$app->request->getParam('oauth_verifier');

        // Retrieve the temporary credentials we saved before.
        $temporaryCredentials = Craft::$app->getSession()->get('oauth.temporaryCredentials');

        // Obtain token credentials from the server.
        $token = $provider->getTokenCredentials($temporaryCredentials, $oauthToken, $oauthVerifier);

        return [
            'success' => true,
            'token' => $token
        ];
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
