<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\controllers;

use Craft;
use craft\web\Controller;
use dukt\social\web\assets\social\SocialAsset;
use dukt\social\Plugin as Social;

class LoginAccountsController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Login Accounts Index
     *
     * @return null
     */
    public function actionIndex()
    {
        Craft::$app->getView()->registerAssetBundle(SocialAsset::class);

        return $this->renderTemplate('social/loginaccounts/_index');
    }

    /**
     * Edit User's Login Accounts
     *
     * @param array $variable Route variables
     *
     * @throws HttpException
     * @return null
     */
    public function actionEdit($userId)
    {
        $user = Craft::$app->users->getUserById($userId);

        if ($user)
        {
            $loginAccounts = Social::$plugin->loginAccounts->getLoginAccountsByUserId($user->id);

            Craft::$app->getView()->registerAssetBundle(SocialAsset::class);

            return $this->renderTemplate('social/loginaccounts/_edit', [
                'userId' => $userId,
                'user' => $user,
                'loginAccounts' => $loginAccounts
            ]);
        }
        else
        {
            throw new HttpException(404);
        }
    }

    /**
     * Delete Login Account
     *
     * @return null
     */
    public function actionDeleteLoginAccount()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $loginAccountId = Craft::$app->request->getRequiredBodyParam('id');

        Social::$plugin->loginAccounts->deleteLoginAccountById($loginAccountId);

        return $this->asJson(array('success' => true));
    }
}
