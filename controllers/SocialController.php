<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class SocialController extends BaseController
{
	// Properties
	// =========================================================================

	protected $allowAnonymous = ['actionLogin'];

	private $socialLoginProvider;
	private $pluginSettings;
	private $socialUid;
	private $redirect;
	private $referer;
	private $token;

	// Public Methods
	// =========================================================================

	/**
	 * Login
	 *
	 * @return null
	 */
	public function actionLogin()
	{
		craft()->social_plugin->checkRequirements();

		$this->referer = craft()->httpSession->get('social.referer');

		if (!$this->referer)
		{
			$this->referer = craft()->request->getUrlReferrer();
			craft()->httpSession->add('social.referer', $this->referer);
		}

		$this->redirect = craft()->request->getParam('redirect');

		$this->_connect();
	}

	/**
	 * Logout
	 *
	 * @return null
	 */
	public function actionLogout()
	{
		craft()->userSession->logout(false);

		$redirect = craft()->request->getParam('redirect');

		if (!$redirect)
		{
			$redirect = craft()->request->getUrlReferrer();
		}

		$this->redirect($redirect);
	}

	/**
	 * Link Account
	 *
	 * @return null
	 */
	public function actionConnectLoginAccount()
	{
		$this->actionLogin();
	}

	/**
	 * Unlink Account
	 *
	 * @return null
	 */
	public function actionDisconnectLoginAccount()
	{
		craft()->social_plugin->checkRequirements();

		$handle = craft()->request->getParam('provider');

		// delete token and social user
		craft()->social_loginAccounts->deleteLoginAccountByProvider($handle);

		craft()->userSession->setNotice(Craft::t('Login account disconnected.'));

		// redirect
		$redirect = craft()->request->getUrlReferrer();
		$this->redirect($redirect);
	}

    /**
     * Change Photo
     *
     * @return null
     */
    public function actionChangePhoto()
    {
        $userId = craft()->request->getParam('userId');
        $photoUrl = craft()->request->getParam('photoUrl');

        $user = craft()->users->getUserById($userId);

        craft()->social->saveRemotePhoto($photoUrl, $user);

        // redirect
        $referrer = craft()->request->getUrlReferrer();
        $this->redirect($referrer);
    }

	// Private Methods
	// =========================================================================

	/**
	 * Connect
	 *
	 * @return null
	 */
	private function _connect()
	{
		// request params
		$providerHandle = craft()->request->getParam('provider');
		$oauthProvider = craft()->oauth->getProvider($providerHandle);
		$requestUri = craft()->request->requestUri;
		$extraScopes = craft()->request->getParam('scope');
		craft()->httpSession->add('social.requestUri', $requestUri);

		// settings
		$plugin = craft()->plugins->getPlugin('social');
		$this->pluginSettings = $plugin->getSettings();


		// try to connect

		try
		{
			if(!$oauthProvider || $oauthProvider && !$oauthProvider->isConfigured())
			{
				throw new Exception("OAuth provider is not configured");
			}

			if (!$this->pluginSettings['enableSocialLogin'])
			{
				throw new Exception("Social login is disabled");
			}

			if (craft()->getEdition() != Craft::Pro)
			{
				throw new Exception("Craft Pro is required");
			}


			// provider scope & authorizationOptions

			$socialProvider = craft()->social_loginProviders->getLoginProvider($providerHandle);

			$scope = $socialProvider->getScope();
			$authorizationOptions = $socialProvider->getAuthorizationOptions();

			if ($response = craft()->oauth->connect([
				'plugin'   => 'social',
				'provider' => $providerHandle,
				'scope'   => $scope,
				'authorizationOptions'   => $authorizationOptions
			]))
			{
				$this->_handleOAuthResponse($providerHandle, $response);
			}
		}
		catch (\Exception $e)
		{
			craft()->userSession->setFlash('error', $e->getMessage());

			$this->_cleanSession();

			$this->redirect($this->referer);
		}
	}

	/**
	 * Handle OAuth Response
	 *
	 * @param string $providerHandle	Handle of the provider
	 * @param string $response      Provider response as an array
	 *
	 * @throws Exception
	 * @return null
	 */
	private function _handleOAuthResponse($providerHandle, $response)
	{
		$this->socialLoginProvider = craft()->social_loginProviders->getLoginProvider($providerHandle);

		if ($response['success'])
		{
			$token = $response['token'];

			$this->token = $token;

			$plugin = craft()->plugins->getPlugin('social');


			// user
			$craftUser = craft()->userSession->getUser();

			if ($craftUser)
			{
				$this->_linkAccount($craftUser);
			}
			else
			{
				$this->_login();
			}
		}
		else
		{
			throw new \Exception($response['errorMsg']);
		}
	}

	/**
	 * Handle Logged In User
	 *
	 * @param object $craftUser The logged-in user object
	 *
	 * @throws Exception
	 * @return null
	 */
	private function _linkAccount($craftUser)
	{
		$this->_cleanSession();

		if (!$this->redirect)
		{
			$this->redirect = $this->referer;
		}

		$remoteAccount = $this->socialLoginProvider->getAccount($this->token);

		$socialUid = $remoteAccount['uid'];

		$account = craft()->social_loginAccounts->getLoginAccountByUid($this->socialLoginProvider->getHandle(), $socialUid);

		if ($account)
		{
			if ($craftUser->id == $account->userId)
			{
				// save token

				$tokenId = $account->tokenId;
				$existingToken = craft()->oauth->getTokenById($tokenId);

				if ($existingToken)
				{
					$this->token->id = $existingToken->id;
				}

				craft()->social_loginAccounts->saveToken($this->token);

				// save user
				$account->tokenId = $this->token->id;
				craft()->social_loginAccounts->saveLoginAccount($account);

				craft()->userSession->setNotice(Craft::t('Social account linked.'));

				$this->redirect($this->redirect);
			}
			else
			{
				throw new Exception("This UID is already associated with another user. Disconnect from your current session and retry.");
			}
		}
		else
		{
			// save token
			craft()->social_loginAccounts->saveToken($this->token);

			// save social user
			$account = new Social_LoginAccountModel;
			$account->userId = $craftUser->id;
			$account->providerHandle = $this->socialLoginProvider->getHandle();
			$account->socialUid = $socialUid;
			$account->tokenId = $this->token->id;

			craft()->social_loginAccounts->saveLoginAccount($account);

			craft()->userSession->setNotice(Craft::t('Social account linked.'));

			$this->redirect($this->redirect);
		}
	}

	/**
	 * Handle Guest User
	 *
	 * @return null
	 */
	private function _login()
	{
		$leagueUser = $this->socialLoginProvider->getAccount($this->token);

		if(!is_array($leagueUser))
		{
			$attributes = $leagueUser->getArrayCopy();
		}
		else
		{
			$attributes = $leagueUser;
		}

		$socialUid = $attributes['uid'];

		$account = craft()->social_loginAccounts->getLoginAccountByUid($this->socialLoginProvider->getHandle(), $socialUid);

		if ($account)
		{
			$craftUser = craft()->users->getUserById($account->userId);

			if ($craftUser)
			{
				// existing token
				if (!empty($account->tokenId))
				{
					$this->token->id = $account->tokenId;
				}

				// save token
				craft()->social_loginAccounts->saveToken($this->token);

				// save user
				$account->tokenId = $this->token->id;
				craft()->social_loginAccounts->saveLoginAccount($account);

				// login
				$this->_handleLogin($account);
			}
			else
			{
				throw new Exception("Social account exists but Craft user doesn't");
			}
		}
		else
		{
			// register user
			$craftUser = craft()->social_loginAccounts->registerUser($attributes, $this->socialLoginProvider->getHandle(), $this->token);

			if ($craftUser)
			{
				// save token
				craft()->social_loginAccounts->saveToken($this->token);

				// save social user
				$account = new Social_LoginAccountModel;
				$account->userId = $craftUser->id;
				$account->providerHandle = $this->socialLoginProvider->getHandle();
				$account->socialUid = $socialUid;
				$account->tokenId = $this->token->id;
				craft()->social_loginAccounts->saveLoginAccount($account);

				// login
				$this->_handleLogin($account, true);
			}
			else
			{
				throw new Exception("Craft user couldn’t be created.");
			}
		}
	}

    /**
     * Handle Login
     *
     * @return null
     */
	private function _handleLogin(Social_LoginAccountModel $account, $registrationMode = false)
	{
		$this->_cleanSession();

		if (!$this->redirect)
		{
			$this->redirect = $this->referer;
		}

		if(craft()->social_userSession->login($account->id))
		{
			if($registrationMode)
			{
				craft()->userSession->setNotice(Craft::t('Account created.'));
			}
			else
			{
				craft()->userSession->setNotice(Craft::t('Logged in.'));
			}

			$this->redirect($this->redirect);
		}
		else
		{
			$errorCode = craft()->social_userSession->getLoginErrorCode();
			$errorMessage = craft()->social_userSession->getLoginErrorMessage($errorCode, $account->user->username);

			craft()->userSession->setError($errorMessage);

			$this->redirect($this->referer);
		}

	}

	/**
	 * Clean session variables
	 *
	 * @return null
	 */
	private function _cleanSession()
	{
		craft()->httpSession->remove('social.referer');
		craft()->httpSession->remove('social.requestUri');
		craft()->httpSession->remove('social.token');
		craft()->httpSession->remove('social.uid');
		craft()->httpSession->remove('social.providerHandle');
	}
}