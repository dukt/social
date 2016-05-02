<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class Social_LoginAccountsService extends BaseApplicationComponent
{
    // Public Methods
    // =========================================================================

    /**
     * Get all social accounts.
     *
     * @return array|null
     */
    public function getLoginAccounts()
    {
        $conditions = '';
        $params = [];

        $records = Social_LoginAccountRecord::model()->findAll($conditions, $params);

        if ($records)
        {
            return Social_LoginAccountModel::populateModels($records);
        }
    }

    /**
     * Get all of the social accounts for a given user id.
     *
     * @return array|null
     */
    public function getLoginAccountsByUserId($userId)
    {
        $conditions = 'userId=:userId';
        $params = [':userId' => $userId];

        $records = Social_LoginAccountRecord::model()->findAll($conditions, $params);

        if ($records)
        {
            return Social_LoginAccountModel::populateModels($records);
        }
    }

    /**
     * Get a social account by it's id.
     *
     * @param int $id
     *
     * @return Social_LoginAccountModel|null
     */
    public function getLoginAccountById($id)
    {
        $record = Social_LoginAccountRecord::model()->findByPk($id);

        if ($record)
        {
            return Social_LoginAccountModel::populateModel($record);
        }
    }

    /**
     * Get a social account by provider handle for the currently logged in user.
     *
     * @param string $providerHandle
     *
     * @return Social_LoginAccountModel|null
     */
    public function getLoginAccountByLoginProvider($providerHandle)
    {
        $currentUser = craft()->userSession->getUser();

        // Check if there is a current user or not
        if (!$currentUser)
        {
            return false;
        }

        $userId = $currentUser->id;

        $conditions = 'providerHandle=:providerHandle and userId=:userId';
        $params = [':providerHandle' => $providerHandle, ':userId' => $userId];

        $record = Social_LoginAccountRecord::model()->find($conditions, $params);

        if ($record)
        {
            return Social_LoginAccountModel::populateModel($record);
        }
    }

    /**
     * Get a social account by social UID.
     *
     * @param string $providerHandle
     * @param string $socialUid
     *
     * @return Social_LoginAccountModel
     */
    public function getLoginAccountByUid($providerHandle, $socialUid)
    {
        $conditions = 'providerHandle=:providerHandle';
        $params = [':providerHandle' => $providerHandle];

        $conditions .= ' AND socialUid=:socialUid';
        $params[':socialUid'] = $socialUid;

        $record = Social_LoginAccountRecord::model()->find($conditions, $params);

        if ($record)
        {
            return Social_LoginAccountModel::populateModel($record);
        }
    }

    /**
     * Save Account
     *
     * @param Social_LoginAccountModel $account
     *
     * @throws Exception
     * @return bool
     */
    public function saveLoginAccount(Social_LoginAccountModel $account)
    {
        if ($account->id)
        {
            $accountRecord = Social_LoginAccountRecord::model()->findById($account->id);

            if (!$accountRecord)
            {
                throw new Exception(Craft::t('No social user exists with the ID “{id}”', ['id' => $account->id]));
            }
        }
        else
        {
            $accountRecord = new Social_LoginAccountRecord;
        }

        // populate
        $accountRecord->userId = $account->userId;
        $accountRecord->tokenId = $account->tokenId;
        $accountRecord->providerHandle = $account->providerHandle;
        $accountRecord->socialUid = $account->socialUid;

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

    /**
     * Save Token
     *
     * @param Oauth_TokenModel $token
     *
     * @return null
     */
    public function saveToken(Oauth_TokenModel $token)
    {
        $existingToken = null;

        if ($token->id)
        {
            $existingToken = craft()->oauth->getTokenById($token->id);

            if (!$existingToken)
            {
                $existingToken = null;
                $token->id = null;
            }
        }

        // onBeforeSaveToken

        // save token
        craft()->oauth->saveToken($token);
    }

    /**
     * Delete a social account by provider
     *
     * @param $providerHandle
     *
     * @return bool
     */
    public function deleteLoginAccountByProvider($providerHandle)
    {
        $currentUser = craft()->userSession->getUser();
        $userId = $currentUser->id;

        $conditions = 'providerHandle=:providerHandle and userId=:userId';
        $params = [':providerHandle' => $providerHandle, ':userId' => $userId];

        $record = Social_LoginAccountRecord::model()->find($conditions, $params);

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

    /**
     * Delete all social accounts by user ID
     *
     * @param int $userId
     *
     * @return bool
     */
    public function deleteLoginAccountByUserId($userId)
    {
        $conditions = 'userId=:userId';
        $params = array(':userId' => $userId);

        $accountRecords = Social_LoginAccountRecord::model()->findAll($conditions, $params);

        foreach($accountRecords as $accountRecord)
        {
            if($accountRecord->tokenId)
            {
                $tokenRecord = Oauth_TokenRecord::model()->findByPk($accountRecord->tokenId);

                if($tokenRecord)
                {
                    $tokenRecord->delete();
                }
            }

            $accountRecord->delete();
        }

        return true;
    }

    /**
     * Delete a social login account by it's ID
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteLoginAccountById($id)
    {
        $record = Social_LoginAccountRecord::model()->findByPk($id);

        if($record)
        {
            $tokenId = $record->tokenId;

            if ($tokenId)
            {
                $tokenRecord = Oauth_TokenRecord::model()->findByPk($tokenId);

                if ($tokenRecord)
                {
                    $tokenRecord->delete();
                }
            }

            return $record->delete();
        }
        else
        {
            return false;
        }
    }

    /**
     * Register a user.
     *
     * @param array $attributes Attributes of the user we want to register
     * @param string $providerHandle
     * @param Oauth_TokenModel $token
     *
     * @throws Exception
     * @return UserModel|null
     */
    public function registerUser($attributes, $providerHandle, $token)
    {
        if (!empty($attributes['email']))
        {
            // find user from email
            $user = craft()->users->getUserByUsernameOrEmail($attributes['email']);

            if (!$user)
            {
                $user = $this->_registerUser($attributes, $providerHandle, $token);
            }
            else
            {
                if (craft()->config->get('allowEmailMatch', 'social') !== true)
                {
                    throw new Exception("An account already exists with this email: ".$attributes['email']);
                }
            }
        }
        else
        {
            throw new Exception("Email address not provided.");
        }

        return $user;
    }

    /**
     * Fires an 'onBeforeRegister' event.
     *
     * @param Event $event
     *
     * @return null
     */
    public function onBeforeRegister(Event $event)
    {
        $this->raiseEvent('onBeforeRegister', $event);
    }

    // Private Methods
    // =========================================================================

    /**
     * Register a user.
     *
     * @param array $attributes Attributes of the user we want to register
     * @param string $providerHandle
     * @param $token
     *
     * @throws Exception
     * @return UserModel|null
     */
    private function _registerUser($attributes, $providerHandle)
    {
        // get social plugin settings

        $socialPlugin = craft()->plugins->getPlugin('social');
        $settings = $socialPlugin->getSettings();

        if (!$settings['enableSocialRegistration'])
        {
            throw new Exception("Social registration is disabled.");
        }

        // Fire an 'onBeforeRegister' event
        $event = new Event($this, [
            'account' => $attributes,
        ]);

        $this->onBeforeRegister($event);

        if ($event->performAction)
        {
            $variables = $attributes;

            $newUser = new UserModel();
            $newUser->username = $attributes['email'];
            $newUser->email = $attributes['email'];

            if($settings['autoFillProfile'])
            {
                // fill user from attributes
                $userMapping = craft()->config->get('userMapping', 'social');

                if(is_array($userMapping))
                {
                    foreach($userMapping as $attribute => $template)
                    {
                        if (array_key_exists($attribute, $newUser->getAttributes()))
                        {
                            try
                            {
                                $newUser->{$attribute} = craft()->templates->renderString($template, $variables);
                            }
                            catch(\Exception $e)
                            {
                                SocialPlugin::log('Could not map:'.print_r([$attribute, $template, $variables, $e->getMessage()], true), LogLevel::Error);
                            }
                        }
                    }
                }

                // fill user fields from attributes
                $userContentMapping = craft()->config->get('userContentMapping', 'social');

                if(isset($userContentMapping[$providerHandle]) && is_array($userContentMapping[$providerHandle]))
                {
                    foreach($userContentMapping[$providerHandle] as $field => $template)
                    {
                        // Check to make sure custom field exists for user profile
                        if (isset($newUser->getContent()[$field]))
                        {
                            try
                            {
                                $newUser->getContent()->{$field} = craft()->templates->renderString($template, $variables);
                            }
                            catch(\Exception $e)
                            {
                                SocialPlugin::log('Could not map:'.print_r([$template, $variables, $e->getMessage()], true), LogLevel::Error);
                            }
                        }
                    }
                }
            }

            // save user
            craft()->users->saveUser($newUser);
            craft()->db->getSchema()->refresh();
            $user = craft()->users->getUserByUsernameOrEmail($attributes['email']);

            // save remote photo
            if($settings['autoFillProfile'])
            {
                if (!empty($attributes['photoUrl']))
                {
                    craft()->social->saveRemotePhoto($attributes['photoUrl'], $user);
                }
            }

            // save groups
            if (!empty($settings['defaultGroup']))
            {
                craft()->userGroups->assignUserToGroups($user->id, [$settings['defaultGroup']]);
            }

            craft()->users->saveUser($user);

            return $user;
        }

        return null;
    }
}
