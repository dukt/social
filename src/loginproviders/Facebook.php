<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2017, Dukt
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
 * @author Dukt <support@dukt.net>
 * @since   1.0
 */
class Facebook extends LoginProvider
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getName()
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
            'id' => (isset($remoteProfile['id']) ? $remoteProfile['id'] : null),
            'email' => (isset($remoteProfile['email']) ? $remoteProfile['email'] : null),
            'firstName' => (isset($remoteProfile['first_name']) ? $remoteProfile['first_name'] : null),
            'lastName' => (isset($remoteProfile['last_name']) ? $remoteProfile['last_name'] : null),
            'photoUrl' => (isset($remoteProfile['picture']['data']['url']) ? $remoteProfile['picture']['data']['url'] : null),
            'name' => (isset($remoteProfile['name']) ? $remoteProfile['name'] : null),
            'hometown' => (isset($remoteProfile['hometown']) ? $remoteProfile['hometown'] : null),
            'isDefaultPicture' => (isset($remoteProfile['picture']['data']['is_silhouette']) ? $remoteProfile['picture']['data']['is_silhouette'] : null),
            'coverPhotoUrl' => (isset($remoteProfile['cover']['source']) ? $remoteProfile['cover']['source'] : null),
            'gender' => (isset($remoteProfile['gender']) ? $remoteProfile['gender'] : null),
            'locale' => (isset($remoteProfile['locale']) ? $remoteProfile['locale'] : null),
            'link' => (isset($remoteProfile['link']) ? $remoteProfile['link'] : null),
            'locationId' => (isset($remoteProfile['location']['id']) ? $remoteProfile['location']['id'] : null),
            'locationName' => (isset($remoteProfile['location']['name']) ? $remoteProfile['location']['name'] : null),
        ];
    }

    /**
     * Get the redirect URI.
     *
     * @return string
     */
    public function getRedirectUri()
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
    protected function getOauthProvider()
    {
        $providerInfos = $this->getInfos();

        $config = [
            'clientId' => $providerInfos['clientId'],
            'clientSecret' => $providerInfos['clientSecret'],
            'graphApiVersion' => 'v2.8',
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
    private function getClient(Token $token)
    {
        $headers = [];

        if ($token) {

            $headers['Authorization'] = 'Bearer '.$token->getToken();
        }

        $options = [
            'base_uri' => 'https://graph.facebook.com/v2.8',
            'headers' => $headers
        ];

        return new Client($options);
    }
}
