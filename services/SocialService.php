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
}

