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

        if(!$user) {
            return false;
        }

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


    public function loginCallback($opts)
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        // get options

        $providerClass = $opts['oauth.providerClass'];
        $socialRedirect = $opts['oauth.socialRedirect'] = craft()->httpSession->get('oauth.socialRedirect');
        $socialReferer = $opts['oauth.socialReferer'] = craft()->httpSession->get('oauth.socialReferer');;
        $token = $opts['oauth.token'];

        // token

        $token = @unserialize(base64_decode($token));


        // instantiate provider

        $provider = craft()->oauth->getProvider($providerClass);

        $provider->setToken($token);

        // get account

        try {
            $account = $provider->getAccount();
        } catch(\Exception $e) {

            // craft()->userSession->setError(Craft::t($e->getMessage()));

            craft()->httpSession->add('error', Craft::t($e->getMessage()));

            return $socialReferer;
        }


        if(!$account) {

            // craft()->userSession->setError(Craft::t("Couldn't connect to your account."));

            craft()->httpSession->add('error', Craft::t($e->getMessage()));

            return $socialReferer;
        }


        // ----------------------
        // find a matching user
        // ----------------------

        $user = null;
        $userId =  craft()->userSession->id;


        // define user with current user

        if($userId) {
            $user = craft()->users->getUserById($userId);
        }


        // no current user ? check with account email

        if(!$user) {
            if(isset($account->email)) {
                $user = craft()->users->getUserByUsernameOrEmail($account['email']);
            }
        }


        // still no user ? check with account mapping

        if(!$user) {

            $criteriaConditions = '
                provider=:provider AND
                userMapping=:userMapping
                ';

            $criteriaParams = array(
                ':provider' => $providerClass,
                ':userMapping' => $account['uid']
                );

            $tokenRecord = Oauth_TokenRecord::model()->find($criteriaConditions, $criteriaParams);

            if($tokenRecord) {
                $userId = $tokenRecord->userId;
                $user = craft()->users->getUserById($userId);
            }
        }


        // no matching user, create one

        if(!$user) {

            // new user

            if(isset($account['email'])) {
                // define email

                $usernameOrEmail = $account['email'];
            } else {

                // get social plugin settings

                $socialPlugin = craft()->plugins->getPlugin('social');
                $settings = $socialPlugin->getSettings();


                // no email allowed ?

                if($settings['allowFakeEmail']) {

                    // no email, we create a fake one

                    $usernameOrEmail = md5($account['uid']).'@'.strtolower($providerClass).'.social.dukt.net';
                } else {
                    // no email here ? we abort, craft requires at least a valid email

                    // add error before redirecting

                    // craft()->userSession->setError(Craft::t("This OAuth provider doesn't provide email sharing. Please try another one."));

                    craft()->httpSession->add('error', Craft::t("This OAuth provider doesn't provide the email address. Please try another one."));

                    return $socialReferer;
                }
            }

            $newUser = new UserModel();
            $newUser->username = $usernameOrEmail;
            $newUser->email = $usernameOrEmail;

            $newUser->newPassword = md5(serialize($provider->getToken()));


            // save user

            craft()->users->saveUser($newUser);

            $user = craft()->users->getUserByUsernameOrEmail($usernameOrEmail);
        }


        // ----------------------
        // save token record
        // ----------------------


        // try to find an existing token

        $tokenRecord = null;

        if($user) {
            $criteriaConditions = '
                provider=:provider AND
                userMapping=:userMapping AND
                userId is not null
                ';

            $criteriaParams = array(
                ':provider' => $providerClass,
                ':userMapping' => $account['uid']
                );

            $tokenRecord = Oauth_TokenRecord::model()->find($criteriaConditions, $criteriaParams);

            if($tokenRecord) {
                if($tokenRecord->user->id != $user->id) {
                    // provider account already in use by another user
                    die('provider account already in use by another craft user');
                }
            }
        }


        // or create a new one

        if(!$tokenRecord) {
            $tokenRecord = new Oauth_TokenRecord();
            $tokenRecord->userId = $user->id;
            $tokenRecord->provider = $providerClass;

            $tokenRecord->userMapping = $account['uid'];
        }


        //scope

        $scope = $opts['oauth.scope'];

        if(!$scope) {
            $scope = $provider->getScope();
        }


        // update token variables

        $tokenRecord->token = base64_encode(serialize($provider->getToken()));

        $tokenRecord->scope = $scope;


        // save token

        $tokenRecord->save();


        // login user to craft

        if($provider->getToken()) {
            craft()->social_userSession->login(base64_encode(serialize($provider->getToken())));
        }

        // clean session variables

        craft()->oauth->sessionClean();


        // redirect

        return $socialRedirect;
    }
}