<?php

namespace Dukt\Social\Provider;

use Guzzle\Http\Client;

class Github extends BaseProvider {

    public function getName()
    {
        return "GitHub";
    }

    public function getScopes()
    {
        return array(
            'user, repo',
        );
    }

    public function getProfile()
    {
        $response = $this->api('get', 'user');

        return array(
            'id' => $response['id'],
            'email' => $response['email'],
            'username' => $response['login'],
            'photo' => $response['avatar_url'],
            'fullName' => $response['name'],
            'profileUrl' => $response['html_url'],
            'location' => $response['location'],
            'company' => $response['company'],
        );

    }

    public function api($method = 'get', $uri, $params = null, $headers = null, $postFields = null)
    {
        // client
        $client = new Client('https://api.github.com/');

        //token
        $token = $this->token;

        // params
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
