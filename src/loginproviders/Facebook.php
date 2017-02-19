<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\loginproviders;

use dukt\social\Plugin as Social;
use GuzzleHttp\Client;
use dukt\social\models\Token;
use GuzzleHttp\HandlerStack;

class Facebook extends BaseProvider
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
	 * @inheritdoc
	 *
	 * @return string
	 */
	public function getOauthProviderHandle()
	{
		return 'facebook';
	}

    public function getOauthProviderClass()
    {
        return '\League\OAuth2\Client\Provider\Facebook';
    }

    public function getOauthProviderConfig()
    {
        $graphApiVersion = 'v2.8';

        $providerInfos = Social::$plugin->oauth->getProviderInfos('facebook');

        $config = [
            'clientId' => $providerInfos['clientId'],
            'clientSecret' => $providerInfos['clientSecret'],
            'graphApiVersion' => $graphApiVersion
        ];

        return $config;
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
/*		$oauthProvider = $this->getOauthProvider();

		$client = new Client('https://graph.facebook.com/v2.8');
		$client->addSubscriber($oauthProvider->getSubscriber($token));

		$fields = implode(',', [
			'id', 'name', 'first_name', 'last_name',
			'email', 'hometown', 'picture.type(large){url,is_silhouette}',
			'cover{source}', 'gender', 'locale', 'link',
			'location',
		]);

		$request = $client->get('/me?fields='.$fields);

		$response = $request->send();
		$json = $response->json();

		return $json;*/

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

        $jsonResponse = json_decode($response->getBody(), true);

        return $jsonResponse;
	}

    /**
     * Get the authenticated client
     *
     * @return Client
     */
    private function getClient(Token $token)
    {
        $options = [
            'base_uri' => 'https://graph.facebook.com/v2.8',
        ];

        if($token)
        {
            $provider = Social::$plugin->oauth->getProvider('facebook');

            $stack = $provider->getSubscriber($token);

            $options['handler'] = $stack;
        }

        return new Client($options);
    }
}
