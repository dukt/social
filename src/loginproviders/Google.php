<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2021, Dukt
 * @license   https://github.com/dukt/social/blob/v2/LICENSE.md
 */

namespace dukt\social\loginproviders;

use craft\helpers\UrlHelper;
use dukt\social\base\LoginProvider;
use dukt\social\models\Token;

/**
 * Google represents the Google login provider.
 *
 * @author  Dukt <support@dukt.net>
 * @since   1.0
 */
class Google extends LoginProvider
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'Google';
    }

    /**
     * @inheritdoc
     */
    public function getDefaultOauthScope(): array
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
        return 'https://console.developers.google.com/';
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
    public function getDefaultUserFieldMapping(): array
    {
        return [
            'id' => '{{ profile.getId() }}',
            'email' => '{{ profile.getEmail() }}',
            'username' => '{{ profile.getEmail() }}',
            'photo' => '{{ profile.getAvatar() }}',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getOauthProviderConfig(): array
    {
        $config = parent::getOauthProviderConfig();

        if (empty($config['options']['useOidcMode'])) {
            $config['options']['useOidcMode'] = true;
        }

        return $config;
    }

    /**
     * Returns the login provider’s OAuth provider.
     *
     * @return \League\OAuth2\Client\Provider\Google
     * @throws \yii\base\InvalidConfigException
     */
    public function getOauthProvider(): \League\OAuth2\Client\Provider\Google
    {
        $config = $this->getOauthProviderConfig();

        return new \League\OAuth2\Client\Provider\Google($config['options']);
    }

    /**
     * Returns the Javascript origin URL.
     *
     * @return string
     * @throws \craft\errors\SiteNotFoundException
     */
    public function getJavascriptOrigin(): string
    {
        $url = UrlHelper::baseUrl();

        return rtrim($url, '/');
    }
}
