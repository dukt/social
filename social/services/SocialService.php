<?php

/**
 * Social Login for Craft
 *
 * @package   Social Login
 * @author    Benjamin David
 * @copyright Copyright (c) 2014, Dukt
 * @link      https://dukt.net/craft/social/
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

use Guzzle\Http\Client;

class SocialService extends BaseApplicationComponent
{
    private $supportedProviders = array(
            'facebook' => true,
            'github' => true,
            'google' => true,
            'twitter' => true
        );

    private $loginProviders = array(
            'facebook' => true,
            'github' => array(
                'scopes' => array(
                    'user',
                ),
            ),
            'google' => array(
                'scopes' => array(
                    'userinfo_profile',
                    'userinfo_email'
                ),
                'params' => array(
                    'access_type' => 'offline'
                )
            )
    );

    public function getScopes($handle)
    {
        if(!empty($this->loginProviders[$handle]['scopes']))
        {
            return $this->loginProviders[$handle]['scopes'];
        }
        else
        {
            return array();
        }
    }

    public function getParams($handle)
    {
        if(!empty($this->loginProviders[$handle]['params']))
        {
            return $this->loginProviders[$handle]['params'];
        }
        else
        {
            return array();
        }
    }

    public function getUserByProvider($handle)
    {
        $currentUser = craft()->userSession->getUser();
        $userId = $currentUser->id;

        $conditions = 'provider=:provider and userId=:userId';
        $params = array(':provider' => $handle, ':userId' => $userId);

        $record = Social_UserRecord::model()->find($conditions, $params);

        if ($record)
        {
            return Social_UserModel::populateModel($record);
        }
    }

    public function deleteUserByProvider($handle)
    {
        $currentUser = craft()->userSession->getUser();
        $userId = $currentUser->id;

        $conditions = 'provider=:provider and userId=:userId';
        $params = array(':provider' => $handle, ':userId' => $userId);

        $record = Social_UserRecord::model()->find($conditions, $params);

        if ($record)
        {
            return $record->delete();
        }

        return false;
    }

    public function getUserByUid($handle, $socialUid)
    {
        $conditions = 'provider=:provider';
        $params = array(':provider' => $handle);

        $conditions .= ' AND socialUid=:socialUid';
        $params[':socialUid'] = $socialUid;

        $record = Social_UserRecord::model()->find($conditions, $params);

        if ($record)
        {
            return Social_UserModel::populateModel($record);
        }
    }

    public function saveUser(Social_UserModel $socialUser)
    {
        if($socialUser->id)
        {
            $socialUserRecord = Social_UserRecord::model()->findById($socialUser->id);

            if (!$socialUserRecord)
            {
                throw new Exception(Craft::t('No social user exists with the ID “{id}”', array('id' => $socialUser->id)));
            }

            $oldSocialUser = Social_UserModel::populateModel($socialUserRecord);
            $isNewUser = false;
        }
        else
        {
            $socialUserRecord = new Social_UserRecord;
            $isNewUser = true;
        }

        // populate
        $socialUserRecord->userId = $socialUser->userId;
        $socialUserRecord->provider = $socialUser->provider;
        $socialUserRecord->socialUid = $socialUser->socialUid;
        $socialUserRecord->encodedToken = $socialUser->encodedToken;

        // validate
        $socialUserRecord->validate();

        $socialUser->addErrors($socialUserRecord->getErrors());

        if (!$socialUser->hasErrors())
        {
            $socialUserRecord->save(false);

            if (!$socialUser->id)
            {
                $socialUser->id = $socialUserRecord->id;
            }

            return true;
        }
        else
        {
            return false;
        }
    }

    public function getProviders($configuredOnly = true)
    {
        $allProviders = craft()->oauth->getProviders($configuredOnly);

        $providers = array();

        foreach($allProviders as $provider)
        {
            if(isset($this->supportedProviders[$provider->getHandle()]))
            {
                array_push($providers, $provider);
            }
        }

        return $providers;
    }

    public function getProvider($handle,  $configuredOnly = true)
    {
        if(isset($this->supportedProviders[$handle]))
        {
            return craft()->oauth->getProvider($handle,  $configuredOnly);
        }
    }
    public function getConnectUrl($handle)
    {
        return UrlHelper::getActionUrl('social/connect', array(
            'provider' => $handle
        ));
    }

    public function getDisconnectUrl($handle)
    {
        return UrlHelper::getActionUrl('social/disconnect', array(
            'provider' => $handle
        ));
    }

    public function getLoginUrl($providerClass, $params = array())
    {

        $params['provider'] = $providerClass;

        $url = UrlHelper::getSiteUrl(craft()->config->get('actionTrigger').'/social/login', $params);

        Craft::log(__METHOD__." : Authenticate : ".$url, LogLevel::Info, true);

        return $url;
    }

    public function getLogoutUrl($redirect = null)
    {
        $params = array('redirect' => $redirect);

        return UrlHelper::getActionUrl('social/logout', $params);
    }

    public function isTemporaryEmail($email)
    {
        $user = craft()->users->getUserByUsernameOrEmail($email);

        if(!$user)
        {
            return false;
        }

        $fake = '.social.dukt.net';

        $pos = strpos($user->email, $fake);
        $len = strlen($user->email);

        if($pos)
        {
            return true;
        }

        return false;
    }

    public function getTemporaryPassword($userId)
    {

        $user = craft()->users->getUserById($userId);
        $fake = '.social.dukt.net';
        $pos = strpos($user->email, $fake);
        $len = strlen($user->email);

        if($pos)
        {

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

        $user = craft()->users->getUserById($userId);

        if(strpos($user->username, '.social.dukt.net') !== false)
        {
            return true;
        }

        return false;
    }

    public function registerUser($account)
    {
        // get social plugin settings

        $socialPlugin = craft()->plugins->getPlugin('social');
        $settings = $socialPlugin->getSettings();

        if(!$settings['allowSocialRegistration'])
        {
            throw new Exception("Social registration is disabled.");
        }


        // new user

        if(isset($account['email']))
        {
            // define email
            $usernameOrEmail = $account['email'];
        }
        else
        {
            throw new Exception("This OAuth provider doesn't provide the email address. Please try another one.");

            // todo

            // // no email allowed ?

            // if($settings['allowFakeEmail'])
            // {
            //     // no email, we create a fake one
            //     $usernameOrEmail = md5($account['uid']).'@'.strtolower($providerClass).'.social.dukt.net';
            // }
            // else
            // {
                // no email here ? we abort, craft requires at least a valid email
            //  throw new Exception("This OAuth provider doesn't provide the email address. Please try another one.");
            // }
        }

        $newUser = new UserModel();
        $newUser->username = $usernameOrEmail;
        $newUser->email = $usernameOrEmail;

        $newUser->newPassword = md5(serialize(time()));


        // save user

        craft()->users->saveUser($newUser);
        craft()->db->getSchema()->refresh();
        $user = craft()->users->getUserByUsernameOrEmail($usernameOrEmail);


        // save groups

        if(!empty($settings['defaultGroup']))
        {
            craft()->userGroups->assignUserToGroups($user->id, array($settings['defaultGroup']));
        }

        return $user;
    }
}