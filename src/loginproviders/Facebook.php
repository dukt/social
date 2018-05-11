<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2018, Dukt
 * @license   https://dukt.net/social/docs/license
 */

namespace dukt\social\loginproviders;

use dukt\social\base\LoginProvider;
use dukt\social\helpers\SocialHelper;
use GuzzleHttp\Client;
use dukt\social\models\Token;

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
    public function getDefaultScope()
    {
        return [
            'email',
            'user_location',
        ];
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
        $remoteProfile = $this->getRemoteProfile($token);

        return [
            'id' => $remoteProfile['id'] ?? null,
            'email' => $remoteProfile['email'] ?? null,
            'firstName' => $remoteProfile['first_name'] ?? null,
            'lastName' => $remoteProfile['last_name'] ?? null,
            'photoUrl' => $remoteProfile['picture']['data']['url'] ?? null,
            'name' => $remoteProfile['name'] ?? null,
            'hometown' => $remoteProfile['hometown'] ?? null,
            'isDefaultPicture' => $remoteProfile['picture']['data']['is_silhouette'] ?? null,
            'coverPhotoUrl' => $remoteProfile['cover']['source'] ?? null,
            'gender' => $remoteProfile['gender'] ?? null,
            'locale' => $remoteProfile['locale'] ?? null,
            'link' => $remoteProfile['link'] ?? null,
            'locationId' => $remoteProfile['location']['id'] ?? null,
            'locationName' => $remoteProfile['location']['name'] ?? null,
        ];
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

            return $parsedUrl['scheme'].'://'.$parsedUrl['host'].$parsedUrl['path'].'?'.$query;
        }

        return $url;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     *
     * @return \League\OAuth2\Client\Provider\Facebook
     */
    protected function getOauthProvider(): \League\OAuth2\Client\Provider\Facebook
    {
        $providerInfos = $this->getInfos();

        $config = [
            'clientId' => $providerInfos['clientId'],
            'clientSecret' => $providerInfos['clientSecret'],
            'graphApiVersion' => $providerInfos['graphApiVersion'] ?? 'v2.12',
            'redirectUri' => $this->getRedirectUri(),
        ];

        return new \League\OAuth2\Client\Provider\Facebook($config);
    }

    /**
     * @inheritdoc
     */
    protected function getRemoteProfile(Token $token)
    {
        $client = $this->getClient($token);

        $fields = implode(',', [
            'id', 'name', 'first_name', 'last_name',
            'email', 'hometown', 'picture.type(large){url,is_silhouette}',
            'cover{source}', 'gender', 'locale', 'link',
            'location',
        ]);

        $options = [
            'query' => [
                'fields' => $fields
            ]
        ];

        $response = $client->request('GET', '/me', $options);

        return json_decode($response->getBody(), true);
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

            $headers['Authorization'] = 'Bearer '.$token->token;
        }

        $options = [
            'base_uri' => 'https://graph.facebook.com/v2.8',
            'headers' => $headers
        ];

        return new Client($options);
    }
}
