<?php

namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'social/vendor/autoload.php');

use Guzzle\Http\Client;

class SocialService extends BaseApplicationComponent
{
    // --------------------------------------------------------------------

    public function login($providerClass, $redirect = null, $scope = null)
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $params = array('provider' => $providerClass);

        if($redirect) {
            $params['redirect'] = $redirect;
        }

        if($scope) {
            $params['scope'] = base64_encode(serialize($scope));
        }

        $url = UrlHelper::getSiteUrl(craft()->config->get('actionTrigger').'/social/public/login', $params);

        Craft::log(__METHOD__." : Authenticate : ".$url, LogLevel::Info, true);

        return $url;
    }

    // --------------------------------------------------------------------

    public function logout($redirect = null)
    {
        $params = array('redirect' => $redirect);

        return UrlHelper::getActionUrl('social/public/logout', $params);
    }

    // --------------------------------------------------------------------

    public function connect($providerClass)
    {
        return craft()->oauth->connect($providerClass, null, null, true);
    }

    // --------------------------------------------------------------------

    public function disconnect($providerClass)
    {
        return craft()->oauth->disconnect($providerClass);
    }

    // --------------------------------------------------------------------

    public function getAccount($providerClass)
    {
        // get token

        $tokenRecord = craft()->oauth_tokens->tokenRecordByCurrentUser($providerClass);

        if(!$tokenRecord) {
            return null;
        }

        $token = unserialize(base64_decode($tokenRecord->token));

        // provider

        $provider = craft()->oauth->getProvider($providerClass);

        $provider->connect($token);

        return $provider->getUserInfo();
    }

    // --------------------------------------------------------------------

    public function getToken($providerClass)
    {
        $tokenRecord = craft()->oauth_tokens->tokenRecordByCurrentUser($providerClass);

        if(!$tokenRecord) {
            return null;
        }

        $token = unserialize(base64_decode($tokenRecord->token));

        return $token;
    }

    // --------------------------------------------------------------------
}

