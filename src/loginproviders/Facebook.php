<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/social/blob/v2/LICENSE.md
 */

namespace dukt\social\loginproviders;

use dukt\social\base\LoginProvider;
use dukt\social\helpers\SocialHelper;
use GuzzleHttp\Client;
use dukt\social\models\Token;
use League\OAuth2\Client\Provider\FacebookUser;

/**
 * Facebook represents the Facebook login provider.
 *
 * @author  Dukt <support@dukt.net>
 * @since   1.0
 */
class Facebook extends LoginProvider
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'Facebook';
    }

    /**
     * @inheritdoc
     */
    public function getManagerUrl()
    {
        return 'https://developers.facebook.com/apps';
    }

    /**
     * @inheritdoc
     */
    public function getScopeDocsUrl()
    {
        return 'https://developers.facebook.com/docs/facebook-login/permissions';
    }

    /**
     * @inheritdoc
     */
    public function getProfile(Token $token)
    {
        $client = $this->getClient($token);

        $fields = implode(',', $this->getProfileFields());

        $options = [
            'query' => [
                'fields' => $fields
            ]
        ];

        $response = $client->request('GET', '/me', $options);

        if (!$response) {
            return null;
        }

        $data = json_decode($response->getBody(), true);

        if (!$data) {
            return null;
        }

        return new FacebookUser($data);
    }

    /**
     * Get the redirect URI.
     *
     * @return string
     */
    public function getRedirectUri(): string
    {
        $url = SocialHelper::siteActionUrl('social/login-accounts/callback');
        $parsedUrl = parse_url($url);

        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $query);

            $query = http_build_query($query);

            return $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'] . '?' . $query;
        }

        return $url;
    }

    /**
     * @inheritdoc
     */
    protected function getOauthProviderConfig(): array
    {
        $config = parent::getOauthProviderConfig();

        if (empty($config['options']['graphApiVersion'])) {
            $config['options']['graphApiVersion'] = 'v3.0';
        }

        return $config;
    }

    /**
     * @inheritdoc
     *
     * @return \League\OAuth2\Client\Provider\Facebook
     * @throws \yii\base\InvalidConfigException
     */
    public function getOauthProvider(): \League\OAuth2\Client\Provider\Facebook
    {
        $config = $this->getOauthProviderConfig();

        return new \League\OAuth2\Client\Provider\Facebook($config['options']);
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
            'photo' => '{{ profile.getPictureUrl() }}',
        ];
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function getDefaultOauthScope(): array
    {
        return [
            'email',
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultProfileFields(): array
    {
        return [
            'id',
            'name',
            'first_name',
            'last_name',
            'email',
            'picture.type(large){url,is_silhouette}',
            'cover{source}',
            'gender',
            'locale',
            'link',
        ];
    }

    // Private Methods
    // =========================================================================

    /**
     * Returns the authenticated Guzzle client.
     *
     * @param Token $token
     *
     * @return Client
     */
    private function getClient(Token $token): Client
    {
        $headers = [];

        if ($token) {

            $headers['Authorization'] = 'Bearer ' . $token->token;
        }

        $options = [
            'base_uri' => 'https://graph.facebook.com/',
            'headers' => $headers
        ];

        return new Client($options);
    }
}
