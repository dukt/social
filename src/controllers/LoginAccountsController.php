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

class LoginAccountsController extends Controller
{
	// Public Methods
	// =========================================================================

	/**
	 * Init
	 *
	 * @return null
	 */
	public function init()
	{
		parent::init();

        \dukt\social\Plugin::getInstance()->social->requireDependencies();
	}

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
	public function actionEdit(array $variables = array())
	{
		if (!empty($variables['userId']))
		{
			$user = craft()->users->getUserById($variables['userId']);

			if ($user)
			{
				$variables['user'] = $user;

				$loginAccounts = \dukt\social\Plugin::getInstance()->social_loginAccounts->getLoginAccountsByUserId($user->id);

				$variables['loginAccounts'] = $loginAccounts;

				return $this->renderTemplate('social/loginaccounts/_edit', $variables);
			}
			else
			{
				throw new HttpException(404);
			}
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
		$this->requireAjaxRequest();

		$loginAccountId = craft()->request->getRequiredPost('id');

		\dukt\social\Plugin::getInstance()->social_loginAccounts->deleteLoginAccountById($loginAccountId);
		return $this->asJson(array('success' => true));
	}
}
