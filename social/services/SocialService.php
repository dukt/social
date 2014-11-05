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

class SocialService extends BaseApplicationComponent
{
    public function requireOAuth()
    {
        if(!isset(craft()->oauth))
        {
            throw new Exception(Craft::t('OAuth plugin is required to perform this action.'));
        }
    }

    public function getAccountByUserId($userId)
    {
        $conditions = 'userId=:userId';
        $params = array(':userId' => $userId);

        $record = Social_AccountRecord::model()->find($conditions, $params);

        if ($record)
        {
            return Social_AccountModel::populateModel($record);
        }
    }

    public function saveToken(Oauth_TokenModel $tokenModel)
    {
        $this->requireOAuth();

        craft()->oauth->saveToken($tokenModel);
    }

    public function getSocialUserByUserId($userId, $provider)
    {
        $conditions = 'provider=:provider and userId=:userId';
        $params = array(':provider' => $provider, ':userId' => $userId);

        $record = Social_UserRecord::model()->find($conditions, $params);

        if ($record)
        {
            return Social_UserModel::populateModel($record);
        }
    }

    public function getTokenBySocialUserId($id)
    {
        $this->requireOAuth();

        $socialUser = $this->getSocialUserById($id);
        $tokenId = $socialUser->tokenId;
        $token = craft()->oauth->getTokenById($tokenId);

        return $token;
    }

    public function getUsers()
    {
        $conditions = '';
        $params = array();

        $records = Social_UserRecord::model()->findAll($conditions, $params);

        if ($records)
        {
            return Social_UserModel::populateModels($records);
        }
    }

    public function getScopes($handle)
    {
        $socialProvider = $this->getProvider($handle, false);

        if($socialProvider)
        {
            return $socialProvider->getScopes();
        }
        else
        {
            return array();
        }
    }

    public function getParams($handle)
    {
        $socialProvider = $this->getProvider($handle, false);

        if($socialProvider)
        {
            return $socialProvider->getParams();
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

        $tokenId = $record->tokenId;

        if($tokenId)
        {
            $tokenRecord = Oauth_TokenRecord::model()->findByPk($tokenId);

            if($tokenRecord)
            {
                $tokenRecord->delete();
            }
        }


        if ($record)
        {
            return $record->delete();
        }

        return false;
    }

    public function deleteSocialUserByUserId($userId)
    {
        $conditions = 'userId=:userId';
        $params = array(':userId' => $userId);

        $socialUserRecords = Social_UserRecord::model()->findAll($conditions, $params);

        foreach($socialUserRecords as $socialUserRecord)
        {
            if($socialUserRecord->tokenId)
            {
                $tokenRecord = Oauth_TokenRecord::model()->findByPk($socialUserRecord->tokenId);

                if($tokenRecord)
                {
                    $tokenRecord->delete();
                }
            }

            $socialUserRecord->delete();
        }

        return true;
    }

    public function getSocialUserById($id)
    {
        $record = Social_UserRecord::model()->findByPk($id);

        if ($record)
        {
            return Social_UserModel::populateModel($record);
        }
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

    public function getUserByTokenId($tokenId)
    {
        $conditions = 'tokenId=:tokenId';
        $params = array(':tokenId' => $tokenId);

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
        $socialUserRecord->tokenId = $socialUser->tokenId;
        $socialUserRecord->provider = $socialUser->provider;
        $socialUserRecord->socialUid = $socialUser->socialUid;

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


    public function saveAccount(Social_AccountModel $account)
    {
        if($account->id)
        {
            $accountRecord = Social_AccountRecord::model()->findById($account->id);

            if (!$accountRecord)
            {
                throw new Exception(Craft::t('No social account exists with the ID “{id}”', array('id' => $account->id)));
            }

            $oldSocialUser = Social_AccountModel::populateModel($accountRecord);
            $isNewAccount = false;
        }
        else
        {
            $accountRecord = new Social_AccountRecord;
            $isNewAccount = true;
        }

        // populate
        $accountRecord->userId = $account->userId;
        $accountRecord->hasEmail = $account->hasEmail;
        $accountRecord->hasPassword = $account->hasPassword;
        $accountRecord->temporaryEmail = $account->temporaryEmail;
        $accountRecord->temporaryPassword = $account->temporaryPassword;

        // validate
        $accountRecord->validate();

        $account->addErrors($accountRecord->getErrors());

        if (!$account->hasErrors())
        {
            $accountRecord->save(false);

            if (!$account->id)
            {
                $account->id = $accountRecord->id;
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
        $this->requireOAuth();

        $allProviders = craft()->oauth->getProviders($configuredOnly);

        $providers = array();

        foreach($allProviders as $provider)
        {
            $socialProvider = $this->getProvider($provider->getHandle(), $configuredOnly);

            if($socialProvider)
            {
                array_push($providers, $socialProvider);
            }
        }

        return $providers;
    }

    public function getProvider($handle,  $configuredOnly = true)
    {
        $this->requireOAuth();

        $className = '\\Dukt\\Social\\Provider\\'.ucfirst($handle);

        if(class_exists($className))
        {
            $socialProvider = new $className;

            $oauthProvider = craft()->oauth->getProvider($handle,  $configuredOnly);

            if($oauthProvider)
            {
                return $socialProvider;
            }
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

        if(isset($params['scopes']) && is_array($params['scopes']))
        {
            $params['scopes'] = urlencode(base64_encode(serialize($params['scopes'])));
        }


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
        $this->requireOAuth();

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
            throw new Exception("Email address not provided.");
        }

        $newUser = new UserModel();
        $newUser->username = $usernameOrEmail;
        $newUser->email = $usernameOrEmail;

        if(!empty($account['firstName']))
        {
            $newUser->firstName = $account['firstName'];
        }

        if(!empty($account['lastName']))
        {
            $newUser->lastName = $account['lastName'];
        }

        $newUser->newPassword = $account['newPassword'];


        // save user

        craft()->users->saveUser($newUser);
        craft()->db->getSchema()->refresh();
        $user = craft()->users->getUserByUsernameOrEmail($usernameOrEmail);

        // save photo

        if(!empty($account['photo']))
        {
            $this->saveRemotePhoto($account['photo'], $user);
        }

        // save groups

        if(!empty($settings['defaultGroup']))
        {
            craft()->userGroups->assignUserToGroups($user->id, array($settings['defaultGroup']));
        }

        return $user;
    }

    public function saveRemotePhoto($photoUrl, $user)
    {
        $filename = 'photo';

        $tempPath = craft()->path->getTempPath().'social/userphotos/'.$user->email.'/';
        IOHelper::createFolder($tempPath);
        $tempFilepath = $tempPath.$filename;
        $client = new \Guzzle\Http\Client();
        $response = $client->get($photoUrl)
            ->setResponseBody($tempPath.$filename)
            ->send();


        $extension = substr($response->getContentType(), strpos($response->getContentType(), "/") + 1);

        IOHelper::rename($tempPath.$filename, $tempPath.$filename.'.'.$extension);

        craft()->users->deleteUserPhoto($user);

        $image = craft()->images->loadImage($tempPath.$filename.'.'.$extension);
        $imageWidth = $image->getWidth();
        $imageHeight = $image->getHeight();

        $dimension = min($imageWidth, $imageHeight);
        $horizontalMargin = ($imageWidth - $dimension) / 2;
        $verticalMargin = ($imageHeight - $dimension) / 2;
        $image->crop($horizontalMargin, $imageWidth - $horizontalMargin, $verticalMargin, $imageHeight - $verticalMargin);

        craft()->users->saveUserPhoto($filename.'.'.$extension, $image, $user);

        IOHelper::deleteFile($tempPath.$filename.'.'.$extension);

        return true;
    }
}