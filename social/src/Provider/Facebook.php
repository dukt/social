<?php

namespace Dukt\Social\Provider;

use Guzzle\Http\Client;

class Facebook extends AbstractProvider {

    public function getName()
    {
        return "Facebook";
    }

    public function getProfile()
    {
        $response = $this->api('get', 'me');

        // return $response;

        return array(
            'id' => $response['id'],
            'email' => $response['email'],
            'username' => $response['username'],
            'photo' => 'http://graph.facebook.com/'.$response['id'].'/picture',
            'locale' => $response['locale'],
            'firstName' => $response['first_name'],
            'lastName' => $response['last_name'],
            'fullName' => $response['name'],
            'profileUrl' => $response['link'],
            'gender' => $response['gender'],
        );
    }

    public function api($method = 'get', $uri, $params = null, $headers = null, $postFields = null)
    {
        // client

        $client = new Client('https://graph.facebook.com/');

        $token = $this->token;

        $params['access_token'] = $token->accessToken;


        // request

        $query = '';

        if($params)
        {
            $query = http_build_query($params);

            if($query)
            {
                $query = '?'.$query;
            }
        }

        $url = $uri.$query;

        $response = $client->get($url, $headers, $postFields)->send();

        $response = $response->json();

        return $response;
    }
}