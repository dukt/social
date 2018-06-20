<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2018, Dukt
 * @license   https://github.com/dukt/social/blob/v2/LICENSE.md
 */

namespace dukt\social\loginproviders;

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
    public function getDefaultOauthScope()
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
    public function getDefaultUserMapping(): array
    {
        return [
            'id' => '{{ profile.getId() }}',
            'email' => '{{ profile.getEmail() }}',
            'username' => '{{ profile.getEmail() }}',
            'photo' => '{{ profile.getAvatar() }}',
        ];
    }

    // Protected Methods
    // =========================================================================

    /**
     * Returns the login provider’s OAuth provider.
     *
     * @return \Dukt\OAuth2\Client\Provider\Google
     * @throws \yii\base\InvalidConfigException
     */
    protected function getOauthProvider(): \Dukt\OAuth2\Client\Provider\Google
    {
        $config = $this->getOauthProviderConfig();

        return new \Dukt\OAuth2\Client\Provider\Google($config['options']);
    }
}
