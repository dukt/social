<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\base;

use Craft;
use craft\web\Response;
use dukt\social\models\Token;

abstract class LoginProvider implements LoginProviderInterface
{
    // Public Methods
    // =========================================================================

    /**
     * OAuth version
     *
     * @return int
     */
    public function oauthVersion()
    {
        return 2;
    }

    /**
     * OAuth Connect
     *
     * @return null
     */
    public function oauthConnect()
    {
        switch($this->oauthVersion())
        {
            case 1:
                return $this->oauth1Connect();
            case 2:
                return $this->oauth2Connect();
        }
    }

    /**
     * OAuth Callback
     *
     * @return array
     */
    public function oauthCallback()
    {
        switch($this->oauthVersion())
        {
            case 1:
                return $this->oauth1Callback();
            case 2:
                return $this->oauth2Callback();
        }

    }

    /**
     * Get the provider handle.
     *
     * @return string
     */
    public function getHandle()
    {
        $class = $this->getClass();

        return strtolower($class);
    }

    /**
     * Get the class name, stripping all the namespaces.
     *
     * For example, "Dukt\Social\LoginProviders\Dribbble" becomes "Dribbble"
     *
     * @return string
     */
    public function getClass()
    {
        $nsClass = get_class($this);

        return substr($nsClass, strrpos($nsClass, "\\") + 1);
    }

    /**
     * Get the icon URL.
     *
     * @return mixed
     */
    public function getIconUrl()
    {
        return Craft::$app->assetManager->getPublishedUrl('@dukt/social/icons/'.$this->getHandle().'.svg', true);
    }

    /**
     * Get the default scope.
     *
     * @return array|null
     */
    public function getDefaultScope()
    {
    }

    /**
     * Get the default authorization options.
     *
     * @return mixed
     */
    public function getDefaultAuthorizationOptions()
    {
    }

    /**
     * Returns the `scope` from login provider class by default, or the `scope` overridden by the config
     *
     * @return mixed
     */
    public function getScope()
    {
        $providerConfig = Craft::$app->getConfig()->get($this->getHandle(), 'social');

        if ($providerConfig && isset($providerConfig['scope']))
        {
            return $providerConfig['scope'];
        }
        else
        {
            return $this->getDefaultScope();
        }
    }

    /**
     * Returns the `authorizationOptions` from login provider class by default, or `authorizationOptions` overridden by the config
     *
     * @return mixed
     */
    public function getAuthorizationOptions()
    {
        $providerConfig = Craft::$app->getConfig()->get($this->getHandle(), 'social');

        if ($providerConfig && isset($providerConfig['authorizationOptions']))
        {
            return $providerConfig['authorizationOptions'];
        }
        else
        {
            return $this->getDefaultAuthorizationOptions();
        }
    }

    /**
     * Returns the `enabled` setting from login provider class by default, or `enabled` overridden by the config.
     *
     * @return bool
     */
    public function getIsEnabled()
    {
        // get plugin settings
        $plugin = Craft::$app->getPlugins()->getPlugin('social');
        $settings = $plugin->getSettings();
        $loginProviders = $settings->loginProviders;

        if (isset($loginProviders[$this->getHandle()]['enabled']) && $loginProviders[$this->getHandle()]['enabled'])
        {
            return true;
        }

        return false;
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
        return $this->getOauthProvider()->getResourceOwner($token->token);
    }

    // Private Methods
    // =========================================================================

    /**
     * OAuth 1 Connect
     *
     * @return Response
     */
    private function oauth1Connect()
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
     * OAuth 2 Connect
     *
     * @return Response
     */
    private function oauth2Connect()
    {
        $provider = $this->getOauthProvider();

        Craft::$app->getSession()->set('social.oauthState', $provider->getState());

        $scope = $this->getScope();
        $options = $this->getAuthorizationOptions();

        if(!is_array($options))
        {
            $options = [];
        }

        $options['scope'] = $scope;

        $authorizationUrl = $provider->getAuthorizationUrl($options);

        return Craft::$app->getResponse()->redirect($authorizationUrl);
    }

    /**
     * OAuth 1 Callback
     *
     * @return array
     */
    private function oauth1Callback()
    {
        $provider = $this->getOauthProvider();

        $oauthToken = Craft::$app->getRequest()->getParam('oauth_token');
        $oauthVerifier = Craft::$app->getRequest()->getParam('oauth_verifier');

        // Retrieve the temporary credentials we saved before.
        $temporaryCredentials = Craft::$app->getSession()->get('oauth.temporaryCredentials');

        // Obtain token credentials from the server.
        $token = $provider->getTokenCredentials($temporaryCredentials, $oauthToken, $oauthVerifier);

        return [
            'success' => true,
            'token' => $token
        ];
    }

    /**
     * OAuth 2 Callback
     *
     * @return array
     */
    private function oauth2Callback()
    {
        $provider = $this->getOauthProvider();

        $code = Craft::$app->getRequest()->getParam('code');

        // Try to get an access token (using the authorization code grant)
        $token = $provider->getAccessToken('authorization_code', [
            'code' => $code
        ]);

        return [
            'success' => true,
            'token' => $token
        ];
    }
}
