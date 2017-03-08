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
use dukt\social\Plugin as Social;

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

        return LoginAccount::find()->userId($currentUser->id)->providerHandle($providerHandle)->one();
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
        return LoginAccount::find()->providerHandle($providerHandle)->socialUid($socialUid)->one();
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
                throw new Exception(Craft::t('social', 'No social user exists with the ID “{id}”', ['id' => $account->id]));
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
}
