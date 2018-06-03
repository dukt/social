<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2018, Dukt
 * @license   https://dukt.net/social/docs/license
 */

namespace dukt\social\base;

use Craft;
use craft\web\Response;
use dukt\social\helpers\SocialHelper;
use dukt\social\models\Token;
use dukt\social\Plugin;

/**
 * LoginProvider is the base class for classes representing login providers in terms of objects.
 *
 * @author  Dukt <support@dukt.net>
 * @since   1.0
 */
abstract class LoginProvider implements LoginProviderInterface
{
    // Public Methods
    // =========================================================================

    /**
     * Use the login provider’s name as the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Get API Manager URL.
     *
     * @return string|null
     */
    public function getManagerUrl()
    {
        return null;
    }

    /**
     * Get Scope Docs URL.
     *
     * @return string|null
     */
    public function getScopeDocsUrl()
    {
        return null;
    }

    /**
     * Checks if the login provider is configured.
     *
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function isConfigured(): bool
    {
        $config = $this->getOauthProviderConfig();

        if (!empty($config['clientId'])) {
            return true;
        }

        return false;
    }

    /**
     * OAuth version.
     *
     * @return int
     */
    public function oauthVersion(): int
    {
        return 2;
    }

    /**
     * OAuth connect.
     *
     * @return null
     */
    public function oauthConnect()
    {
        switch ($this->oauthVersion()) {
            case 1:
                return $this->oauth1Connect();
            case 2:
                return $this->oauth2Connect();
        }
    }

    /**
     * OAuth callback.
     *
     * @return array
     */
    public function oauthCallback()
    {
        switch ($this->oauthVersion()) {
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
    public function getHandle(): string
    {
        $class = $this->getClass();

        return strtolower($class);
    }

    /**
     * Get the class name, stripping all the namespaces.
     *
     * For example, "dukt\social\loginproviders\Google" becomes "Google"
     *
     * @return string
     */
    public function getClass(): string
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
        return null;
    }

    /**
     * Get the default authorization options.
     *
     * @return array|null
     */
    public function getDefaultAuthorizationOptions()
    {
        return null;
    }

    /**
     * Returns the `scope` from login provider class by default, or the `scope` overridden by the config.
     *
     * @return array|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getScope()
    {
        $providerHandle = $this->getHandle();
        $config = Plugin::$plugin->getOauthProviderConfig($providerHandle);

        if (isset($config['scope'])) {
            return $config['scope'];
        }

        return $this->getDefaultScope();
    }

    /**
     * Returns the `authorizationOptions` from login provider class by default, or `authorizationOptions` overridden by the config.
     *
     * @return array|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getAuthorizationOptions()
    {
        $providerHandle = $this->getHandle();
        $config = Plugin::$plugin->getOauthProviderConfig($providerHandle);

        if (isset($config['authorizationOptions'])) {
            return $config['authorizationOptions'];
        }

        return $this->getDefaultAuthorizationOptions();
    }

    /**
     * Returns the `enabled` setting from login provider class by default, or `enabled` overridden by the config.
     *
     * @return bool
     */
    public function getIsEnabled(): bool
    {
        // get plugin settings
        $settings = Plugin::$plugin->getSettings();
        $enabledLoginProviders = $settings->enabledLoginProviders;

        if (in_array($this->getHandle(), $enabledLoginProviders)) {
            return true;
        }

        return false;
    }

    /**
     * Returns the URI users are redirected to after they have connected.
     *
     * @return string
     */
    public function getRedirectUri(): string
    {
        return SocialHelper::siteActionUrl('social/login-accounts/callback');
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getOauthProviderConfig()
    {
        return Plugin::getInstance()->getOauthProviderConfig($this->getHandle());
    }

    // Protected Methods
    // =========================================================================

    /**
     * Returns the remote profile.
     *
     * @param $token
     *
     * @return array|null
     */
    protected function getRemoteProfile(Token $token)
    {
        return $this->getOauthProvider()->getResourceOwner($token->token);
    }

    // Private Methods
    // =========================================================================

    /**
     * OAuth 1 connect.
     *
     * @return Response
     */
    private function oauth1Connect(): Response
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
     * OAuth 2 connect.
     *
     * @return Response
     */
    private function oauth2Connect(): Response
    {
        $provider = $this->getOauthProvider();

        Craft::$app->getSession()->set('social.oauthState', $provider->getState());

        $scope = $this->getScope();
        $options = $this->getAuthorizationOptions();

        if (!is_array($options)) {
            $options = [];
        }

        $options['scope'] = $scope;

        $authorizationUrl = $provider->getAuthorizationUrl($options);

        return Craft::$app->getResponse()->redirect($authorizationUrl);
    }

    /**
     * OAuth 1 callback.
     *
     * @return array
     */
    private function oauth1Callback(): array
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
     * OAuth 2 callback.
     *
     * @return array
     */
    private function oauth2Callback(): array
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
