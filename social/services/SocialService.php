<?php

/**
 * Social Login for Craft
 *
 * @package   Social Login
 * @author    Benjamin David
 * @copyright Copyright (c) 2013, Dukt
 * @link      http://dukt.net/craft/social/
 * @license   http://dukt.net/craft/social/docs/license
 */

namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'social/vendor/autoload.php');

use Guzzle\Http\Client;

class SocialService extends BaseApplicationComponent
{
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

    public function logout($redirect = null)
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $params = array('redirect' => $redirect);

        return UrlHelper::getActionUrl('social/public/logout', $params);
    }

    public function isTemporaryEmail($email)
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $user = craft()->users->getUserByUsernameOrEmail($email);

        $fake = '.social.dukt.net';
        $pos = strpos($user->email, $fake);
        $len = strlen($user->email);

        if($pos) {
            return true;
        }

        return false;
    }

    public function getTemporaryPassword($userId)
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $user = craft()->users->getUserById($userId);
        $fake = '.social.dukt.net';
        $pos = strpos($user->email, $fake);
        $len = strlen($user->email);

        if($pos) {

            // temporary email : [uid]@[providerHandle].social.dukt.net

            // retrieve providerHandle

            $handle = substr($user->email, 0, $pos);
            $handle = substr($handle, (strpos($handle, "@") + 1));

            // get token

            $token = craft()->oauth->getUserToken($handle);


            // md5

            $pass = md5(serialize($token->getRealToken()));

            return $pass;
        }

        return false;
    }

    public function userHasTemporaryUsername($userId)
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $user = craft()->users->getUserById($userId);

        if(strpos($user->username, '.social.dukt.net') !== false) {
            return true;
        }

        return false;
    }
}