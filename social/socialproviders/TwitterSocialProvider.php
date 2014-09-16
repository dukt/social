<?php

namespace Craft;

use Guzzle\Http\Client;

class TwitterSocialProvider extends BaseSocialProvider {

    public function getProfile()
    {
        $token = $this->token;
        $extraParams = $token->getExtraParams();
        $user_id = $extraParams['user_id'];
        $response = $this->api('get', 'users/lookup', array('user_id' => $user_id));

        // return $response;

        return array(
            'id' => $response[0]['id_str'],
            'username' => $response[0]['screen_name'],
            'photo' => $response[0]['profile_image_url'],
            'locale' => $response[0]['lang'],
            'fullName' => $response[0]['name'],
            'profileUrl' => 'https://twitter.com/'.$response[0]['screen_name'],
        );
    }

    public function api($method = 'get', $uri, $params = null, $headers = null, $postFields = null)
    {
        // client

        $client = new Client('https://api.twitter.com/1.1');

        $provider = craft()->oauth->getProvider('twitter');

        $token = $this->token;

        $oauth = new \Guzzle\Plugin\Oauth\OauthPlugin(array(
            'consumer_key'    => $provider->clientId,
            'consumer_secret' => $provider->clientSecret,
            'token'           => $token->getAccessToken(),
            'token_secret'    => $token->getAccessTokenSecret()
        ));

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