<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2018, Dukt
 * @license   https://github.com/dukt/social/blob/v2/LICENSE.md
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
     * Get the icon URL.
     *
     * @return mixed
     */
    public function getIconUrl()
    {
        return Craft::$app->assetManager->getPublishedUrl('@dukt/social/icons/'.$this->getHandle().'.svg', true);
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

        if (!empty($config['options']['clientId'])) {
            return true;
        }

        return false;
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
     * @return Response
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    public function oauthConnect(): Response
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
     * @throws \craft\errors\MissingComponentException
     */
    public function oauthCallback(): array
    {
        switch ($this->oauthVersion()) {
            case 1:
                return $this->oauth1Callback();
            case 2:
                return $this->oauth2Callback();
        }
    }

    /**
     * Returns the `scope` from login provider class by default, or the `scope` overridden by the config.
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getOauthScope()
    {
        $scope = $this->getDefaultOauthScope();
        $oauthProviderConfig = $this->getOauthProviderConfig();

        if (isset($oauthProviderConfig['scope'])) {
            $scope = $this->mergeArrayValues($scope, $oauthProviderConfig['scope']);
        }

        return $scope;
    }

    /**
     * Returns the OAuth authorization options for this provider.
     *
     * @return array|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getOauthAuthorizationOptions()
    {
        $authorizationOptions = $this->getDefaultOauthAuthorizationOptions();
        $config = $this->getOauthProviderConfig();

        if (isset($config['authorizationOptions'])) {
            $authorizationOptions = array_merge($authorizationOptions, $config['authorizationOptions']);
        }

        return $authorizationOptions;
    }

    /**
     * Returns the `enabled` setting from login provider class by default, or `enabled` overridden by the config.
     *
     * @return bool
     */
    public function getIsEnabled(): bool
    {
        // get plugin settings
        $settings = Plugin::getInstance()->getSettings();
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
     * Get profile fields.
     *
     * @return array
     */
    public function getProfileFields(): array
    {
        $profileFields = $this->getDefaultProfileFields();
        $loginProviderConfig = Plugin::getInstance()->getLoginProviderConfig($this->getHandle());

        if (isset($loginProviderConfig['profileFields'])) {
            $profileFields = $this->mergeArrayValues($profileFields, $loginProviderConfig['profileFields']);
        }

        return $profileFields;
    }

    /**
     * Get user field mapping.
     *
     * @return array
     */
    public function getUserFieldMapping(): array
    {
        $userFieldMapping = $this->getDefaultUserFieldMapping();
        $loginProviderConfig = Plugin::getInstance()->getLoginProviderConfig($this->getHandle());

        if (isset($loginProviderConfig['userFieldMapping'])) {
            $userFieldMapping = array_merge($userFieldMapping, $loginProviderConfig['userFieldMapping']);
        }

        return $userFieldMapping;
    }

    /**
     * Returns a profile from an OAuth token.
     *
     * @param Token $token
     *
     * @return array|null
     */
    public function getProfile(Token $token)
    {
        $profile = $this->getOauthProvider()->getResourceOwner($token->token);

        if (!$profile) {
            return null;
        }

        return $profile;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Get the default authorization options.
     *
     * @return array
     */
    protected function getDefaultOauthAuthorizationOptions(): array
    {
        return [];
    }

    /**
     * Get the default scope.
     *
     * @return array
     */
    protected function getDefaultOauthScope(): array
    {
        return [];
    }

    /**
     * Get default profile fields.
     *
     * @return array
     */
    protected function getDefaultProfileFields(): array
    {
        return [];
    }

    /**
     * Get OAuth provider config.
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    protected function getOauthProviderConfig(): array
    {
        return Plugin::getInstance()->getOauthProviderConfig($this->getHandle());
    }

    /**
     * Get login provider config.
     *
     * @return array
     */
    protected function getLoginProviderConfig(): array
    {
        return Plugin::getInstance()->getLoginProviderConfig($this->getHandle());
    }

    // Private Methods
    // =========================================================================

    /**
     * OAuth 1 connect.
     *
     * @return Response
     * @throws \craft\errors\MissingComponentException
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
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    private function oauth2Connect(): Response
    {
        $provider = $this->getOauthProvider();

        Craft::$app->getSession()->set('social.oauthState', $provider->getState());

        $scope = $this->getOauthScope();
        $options = $this->getOauthAuthorizationOptions();

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
     * @throws \craft\errors\MissingComponentException
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

    /**
     * Merge scope.
     *
     * @param array $array
     * @param array $array2
     *
     * @return array
     */
    private function mergeArrayValues(array $array, array $array2): array
    {
        foreach ($array2 as $value2) {
            $addValue = true;

            foreach ($array as $value) {
                if ($value === $value2) {
                    $addValue = false;
                }
            }

            if ($addValue) {
                $array[] = $value2;
            }
        }

        return $array;
    }
}
