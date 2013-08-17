<?php

namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'social/vendor/autoload.php');

use Guzzle\Http\Client;

class SocialService extends BaseApplicationComponent
{
    // --------------------------------------------------------------------

    public function login($providerClass)
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $params = array('provider' => $providerClass);

        $url = UrlHelper::getSiteUrl(craft()->config->get('actionTrigger').'/social/public/login', $params);

        Craft::log(__METHOD__." : Authenticate : ".$url, LogLevel::Info, true);

        return $url;
    }

    // --------------------------------------------------------------------

    public function logout()
    {
        return UrlHelper::getActionUrl('social/public/logout');
    }

    // --------------------------------------------------------------------

    public function connect($providerClass)
    {
        return craft()->oauth->connect($providerClass, null, null, true);
    }

    // --------------------------------------------------------------------

    public function disconnect($providerClass)
    {
        return craft()->oauth->disconnect('social.user', $providerClass);
    }

    // --------------------------------------------------------------------

    public function getAccount($providerClass)
    {
        return craft()->oauth->getAccount($providerClass, null, true);
    }

    // --------------------------------------------------------------------

    public function publishFacebook($element = false)
    {
        // set templates path

        $templatePath = craft()->path->getPluginsPath().'social/templates/';
        craft()->path->setTemplatesPath($templatePath);


        // load template with element

        $variables = array(
                'element' => $element
            );

        $json = craft()->templates->render('_publish/facebook-post.json', $variables);

        $opts = json_decode($json, true);


        // get authenticated provider

        $providerLib = craft()->oauth->getProvider('Facebook');

        $clientId = $providerLib->clientId;
        $clientSecret = $providerLib->clientSecret;

        $provider = craft()->oauth->getProviderLibrary('Facebook', 'social.user', true);


        // options : access token

        $opts['access_token'] = $provider->token->access_token;


        // options : message : clean up some stuff so facebook doesn't break

        if(isset($opts['message'])) {
            $opts['message'] = strip_tags($opts['message']);    
        }



        // publish to user stream

        $client = new Client('https://graph.facebook.com/');

        $request = $client->post('me/feed', array(), $opts);
        // $request = $client->post('me/photos', array(), $opts);

        $response = $client->send($request);
        $response = json_decode($response->getBody(), true);  


        var_dump($opts);
        die();      
    }
}

