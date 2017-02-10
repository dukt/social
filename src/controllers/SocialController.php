<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\controllers;

use craft\web\Controller;

class SocialController extends Controller
{
	// Properties
	// =========================================================================

    /**
     * @inheritdoc
     *
     * @var array
     */
	protected $allowAnonymous = ['actionLogin'];

    /**
     * Redirect URL
     *
     * @var string
     */
	private $redirect;

    /**
     * Referer URL
     *
     * @var string
     */
	private $referer;

	// Public Methods
	// =========================================================================

	/**
	 * Login
	 *
	 * @return null
	 */
	public function actionLogin()
	{
		\dukt\social\Plugin::getInstance()->social->checkPluginRequirements();

		$this->referer = craft()->httpSession->get('social.referer');

		if (!$this->referer)
		{
			$this->referer = craft()->request->getUrlReferrer();
			craft()->httpSession->add('social.referer', $this->referer);
		}

		$this->redirect = craft()->request->getParam('redirect');


		// Connect

		// Request params
		$providerHandle = craft()->request->getParam('provider');
		$oauthProvider = \dukt\oauth\Plugin::getInstance()->oauth->getProvider($providerHandle);
		$requestUri = craft()->request->requestUri;
		craft()->httpSession->add('social.requestUri', $requestUri);

		// Settings
		$plugin = craft()->plugins->getPlugin('social');
		$pluginSettings = $plugin->getSettings();

		// Try to connect
		try
		{
			if (!$oauthProvider || ($oauthProvider && !$oauthProvider->isConfigured()))
			{
				throw new Exception("OAuth provider is not configured");
			}

			if (!$pluginSettings['enableSocialLogin'])
			{
				throw new Exception("Social login is disabled");
			}

			if (craft()->getEdition() != Craft::Pro)
			{
				throw new Exception("Craft Pro is required");
			}

			// Provider scope & authorizationOptions
			$socialProvider = \dukt\social\Plugin::getInstance()->social_loginProviders->getLoginProvider($providerHandle);

			if (!$socialProvider)
			{
				throw new Exception("Login provider is not configured");
			}

			$scope = $socialProvider->getScope();
			$authorizationOptions = $socialProvider->getAuthorizationOptions();

			if ($response = \dukt\oauth\Plugin::getInstance()->oauth->connect([
				'plugin'   => 'social',
				'provider' => $providerHandle,
				'scope'   => $scope,
				'authorizationOptions'   => $authorizationOptions
			]))
			{
				if($response['success'])
				{
					$this->_connectUserFromToken($response['token']);
				}
				else
				{
					throw new \Exception($response['errorMsg']);
				}
			}
		}
		catch(\Guzzle\Http\Exception\BadResponseException $e)
		{
			$response = $e->getResponse();

			SocialPlugin::log((string) $response, LogLevel::Error);

			$body = $response->getBody();
			$json = json_decode($body, true);

			if($json)
			{
				$errorMsg = $json['error']['message'];
			}
			else
			{
				$errorMsg = "Couldn’t login.";
			}

			craft()->userSession->setFlash('error', $errorMsg);
			$this->_cleanSession();
			$this->redirect($this->referer);
		}
		catch (\Exception $e)
		{
			$errorMsg = $e->getMessage();
			craft()->userSession->setFlash('error', $errorMsg);
			$this->_cleanSession();
			$this->redirect($this->referer);
		}
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
	 * Connect a login account (link)
	 *
	 * @return null
	 */
	public function actionConnectLoginAccount()
	{
		$this->actionLogin();
	}

	/**
	 * Disconnect a login account (unlink)
	 *
	 * @return null
	 */
	public function actionDisconnectLoginAccount()
	{
		\dukt\social\Plugin::getInstance()->social->checkPluginRequirements();

		$handle = craft()->request->getParam('provider');

		// delete social user
		\dukt\social\Plugin::getInstance()->social_loginAccounts->deleteLoginAccountByProvider($handle);

		craft()->userSession->setNotice(Craft::t('app', 'Login account disconnected.'));

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

		\dukt\social\Plugin::getInstance()->social->saveRemotePhoto($photoUrl, $user);

		// redirect
		$referrer = craft()->request->getUrlReferrer();
		$this->redirect($referrer);
	}

	// Private Methods
	// =========================================================================

	/**
	 * Connect (register, login, link) a user from token
	 *
	 * @param Oauth_TokenModel $token
	 */
	private function _connectUserFromToken(Oauth_TokenModel $token)
	{
		$craftUser = craft()->userSession->getUser();

		if ($craftUser)
		{
			$this->_linkAccountFromToken($token, $craftUser);
		}
		else
		{
			$this->_registerOrLoginFromToken($token);
		}
	}

	/**
	 * Link account from token
	 *
	 * @param object $craftUser The logged-in user object
	 *
	 * @throws Exception
	 * @return null
	 */
	private function _linkAccountFromToken(Oauth_TokenModel $token, $craftUser)
	{
		$this->_cleanSession();

		if (!$this->redirect)
		{
			$this->redirect = $this->referer;
		}

		$socialLoginProvider = \dukt\social\Plugin::getInstance()->social_loginProviders->getLoginProvider($token->providerHandle);

		$attributes = $socialLoginProvider->getProfile($token);

		$socialUid = $attributes['id'];

		$account = \dukt\social\Plugin::getInstance()->social_loginAccounts->getLoginAccountByUid($socialLoginProvider->getHandle(), $socialUid);

		if ($account)
		{
			if ($craftUser->id == $account->userId)
			{
				\dukt\social\Plugin::getInstance()->social_loginAccounts->saveLoginAccount($account);

				craft()->userSession->setNotice(Craft::t('app', 'Login account added.'));

				$this->redirect($this->redirect);
			}
			else
			{
				throw new Exception("This UID is already associated with another user. Disconnect from your current session and retry.");
			}
		}
		else
		{
			// save social user
			$account = new Social_LoginAccountModel;
			$account->userId = $craftUser->id;
			$account->providerHandle = $socialLoginProvider->getHandle();
			$account->socialUid = $socialUid;

			\dukt\social\Plugin::getInstance()->social_loginAccounts->saveLoginAccount($account);

			craft()->userSession->setNotice(Craft::t('app', 'Login account added.'));

			$this->redirect($this->redirect);
		}
	}

	/**
	 * Register or login user from an OAuth token
	 *
	 * @throws Exception
	 * @return null
	 */
	private function _registerOrLoginFromToken(Oauth_TokenModel $token)
	{
		$socialLoginProvider = \dukt\social\Plugin::getInstance()->social_loginProviders->getLoginProvider($token->providerHandle);

		$attributes = $socialLoginProvider->getProfile($token);

		$socialUid = $attributes['id'];

		$account = \dukt\social\Plugin::getInstance()->social_loginAccounts->getLoginAccountByUid($socialLoginProvider->getHandle(), $socialUid);

		if ($account)
		{
			$craftUser = craft()->users->getUserById($account->userId);

			if ($craftUser)
			{
				// save user
				\dukt\social\Plugin::getInstance()->social_loginAccounts->saveLoginAccount($account);

				// login
				$this->_login($token);
			}
			else
			{
				throw new Exception("Social account exists but Craft user doesn't");
			}
		}
		else
		{
			// Register user
			$craftUser = \dukt\social\Plugin::getInstance()->social_loginAccounts->registerUser($attributes, $socialLoginProvider->getHandle());

			if ($craftUser)
			{
				// Save social user
				$account = new Social_LoginAccountModel;
				$account->userId = $craftUser->id;
				$account->providerHandle = $socialLoginProvider->getHandle();
				$account->socialUid = $socialUid;
				\dukt\social\Plugin::getInstance()->social_loginAccounts->saveLoginAccount($account);

				// Login
				$this->_login($token, true);
			}
			else
			{
				throw new Exception("Craft user couldn’t be created.");
			}
		}
	}

	/**
	 * Login user from login account
	 *
	 * @return null
	 */
	private function _login(Oauth_TokenModel $token, $registrationMode = false)
	{
		$this->_cleanSession();

		if (!$this->redirect)
		{
			$this->redirect = $this->referer;
		}

		if (\dukt\social\Plugin::getInstance()->social_userSession->login($token))
		{
			if ($registrationMode)
			{
				craft()->userSession->setNotice(Craft::t('app', 'Account created.'));
			}
			else
			{
				craft()->userSession->setNotice(Craft::t('app', 'Logged in.'));
			}

			$this->redirect($this->redirect);
		}
		else
		{
			$errorCode = \dukt\social\Plugin::getInstance()->social_userSession->getLoginErrorCode();
			$errorMessage = \dukt\social\Plugin::getInstance()->social_userSession->getLoginErrorMessage($errorCode, $account->user->username);

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
	}
}
