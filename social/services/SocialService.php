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
        Craft::log(__METHOD__, LogLevel::Info, true);

        $params = array('redirect' => $redirect);

        return UrlHelper::getActionUrl('social/public/logout', $params);
    }


    // --------------------------------------------------------------------

    public function getTemporaryPassword($userId)
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $user = craft()->users->getUserById($userId);
        $fake = '.social.dukt.net';
        $pos = strpos($user->email, $fake);
        $len = strlen($user->email);

        if($pos) {

            // temporary

            $handle = substr($user->email, 0, $pos);
            $handle = substr($handle, (strpos($handle, "@") + 1));

            $token = craft()->oauth->getUserToken($handle);

            $pass = md5(serialize($token->getRealToken()));
            $pass = "azeaze";

            return $pass;
        }

        return false;
    }


    // --------------------------------------------------------------------

    public function userHasTemporaryEmail($userId)
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $user = craft()->users->getUserById($userId);

        if(strpos($user->email, '.social.dukt.net') == false) {
            return true;
        }

        return false;
    }


    // --------------------------------------------------------------------

    public function userHasTemporaryUsername($userId)
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $user = craft()->users->getUserById($userId);

        if(strpos($user->username, '.social.dukt.net') == false) {
            return true;
        }

        return false;
    }

    // --------------------------------------------------------------------
}

