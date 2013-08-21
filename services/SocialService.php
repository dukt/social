<?php

namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'social/vendor/autoload.php');

use Guzzle\Http\Client;

class SocialService extends BaseApplicationComponent
{
    // --------------------------------------------------------------------

    public function login($providerClass, $redirect = null)
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $params = array('provider' => $providerClass);

        if($redirect) {
            $params['redirect'] = $redirect;
        }

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
        return craft()->oauth->disconnect($providerClass);
    }

    // --------------------------------------------------------------------

    public function getAccount($providerClass)
    {
        // get token

        $tokenRecord = craft()->oauth->tokenRecordByCurrentUser($providerClass);

        if(!$tokenRecord) {
            return null;
        }

        $token = unserialize(base64_decode($tokenRecord->token));

        // provider

        $callbackUrl = UrlHelper::getSiteUrl(
            craft()->config->get('actionTrigger').'/oauth/public/connect',
            array('provider' => $providerClass)
        );

        $provider = craft()->oauth->providerInstantiate($providerClass, $callbackUrl, $token);

        return $provider->getAccount();
    }

    // --------------------------------------------------------------------
}

