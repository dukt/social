<?php

namespace Dukt\Social\Provider;

use Guzzle\Http\Client;

class Twitter extends BaseProvider {

    public function getName()
    {
        return "Twitter";
    }

    public function getProfile()
    {
        $token = $this->token;
        // $extraParams = $token->getExtraParams();
        // $user_id = $extraParams['user_id'];
        $response = $this->api('get', 'account/verify_credentials');

        // return $response;

        return array(
            'id' => $response['id_str'],
            'username' => $response['screen_name'],
            'photo' => $response['profile_image_url'],
            'locale' => $response['lang'],
            'fullName' => $response['name'],
            'profileUrl' => 'https://twitter.com/'.$response['screen_name'],
        );
    }

    public function api($method = 'get', $uri, $params = null, $headers = null, $postFields = null)
    {
        // client

        $client = new Client('https://api.twitter.com/1.1');

        $provider = \Craft\craft()->oauth->getProvider('twitter');

        $token = $this->token;

        $oauthData = array(
            'consumer_key'    => $provider->provider->clientId,
            'consumer_secret' => $provider->provider->clientSecret,
            'token'           => $token->accessToken,
            'token_secret'    => $token->secret
        );

        $oauth = new \Guzzle\Plugin\Oauth\OauthPlugin($oauthData);

        $client->addSubscriber($oauth);


        // request

        $format = 'json';

        $query = '';

        if($params)
        {
            $query = http_build_query($params);

            if($query)
            {
                $query = '?'.$query;
            }
        }

        $url = $uri.'.'.$format.$query;

        $response = $client->get($url, $headers, $postFields)->send();

        $response = $response->json();

        return $response;
    }
}