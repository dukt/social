<?php

/**
 * Social plugin for Craft
 *
 * @package   Craft Social
 * @author    Benjamin David
 * @copyright Copyright (c) 2015, Dukt
 * @link      https://dukt.net/craft/social/
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class SocialService extends BaseApplicationComponent
{
    /**
     * Check Requirements
     */
    public function checkRequirements()
    {
        $plugin = craft()->plugins->getPlugin('social');

        $pluginDependencies = $plugin->getPluginDependencies();

        if (count($pluginDependencies) > 0)
        {
            throw new \Exception("Social is not configured properly. Check Social settings for more informations.");
        }
    }

    public function getAccountByUserId($userId)
    {
        $conditions = 'userId=:userId';
        $params = [':userId' => $userId];

        $record = Social_AccountRecord::model()->find($conditions, $params);

        if ($record)
        {
            return Social_AccountModel::populateModel($record);
        }
    }

    public function getSocialUserByUserId($userId, $provider)
    {
        $conditions = 'provider=:provider and userId=:userId';
        $params = [':provider' => $provider, ':userId' => $userId];

        $record = Social_UserRecord::model()->find($conditions, $params);

        if ($record)
        {
            return Social_UserModel::populateModel($record);
        }
    }

    public function getUsers()
    {
        $conditions = '';
        $params = [];

        $records = Social_UserRecord::model()->findAll($conditions, $params);

        if ($records)
        {
            return Social_UserModel::populateModels($records);
        }
    }

    public function getScopes($handle)
    {
        $scopes = craft()->config->get($handle.'Scopes', 'social');

        if ($scopes)
        {
            return $scopes;
        }
        else
        {
            return [];
        }
    }

    public function getParams($handle)
    {
        $socialProvider = $this->getProvider($handle, false);

        if ($socialProvider)
        {
            return $socialProvider->getParams();
        }
        else
        {
            return [];
        }
    }

    public function getUserByProvider($handle)
    {
        $currentUser = craft()->userSession->getUser();
        $userId = $currentUser->id;

        $conditions = 'provider=:provider and userId=:userId';
        $params = [':provider' => $handle, ':userId' => $userId];

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
        $params = [':provider' => $handle, ':userId' => $userId];

        $record = Social_UserRecord::model()->find($conditions, $params);

        $tokenId = $record->tokenId;

        if ($tokenId)
        {
            $tokenRecord = Oauth_TokenRecord::model()->findByPk($tokenId);

            if ($tokenRecord)
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
        $params = [':userId' => $userId];

        $socialUserRecords = Social_UserRecord::model()->findAll($conditions, $params);

        foreach ($socialUserRecords as $socialUserRecord)
        {
            if ($socialUserRecord->tokenId)
            {
                $tokenRecord = Oauth_TokenRecord::model()->findByPk($socialUserRecord->tokenId);

                if ($tokenRecord)
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
        $params = [':provider' => $handle];

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
        if ($socialUser->id)
        {
            $socialUserRecord = Social_UserRecord::model()->findById($socialUser->id);

            if (!$socialUserRecord)
            {
                throw new Exception(Craft::t('No social user exists with the ID “{id}”', ['id' => $socialUser->id]));
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
        if ($account->id)
        {
            $accountRecord = Social_AccountRecord::model()->findById($account->id);

            if (!$accountRecord)
            {
                throw new Exception(Craft::t('No social account exists with the ID “{id}”', ['id' => $account->id]));
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
        $this->checkRequirements();

        $allProviders = craft()->oauth->getProviders($configuredOnly);

        $providers = [];

        foreach ($allProviders as $provider)
        {
            $socialProvider = $this->getProvider($provider->getHandle(), $configuredOnly);

            if ($socialProvider)
            {
                array_push($providers, $socialProvider);
            }
        }

        return $providers;
    }

    public function getProvider($handle, $configuredOnly = true)
    {
        $this->checkRequirements();

        $className = '\\Dukt\\Social\\Provider\\'.ucfirst($handle);

        if (class_exists($className))
        {
            $socialProvider = new $className;

            $oauthProvider = craft()->oauth->getProvider($handle, $configuredOnly);

            if ($oauthProvider)
            {
                return $socialProvider;
            }
        }
    }

    public function getConnectUrl($handle)
    {
        return UrlHelper::getActionUrl('social/connect', [
            'provider' => $handle
        ]);
    }

    public function getDisconnectUrl($handle)
    {
        return UrlHelper::getActionUrl('social/disconnect', [
            'provider' => $handle
        ]);
    }

    public function getLoginUrl($providerClass, $params = [])
    {
        $params['provider'] = $providerClass;

        if (isset($params['scopes']) && is_array($params['scopes']))
        {
            $params['scopes'] = urlencode(base64_encode(serialize($params['scopes'])));
        }

        $url = UrlHelper::getSiteUrl(craft()->config->get('actionTrigger').'/social/login', $params);

        Craft::log(__METHOD__." : Authenticate : ".$url, LogLevel::Info, true);

        return $url;
    }

    public function getLogoutUrl($redirect = null)
    {
        $params = ['redirect' => $redirect];

        return UrlHelper::getActionUrl('social/logout', $params);
    }

    public function registerUser($account)
    {
        // get social plugin settings

        $socialPlugin = craft()->plugins->getPlugin('social');
        $settings = $socialPlugin->getSettings();

        if (!$settings['allowSocialRegistration'])
        {
            throw new Exception("Social registration is disabled.");
        }


        // new user

        if (isset($account['email']))
        {
            // define email
            $usernameOrEmail = $account['email'];
        }
        else
        {
            throw new Exception("Email address not provided.");
        }


        // Fire an 'onBeforeRegister' event

        $event = new Event($this, [
            'account' => $account,
        ]);

        $this->onBeforeRegister($event);

        if ($event->performAction)
        {
            $newUser = new UserModel();
            $newUser->username = $usernameOrEmail;
            $newUser->email = $usernameOrEmail;

            if (!empty($account['firstName']))
            {
                $newUser->firstName = $account['firstName'];
            }

            if (!empty($account['lastName']))
            {
                $newUser->lastName = $account['lastName'];
            }

            $newUser->newPassword = $account['newPassword'];


            // save user

            craft()->users->saveUser($newUser);
            craft()->db->getSchema()->refresh();
            $user = craft()->users->getUserByUsernameOrEmail($usernameOrEmail);

            // save photo

            if (!empty($account['photo']))
            {
                $this->saveRemotePhoto($account['photo'], $user);
            }

            // save groups

            if (!empty($settings['defaultGroup']))
            {
                craft()->userGroups->assignUserToGroups($user->id, [$settings['defaultGroup']]);
            }

            return $user;
        }

        return false;
    }

    public function onBeforeRegister(Event $event)
    {
        $this->raiseEvent('onBeforeRegister', $event);
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