<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2021, Dukt
 * @license   https://github.com/dukt/social/blob/v2/LICENSE.md
 */

namespace dukt\social\services;

use Craft;
use craft\elements\User;
use craft\helpers\FileHelper;
use dukt\social\errors\ImageTypeException;
use dukt\social\errors\LoginAccountNotFoundException;
use dukt\social\helpers\SocialHelper;
use dukt\social\Plugin;
use yii\base\Component;
use craft\elements\User as UserModel;
use dukt\social\elements\LoginAccount;
use dukt\social\records\LoginAccount as LoginAccountRecord;
use craft\helpers\UrlHelper;

/**
 * The LoginAccounts service provides APIs for managing login accounts in Craft.
 *
 * An instance of the LoginAccounts service is globally accessible in Craft via [[Plugin::loginAccounts `Plugin::getInstance()->getLoginAccounts()`]].
 *
 * @author  Dukt <support@dukt.net>
 * @since   1.0
 */
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
     * @param $userId
     *
     * @return array|null
     */
    public function getLoginAccountsByUserId($userId)
    {
        return LoginAccount::find()->userId($userId)->all();
    }

    /**
     * Returns a social account from its ID.
     *
     *
     * @return LoginAccount|null
     */
    public function getLoginAccountById(int $id)
    {
        return Craft::$app->elements->getElementById($id);
    }

    /**
     * Get a social account by provider handle for the currently logged in user.
     *
     *
     * @return LoginAccount|null
     */
    public function getLoginAccountByLoginProvider(string $providerHandle)
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
     *
     * @return LoginAccount|null
     */
    public function getLoginAccountByUid(string $providerHandle, string $socialUid)
    {
        return LoginAccount::find()->providerHandle($providerHandle)->socialUid($socialUid)->one();
    }

    /**
     * Deletes a social account by provider.
     *
     * @param $providerHandle
     *
     * @return bool
     * @throws \Throwable
     */
    public function deleteLoginAccountByProvider($providerHandle): bool
    {
        $loginAccount = $this->getLoginAccountByLoginProvider($providerHandle);

        return $this->deleteLoginAccount($loginAccount);
    }

    /**
     * Deletes a social login account by its ID.
     *
     *
     * @return bool
     * @throws \Throwable
     */
    public function deleteLoginAccountById(int $id): bool
    {
        $loginAccount = $this->getLoginAccountById($id);

        return $this->deleteLoginAccount($loginAccount);
    }

    /**
     * Deletes a login account.
     *
     * @param LoginAccount $loginAccount
     *
     * @return bool
     * @throws \Throwable
     */
    public function deleteLoginAccount(LoginAccount $loginAccount): bool
    {
        Craft::$app->elements->deleteElement($loginAccount);

        return true;
    }

    /**
     * Get login URL.
     *
     * @param       $providerHandle
     * @param array $params
     *
     * @return string
     */
    public function getLoginUrl($providerHandle, array $params = []): string
    {
        $params['provider'] = $providerHandle;

        return UrlHelper::actionUrl('social/login-accounts/login', $params);
    }

    /**
     * Get link account URL.
     *
     * @param $handle
     *
     * @return string
     */
    public function getLoginAccountConnectUrl($handle): string
    {
        return UrlHelper::actionUrl('social/login-accounts/connect-login-account', [
            'provider' => $handle
        ]);
    }

    /**
     * Get unlink account URL.
     *
     * @param $handle
     *
     * @return string
     */
    public function getLoginAccountDisconnectUrl($handle): string
    {
        return UrlHelper::actionUrl('social/login-accounts/disconnect-login-account', [
            'provider' => $handle
        ]);
    }

    /**
     * Saves a remote photo.
     *
     * @param string $providerHandle
     * @param User $newUser
     * @param $profile
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \craft\errors\ImageException
     * @throws \craft\errors\VolumeException
     * @throws \yii\base\Exception
     */
    public function saveRemotePhoto(string $providerHandle, User $newUser, $profile)
    {
        $photoUrl = false;
        $loginProvider = Plugin::getInstance()->getLoginProviders()->getLoginProvider($providerHandle);
        $userFieldMapping = $loginProvider->getUserFieldMapping();

        if (isset($userFieldMapping['photo'])) {
            try {
                $photoUrl = html_entity_decode(Craft::$app->getView()->renderString($userFieldMapping['photo'], ['profile' => $profile]));
            } catch (\Exception $exception) {
                Craft::warning('Could not map:' . print_r(['photo', $userFieldMapping['photo'], $profile, $exception->getMessage()], true), __METHOD__);
            }
        }

        if ($photoUrl) {
            $this->_saveRemotePhoto($photoUrl, $newUser);
        }
    }

    /**
     * Save a remote photo.
     *
     * @param           $photoUrl
     * @param UserModel $user
     *
     * @return bool|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \craft\errors\ImageException
     * @throws \craft\errors\VolumeException
     * @throws \yii\base\Exception
     */
    private function _saveRemotePhoto($photoUrl, UserModel $user)
    {
        $filename = 'photo';

        $tempPath = Craft::$app->path->getTempPath() . '/social/userphotos/' . $user->email . '/';

        FileHelper::createDirectory($tempPath);

        $client = new \GuzzleHttp\Client();

        $response = $client->request('GET', $photoUrl, [
            'sink' => $tempPath . $filename
        ]);

        if ($response->getStatusCode() !== 200) {
            return;
        }

        $contentTypes = $response->getHeader('Content-Type');

        if (is_array($contentTypes) && isset($contentTypes[0])) {
            switch ($contentTypes[0]) {
                case 'image/gif':
                    $extension = 'gif';
                    break;
                case 'image/jpeg':
                    $extension = 'jpg';
                    break;
                case 'image/png':
                    $extension = 'png';
                    break;
                case 'image/svg+xml':
                    $extension = 'svg';
                    break;

                default:
                    throw new ImageTypeException('Image type “' . $contentTypes[0] . '” not supported');
            }
        } else {
            throw new ImageTypeException('Image type not supported');
        }

        rename($tempPath . $filename, $tempPath . $filename . '.' . $extension);

        $image = Craft::$app->images->loadImage($tempPath . $filename . '.' . $extension);
        $imageWidth = $image->getWidth();
        $imageHeight = $image->getHeight();

        $dimension = min($imageWidth, $imageHeight);
        $horizontalMargin = ($imageWidth - $dimension) / 2;
        $verticalMargin = ($imageHeight - $dimension) / 2;
        $image->crop($horizontalMargin, $imageWidth - $horizontalMargin, $verticalMargin, $imageHeight - $verticalMargin);

        Craft::$app->users->saveUserPhoto($tempPath . $filename . '.' . $extension, $user, $filename . '.' . $extension);

        return true;
    }

    // Private Methods
    // =========================================================================

    /**
     * Gets a user record by its ID.
     *
     * @param int $loginAccountId
     *
     * @return LoginAccountRecord
     * @throws LoginAccountNotFoundException if $loginAccountId is invalid
     */
    private function _getLoginAccountRecordById(int $loginAccountId): LoginAccountRecord
    {
        $loginAccountRecord = LoginAccountRecord::findOne($loginAccountId);

        if (!$loginAccountRecord instanceof \dukt\social\records\LoginAccount) {
            throw new LoginAccountNotFoundException(sprintf('No login account exists with the ID \'%d\'', $loginAccountId));
        }

        return $loginAccountRecord;
    }
}
