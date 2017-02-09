<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
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
        $criteria = craft()->elements->getCriteria('Social_LoginAccount');
        $loginAccounts = $criteria->find();

        return $loginAccounts;
    }

    /**
     * Get all of the social accounts for a given user id.
     *
     * @return array|null
     */
    public function getLoginAccountsByUserId($userId)
    {
        $criteria = craft()->elements->getCriteria('Social_LoginAccount');
        $criteria->userId = $userId;
        $loginAccounts = $criteria->find();

        return $loginAccounts;
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
        return craft()->elements->getElementById($id, 'Social_LoginAccount');
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

        $criteria = craft()->elements->getCriteria('Social_LoginAccount');
        $criteria->userId = $currentUser->id;
        $criteria->providerHandle = $providerHandle;
        $loginAccount = $criteria->first();

        return $loginAccount;
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
        $criteria = craft()->elements->getCriteria('Social_LoginAccount');
        $criteria->providerHandle = $providerHandle;
        $criteria->socialUid = $socialUid;
        $loginAccount = $criteria->first();

        return $loginAccount;
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
        $isNewAccount = !$account->id;

        if (!$isNewAccount)
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
        $accountRecord->providerHandle = $account->providerHandle;
        $accountRecord->socialUid = $account->socialUid;

        // validate
        $accountRecord->validate();

        $account->addErrors($accountRecord->getErrors());

        if (!$account->hasErrors())
        {
            $transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;

            try
            {
                if (craft()->elements->saveElement($account))
                {
                    // Now that we have an element ID, save it on the other stuff
                    if ($isNewAccount)
                    {
                        $accountRecord->id = $account->id;
                    }

                    $accountRecord->save(false);

                    if ($transaction !== null)
                    {
                        $transaction->commit();
                    }

                    return true;
                }
            }
            catch (\Exception $e)
            {
                if ($transaction !== null)
                {
                    $transaction->rollback();
                }

                throw $e;
            }

            return true;
        }
        else
        {
            return false;
        }
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
        $loginAccount = $this->getLoginAccountByLoginProvider($providerHandle);

        return $this->deleteLoginAccounts($loginAccount);
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
        $loginAccounts = $this->getLoginAccountById($userId);

        return $this->deleteLoginAccounts($loginAccounts);
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
        $loginAccount = $this->getLoginAccountById($id);

        return $this->deleteLoginAccounts($loginAccount);
    }

    /**
     * Deletes login accounts
     *
     * @param Social_LoginAccount|array $loginAccounts
     *
     * @return bool
     */
    public function deleteLoginAccounts($loginAccounts)
    {
        if (!$loginAccounts)
        {
            return false;
        }

        if (!is_array($loginAccounts))
        {
            $loginAccounts = [$loginAccounts];
        }

        $loginAccountIds = [];

        foreach ($loginAccounts as $loginAccount)
        {
            $loginAccountIds[] = $loginAccount->id;
        }

        return craft()->elements->deleteElementById($loginAccountIds);
    }

    /**
     * Register a user.
     *
     * @param array $attributes Attributes of the user we want to register
     * @param string $providerHandle
     *
     * @throws Exception
     * @return UserModel|null
     */
    public function registerUser($attributes, $providerHandle)
    {
        if (!empty($attributes['email']))
        {
            // check domain locking

            $lockDomains = craft()->config->get('lockDomains', 'social');

            if(count($lockDomains) > 0)
            {
                $domainRejected = true;

                foreach($lockDomains as $lockDomain)
                {
                    if(strpos($attributes['email'], '@'.$lockDomain) !== false)
                    {
                        $domainRejected = false;
                    }
                }

                if($domainRejected)
                {
                    throw new Exception("Couldn’t register with this email (domain is not allowed): ".$attributes['email']);
                }
            }

            // find user from email
            $user = craft()->users->getUserByUsernameOrEmail($attributes['email']);

            if (!$user)
            {
                $user = $this->_registerUser($attributes, $providerHandle);
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
            'account' => &$attributes,
        ]);

        $this->onBeforeRegister($event);

        if ($event->performAction)
        {
            $variables = $attributes;

            $providerConfig = craft()->config->get($providerHandle, 'social');
            $userMapping = isset($providerConfig['userMapping']) ? $providerConfig['userMapping'] : null;

            $userModelAttributes = ['email', 'username', 'firstName', 'lastName', 'preferredLocale', 'weekStartDay'];

            $newUser = new UserModel();

            if ($settings['autoFillProfile'] && is_array($userMapping))
            {
                $userContent = [];

                foreach ($userMapping as $key => $template)
                {
                    // Check whether they try to set an attribute or a custom field
                    if (in_array($key, $userModelAttributes))
                    {
                        $attribute = $key;

                        if (array_key_exists($attribute, $newUser->getAttributes()))
                        {
                            try
                            {
                                $newUser->{$attribute} = craft()->templates->renderString($template, $variables);
                            }
                            catch(\Exception $e)
                            {
                                SocialPlugin::log('Could not map:'.print_r([$attribute, $template, $variables, $e->getMessage()], true), LogLevel::Warning);
                            }
                        }
                    }
                    else
                    {
                        $fieldHandle = $key;

                        // Check to make sure custom field exists for user profile
                        if (isset($newUser->getContent()[$fieldHandle]))
                        {
                            try
                            {
                                $userContent[$fieldHandle] = craft()->templates->renderString($template, $variables);
                            }
                            catch(\Exception $e)
                            {
                                SocialPlugin::log('Could not map:'.print_r([$template, $variables, $e->getMessage()], true), LogLevel::Warning);
                            }
                        }
                    }
                }

                $newUser->setContentFromPost($userContent);
            }


            // fill default email and username if not already done

            if (!$newUser->email)
            {
                $newUser->email = $attributes['email'];
            }

            if (!$newUser->username)
            {
                $newUser->username = $attributes['email'];
            }


            // save user

            if (!craft()->users->saveUser($newUser))
            {
                SocialPlugin::log('There was a problem creating the user:'.print_r($newUser->getErrors(), true), LogLevel::Error);
                throw new Exception("Craft user couldn’t be created.");
            }

            // save remote photo
            if ($settings['autoFillProfile'])
            {
                $photoUrl = false;

                if(isset($userMapping['photoUrl']))
                {
                    try
                    {
                        $photoUrl = craft()->templates->renderString($userMapping['photoUrl'], $variables);
                        $photoUrl = html_entity_decode($photoUrl);
                    }
                    catch(\Exception $e)
                    {
                        SocialPlugin::log('Could not map:'.print_r(['photoUrl', $userMapping['photoUrl'], $variables, $e->getMessage()], true), LogLevel::Warning);
                    }
                }
                else
                {
                    if (!empty($attributes['photoUrl']))
                    {
                        $photoUrl = $attributes['photoUrl'];
                    }
                }

                if($photoUrl)
                {
                    craft()->social->saveRemotePhoto($photoUrl, $newUser);
                }
            }

            // save groups
            if (!empty($settings['defaultGroup']))
            {
                craft()->userGroups->assignUserToGroups($newUser->id, [$settings['defaultGroup']]);
            }

            craft()->users->saveUser($newUser);

            return $newUser;
        }

        return null;
    }
}
