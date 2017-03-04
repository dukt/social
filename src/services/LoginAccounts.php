<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\services;

use Craft;
use yii\base\Component;
use craft\elements\User as UserModel;
use dukt\social\elements\LoginAccount;
use Exception;
use craft\helpers\UrlHelper;

class LoginAccounts extends Component
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
        return LoginAccount::find()->all();
    }

    /**
     * Get all of the social accounts for a given user id.
     *
     * @return array|null
     */
    public function getLoginAccountsByUserId($userId)
    {
        return LoginAccount::find()->userId($userId)->all();
    }

    /**
     * Get a social account by it's id.
     *
     * @param int $id
     *
     * @return LoginAccount|null
     */
    public function getLoginAccountById($id)
    {
        return Craft::$app->elements->getElementById($id);
    }

    /**
     * Get a social account by provider handle for the currently logged in user.
     *
     * @param string $providerHandle
     *
     * @return LoginAccount|null
     */
    public function getLoginAccountByLoginProvider($providerHandle)
    {
        $currentUser = Craft::$app->getUser()->getIdentity();

        // Check if there is a current user or not
        if (!$currentUser) {
            return false;
        }

        return LoginAccount::find()->userId($currentUser->id)->providerHandle($providerHandle)->first();
    }

    /**
     * Get a social account by social UID.
     *
     * @param string $providerHandle
     * @param string $socialUid
     *
     * @return LoginAccount
     */
    public function getLoginAccountByUid($providerHandle, $socialUid)
    {
        return LoginAccount::find()->providerHandle($providerHandle)->socialUid($socialUid)->first();
    }

    /**
     * Save Account
     *
     * @param LoginAccount $account
     *
     * @throws Exception
     * @return bool
     */
    public function saveLoginAccount(LoginAccount $account)
    {
        $isNewAccount = !$account->id;

        if (!$isNewAccount) {
            $accountRecord = LoginAccount::model()->findById($account->id);

            if (!$accountRecord) {
                throw new Exception(Craft::t('app', 'No social user exists with the ID “{id}”', ['id' => $account->id]));
            }
        } else {
            $accountRecord = new LoginAccount;
        }

        // populate
        $accountRecord->userId = $account->userId;
        $accountRecord->providerHandle = $account->providerHandle;
        $accountRecord->socialUid = $account->socialUid;

        // validate
        $accountRecord->validate();

        $account->addErrors($accountRecord->getErrors());

        if (!$account->hasErrors()) {
            $transaction = Craft::$app->db->getCurrentTransaction() === null ? Craft::$app->db->beginTransaction() : null;

            try {
                if (Craft::$app->elements->saveElement($account)) {
                    // Now that we have an element ID, save it on the other stuff
                    if ($isNewAccount) {
                        $accountRecord->id = $account->id;
                    }

                    $accountRecord->save(false);

                    if ($transaction !== null) {
                        $transaction->commit();
                    }

                    return true;
                }
            } catch (\Exception $e) {
                if ($transaction !== null) {
                    $transaction->rollback();
                }

                throw $e;
            }

            return true;
        } else {
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
     * @param string|array $loginAccounts
     *
     * @return bool
     */
    public function deleteLoginAccounts($loginAccountStringOrArray)
    {
        if (!$loginAccountStringOrArray) {
            return false;
        }

        if (!is_array($loginAccountStringOrArray)) {
            $loginAccounts = [$loginAccountStringOrArray];
        } else {
            $loginAccounts = $loginAccountStringOrArray;
        }

        foreach ($loginAccounts as $loginAccount) {
            Craft::$app->elements->deleteElement($loginAccount);
        }

        return true;
    }

    /**
     * Register a user.
     *
     * @param array  $attributes Attributes of the user we want to register
     * @param string $providerHandle
     *
     * @throws Exception
     * @return UserModel|null
     */
    public function registerUser($attributes, $providerHandle)
    {
        if (!empty($attributes['email'])) {
            // check domain locking

            $lockDomains = Craft::$app->getConfig()->get('lockDomains', 'social');

            if (count($lockDomains) > 0) {
                $domainRejected = true;

                foreach ($lockDomains as $lockDomain) {
                    if (strpos($attributes['email'], '@'.$lockDomain) !== false) {
                        $domainRejected = false;
                    }
                }

                if ($domainRejected) {
                    throw new Exception("Couldn’t register with this email (domain is not allowed): ".$attributes['email']);
                }
            }

            // find user from email
            $user = Craft::$app->users->getUserByUsernameOrEmail($attributes['email']);

            if (!$user) {
                $user = $this->_registerUser($attributes, $providerHandle);
            } else {
                if (Craft::$app->getConfig()->get('allowEmailMatch', 'social') !== true) {
                    throw new Exception("An account already exists with this email: ".$attributes['email']);
                }
            }
        } else {
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

    // Former Social service methods
    // =========================================================================

    /**
     * Get login URL
     *
     * @param $providerHandle
     * @param array  $params
     *
     * @return string
     */
    public function getLoginUrl($providerHandle, array $params = [])
    {
        $params['provider'] = $providerHandle;

        if (isset($params['scope']) && is_array($params['scope']))
        {
            $params['scope'] = urlencode(base64_encode(serialize($params['scope'])));
        }

        $url = UrlHelper::siteUrl(Craft::$app->getConfig()->get('actionTrigger').'/social/login-accounts/login', $params);

        return $url;
    }

    /**
     * Get logout URL
     *
     * @param string|null $redirect
     *
     * @return string
     */
    public function getLogoutUrl($redirect = null)
    {
        $params = ['redirect' => $redirect];

        return UrlHelper::actionUrl('social/login-accounts/logout', $params);
    }

    /**
     * Get link account URL
     *
     * @param $handle
     *
     * @return string
     */
    public function getLoginAccountConnectUrl($handle)
    {
        return UrlHelper::actionUrl('social/login-accounts/connect-login-account', [
            'provider' => $handle
        ]);
    }

    /**
     * Get unlink account URL
     *
     * @param $handle
     *
     * @return string
     */
    public function getLoginAccountDisconnectUrl($handle)
    {
        return UrlHelper::actionUrl('social/login-accounts/disconnect-login-account', [
            'provider' => $handle
        ]);
    }

    /**
     * Save remote photo
     *
     * @param string $photoUrl
     * @param UserModel $user
     *
     * @return bool
     */
    public function saveRemotePhoto($photoUrl, UserModel $user)
    {
        $filename = 'photo';

        $tempPath = Craft::$app->path->getTempPath().'social/userphotos/'.$user->email.'/';
        IOHelper::createFolder($tempPath);
        $tempFilepath = $tempPath.$filename;
        $client = new \Guzzle\Http\Client();
        $response = $client->get($photoUrl)
            ->setResponseBody($tempPath.$filename)
            ->send();

        $extension = substr($response->getContentType(), strpos($response->getContentType(), "/") + 1);

        IOHelper::rename($tempPath.$filename, $tempPath.$filename.'.'.$extension);

        Craft::$app->users->deleteUserPhoto($user);

        $image = Craft::$app->images->loadImage($tempPath.$filename.'.'.$extension);
        $imageWidth = $image->getWidth();
        $imageHeight = $image->getHeight();

        $dimension = min($imageWidth, $imageHeight);
        $horizontalMargin = ($imageWidth - $dimension) / 2;
        $verticalMargin = ($imageHeight - $dimension) / 2;
        $image->crop($horizontalMargin, $imageWidth - $horizontalMargin, $verticalMargin, $imageHeight - $verticalMargin);

        Craft::$app->users->saveUserPhoto($filename.'.'.$extension, $image, $user);

        IOHelper::deleteFile($tempPath.$filename.'.'.$extension);

        return true;
    }

    // Private Methods
    // =========================================================================

    /**
     * Register a user.
     *
     * @param array  $attributes Attributes of the user we want to register
     * @param string $providerHandle
     *
     * @throws Exception
     * @return UserModel|null
     */
    private function _registerUser($attributes, $providerHandle)
    {
        // get social plugin settings

        $socialPlugin = Craft::$app->getPlugins()->getPlugin('social');
        $settings = $socialPlugin->getSettings();

        if (!$settings['enableSocialRegistration']) {
            throw new Exception("Social registration is disabled.");
        }


        // Fire an 'onBeforeRegister' event
        /*        $event = new Event($this, [
                    'account' => &$attributes,
                ]);

                $this->onBeforeRegister($event);*/

        /*        $this->trigger('beforeRegister', new ElementEvent([
                    'account' => &$attributes,
                ]));*/
        /*
                if ($event->performAction)
                {*/
        $variables = $attributes;

        $providerConfig = Craft::$app->getConfig()->get($providerHandle, 'social');
        $userMapping = isset($providerConfig['userMapping']) ? $providerConfig['userMapping'] : null;

        $userModelAttributes = ['email', 'username', 'firstName', 'lastName', 'preferredLocale', 'weekStartDay'];

        $newUser = new UserModel();

        if ($settings['autoFillProfile'] && is_array($userMapping)) {
            $userContent = [];

            foreach ($userMapping as $key => $template) {
                // Check whether they try to set an attribute or a custom field
                if (in_array($key, $userModelAttributes)) {
                    $attribute = $key;

                    if (array_key_exists($attribute, $newUser->getAttributes())) {
                        try {
                            $newUser->{$attribute} = Craft::$app->templates->renderString($template, $variables);
                        } catch (\Exception $e) {
                            // Social::log('Could not map:'.print_r([$attribute, $template, $variables, $e->getMessage()], true), LogLevel::Warning);
                        }
                    }
                } else {
                    $fieldHandle = $key;

                    // Check to make sure custom field exists for user profile
                    if (isset($newUser->getContent()[$fieldHandle])) {
                        try {
                            $userContent[$fieldHandle] = Craft::$app->templates->renderString($template, $variables);
                        } catch (\Exception $e) {
                            // Social::log('Could not map:'.print_r([$template, $variables, $e->getMessage()], true), LogLevel::Warning);
                        }
                    }
                }
            }

            $newUser->setContentFromPost($userContent);
        }


        // fill default email and username if not already done

        if (!$newUser->email) {
            $newUser->email = $attributes['email'];
        }

        if (!$newUser->username) {
            $newUser->username = $attributes['email'];
        }


        // save user

        if (!Craft::$app->elements->saveElement($newUser)) {
            // Social::log('There was a problem creating the user:'.print_r($newUser->getErrors(), true), LogLevel::Error);
            throw new Exception("Craft user couldn’t be created.");
        }

        // save remote photo
        if ($settings['autoFillProfile']) {
            $photoUrl = false;

            if (isset($userMapping['photoUrl'])) {
                try {
                    $photoUrl = Craft::$app->templates->renderString($userMapping['photoUrl'], $variables);
                    $photoUrl = html_entity_decode($photoUrl);
                } catch (\Exception $e) {
                    // Social::log('Could not map:'.print_r(['photoUrl', $userMapping['photoUrl'], $variables, $e->getMessage()], true), LogLevel::Warning);
                }
            } else {
                if (!empty($attributes['photoUrl'])) {
                    $photoUrl = $attributes['photoUrl'];
                }
            }

            if ($photoUrl) {
                // Social::$plugin->getSocial()->saveRemotePhoto($photoUrl, $newUser);
            }
        }

        // save groups
        if (!empty($settings['defaultGroup'])) {
            Craft::$app->userGroups->assignUserToGroups($newUser->id, [$settings['defaultGroup']]);
        }

        Craft::$app->elements->saveElement($newUser);

        return $newUser;
        /*        }

                return null;*/
    }
}
