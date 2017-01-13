<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class Social_LoginAccountsController extends BaseController
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

        craft()->social->requireDependencies();
	}

	/**
	 * Login Accounts Index
	 *
	 * @return null
	 */
	public function actionIndex()
	{
		$this->renderTemplate('social/loginaccounts/_index');
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

				$loginAccounts = craft()->social_loginAccounts->getLoginAccountsByUserId($user->id);

				$variables['loginAccounts'] = $loginAccounts;

				$this->renderTemplate('social/loginaccounts/_edit', $variables);
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

		craft()->social_loginAccounts->deleteLoginAccountById($loginAccountId);
		$this->returnJson(array('success' => true));
	}
}
