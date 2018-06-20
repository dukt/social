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
 * Twitter represents the Twitter login provider.
 *
 * @author  Dukt <support@dukt.net>
 * @since   1.0
 */
class Twitter extends LoginProvider
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'Twitter';
    }

    /**
     * @inheritdoc
     */
    public function oauthVersion(): int
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
        $profile = $this->getOauthProvider()->getUserDetails($token->token);

        if(!$profile) {
            return null;
        }

        $profile = (array) $profile;
        $profile['id'] = $profile['uid'];

        return $profile;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultUserMapping(): array
    {
        return [
            'id' => '{{ profile.uid }}',
            'email' => '{{ profile.email }}',
            'username' => '{{ profile.email }}',
            'photo' => '{{ profile.imageUrl|replace("_normal.", ".") }}',
        ];
    }

    /**
     * Returns the login provider’s OAuth provider.
     *
     * @return \League\OAuth1\Client\Server\Twitter
     * @throws \yii\base\InvalidConfigException
     */
    public function getOauthProvider(): \League\OAuth1\Client\Server\Twitter
    {
        $config = $this->getOauthProviderConfig();

        $config['identifier'] = $config['options']['clientId'] ?? '';
        unset($config['options']['clientId']);

        $config['secret'] = $config['options']['clientSecret'] ?? '';
        unset($config['options']['clientSecret']);

        $config['callback_uri'] = $config['options']['redirectUri'] ?? '';
        unset($config['options']['redirectUri']);

        return new \League\OAuth1\Client\Server\Twitter($config);
    }
}
