<?php

namespace Dukt\Social\Provider;

use \Google_Client;
use \Google_Service_Oauth2;

class Google extends AbstractProvider {

    public function getName()
    {
        return "Google";
    }

    public function getScopes()
    {
        return array(
            'https://www.googleapis.com/auth/userinfo.profile',
            'https://www.googleapis.com/auth/userinfo.email',
        );
    }

    public function getParams()
    {
        return array(
            'access_type' => 'offline',
            // 'approval_prompt' => 'force'
        );
    }
    public function getProfile()
    {
        try
        {
            $token = $this->token;

            if($token)
            {
                // make token compatible with Google library
                $arrayToken = array();
                $arrayToken['created'] = 0;
                $arrayToken['access_token'] = $token->accessToken;
                $arrayToken['expires_in'] = $token->endOfLife;
                $arrayToken = json_encode($arrayToken);


                // client
                $client = new Google_Client();
                $client->setApplicationName('Google+ PHP Starter Application');
                $client->setClientId('clientId');
                $client->setClientSecret('clientSecret');
                $client->setRedirectUri('redirectUri');
                $client->setAccessToken($arrayToken);

                // $api = new Google_Service_Analytics($client);

                $service = new Google_Service_Oauth2($client);

                $response = $service->userinfo->get();

                return array(
                    'id' => $response->id,
                    'email' => $response->email,
                    'photo' => $response->picture,
                    'locale' => $response->locale,
                    'firstName' => $response->givenName,
                    'lastName' => $response->familyName,
                    'profileUrl' => $response->link,
                    'gender' => $response->gender,
                );
            }
            else
            {
                Craft::log(__METHOD__.' : No token defined', LogLevel::Info, true);
                return false;
            }
        }
        catch(\Exception $e)
        {
            // todo: catch errors
            // throw $e;
        }
    }
}