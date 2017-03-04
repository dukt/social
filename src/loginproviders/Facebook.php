<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\loginproviders;

use craft\helpers\UrlHelper;
use dukt\social\Plugin as Social;
use dukt\social\base\LoginProvider;
use GuzzleHttp\Client;
use dukt\social\models\Token;

class Facebook extends LoginProvider
{
    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getName()
    {
        return 'Facebook';
    }

    /**
     * Get the OAuth provider.
     *
     * @return mixed
     */
    protected function getOauthProvider()
    {
        $providerInfos = $this->getInfos();

        $config = [
            'clientId' => $providerInfos['oauthProviderOptions']['clientId'],
            'clientSecret' => $providerInfos['oauthProviderOptions']['clientSecret'],
            'graphApiVersion' => 'v2.8',
            'redirectUri' => UrlHelper::actionUrl('social/oauth/callback'),
        ];

        return new \League\OAuth2\Client\Provider\Facebook($config);
    }

    /**
     * @inheritdoc
     *
     * @return array|null
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
     *
     * @param Token $token
     *
     * @return array|null
     */
    public function getProfile(Token $token)
    {
        $remoteProfile = $this->getRemoteProfile($token);

        return [
            'id' => (isset($remoteProfile['id']) ? $remoteProfile['id'] : null ),
            'email' => (isset($remoteProfile['email']) ? $remoteProfile['email'] : null ),
            'firstName' => (isset($remoteProfile['first_name']) ? $remoteProfile['first_name'] : null ),
            'lastName' => (isset($remoteProfile['last_name']) ? $remoteProfile['last_name'] : null ),
            'photoUrl' => (isset($remoteProfile['picture']['data']['url']) ? $remoteProfile['picture']['data']['url'] : null ),

            'name' => (isset($remoteProfile['name']) ? $remoteProfile['name'] : null ),
            'hometown' => (isset($remoteProfile['hometown']) ? $remoteProfile['hometown'] : null ),
            'isDefaultPicture' => (isset($remoteProfile['picture']['data']['is_silhouette']) ? $remoteProfile['picture']['data']['is_silhouette'] : null ),
            'coverPhotoUrl' => (isset($remoteProfile['cover']['source']) ? $remoteProfile['cover']['source'] : null ),
            'gender' => (isset($remoteProfile['gender']) ? $remoteProfile['gender'] : null ),
            'locale' => (isset($remoteProfile['locale']) ? $remoteProfile['locale'] : null ),
            'link' => (isset($remoteProfile['link']) ? $remoteProfile['link'] : null ),
            'locationId' => (isset($remoteProfile['location']['id']) ? $remoteProfile['location']['id'] : null ),
            'locationName' => (isset($remoteProfile['location']['name']) ? $remoteProfile['location']['name'] : null ),
        ];
    }

    /**
     * @inheritDoc
     *
     * @param $token
     *
     * @return array|null
     */
    public function getRemoteProfile(Token $token)
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

    /**
     * Get the authenticated client
     *
     * @return Client
     */
    private function getClient(Token $token)
    {
        $headers = array();

        if($token)
        {

            $headers['Authorization'] = 'Bearer '.$token->getToken();
        }

        $options = [
            'base_uri' => 'https://graph.facebook.com/v2.8',
            'headers' => $headers
        ];

        return new Client($options);
    }

    /**
     * Get API Manager URL
     *
     * @return string
     */
    public function getManagerUrl()
    {
        return 'https://developers.facebook.com/apps';
    }

    /**
     * Get Scope Docs URL
     *
     * @return string
     */
    public function getScopeDocsUrl()
    {
        return 'https://developers.facebook.com/docs/facebook-login/permissions';
    }
}
