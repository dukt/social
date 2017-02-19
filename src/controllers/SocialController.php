<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\controllers;

use Craft;
use craft\web\Controller;
use dukt\oauth\models\Token;
use dukt\social\elements\LoginAccount;
use dukt\social\Plugin as Social;

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
		Social::$plugin->social->checkPluginRequirements();

		$this->referer = Craft::$app->getSession()->get('social.referer');

		if (!$this->referer)
		{
			$this->referer = Craft::$app->request->referrer;
			Craft::$app->getSession()->set('social.referer', $this->referer);
		}

		$this->redirect = Craft::$app->request->getParam('redirect');


		// Connect

		// Request params
		$providerHandle = Craft::$app->request->getParam('provider');
		$oauthProvider = \dukt\oauth\Plugin::getInstance()->oauth->getProvider($providerHandle);
/*		$requestUri = Craft::$app->request->resolveRequestUri();
		Craft::$app->getSession()->set('social.requestUri', $requestUri);*/

		// Settings
		$plugin = Craft::$app->plugins->getPlugin('social');
		$pluginSettings = $plugin->getSettings();

		// Try to connect
/*		try
		{*/
			if (!$oauthProvider || ($oauthProvider && !$oauthProvider->isConfigured()))
			{
				throw new Exception("OAuth provider is not configured");
			}

			if (!$pluginSettings['enableSocialLogin'])
			{
				throw new Exception("Social login is disabled");
			}

			if (Craft::$app->getEdition() != Craft::Pro)
			{
				throw new Exception("Craft Pro is required");
			}

			// Provider scope & authorizationOptions
			$socialProvider = Social::$plugin->social_loginProviders->getLoginProvider($providerHandle);

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
                if($response && is_object($response) && !$response->data)
                {
                    return $response;
                }

				if($response['success'])
				{
					return $this->_connectUserFromToken($response['token']);
				}
				else
				{
					throw new \Exception($response['errorMsg']);
				}
			}
		/*}
		catch(\Guzzle\Http\Exception\BadResponseException $e)
		{
			$response = $e->getResponse();

			// Social::log((string) $response, LogLevel::Error);

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

			Craft::$app->getSession()->setFlash('error', $errorMsg);
			$this->_cleanSession();
			return $this->redirect($this->referer);
		}
		catch (\Exception $e)
		{
			$errorMsg = $e->getMessage();
			Craft::$app->getSession()->setFlash('error', $errorMsg);
			$this->_cleanSession();
			return $this->redirect($this->referer);
		}*/
	}

	/**
	 * Logout
	 *
	 * @return null
	 */
	public function actionLogout()
	{
		// Craft::$app->getSession()->logout(false);
        Craft::$app->getUser()->logout(false);

		$redirect = Craft::$app->request->getParam('redirect');

		if (!$redirect)
		{
			$redirect = Craft::$app->request->referrer;
		}

		return $this->redirect($redirect);
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
		Social::$plugin->social->checkPluginRequirements();

		$handle = Craft::$app->request->getParam('provider');

		// delete social user
		Social::$plugin->social_loginAccounts->deleteLoginAccountByProvider($handle);

		Craft::$app->getSession()->setNotice(Craft::t('app', 'Login account disconnected.'));

		// redirect
		$redirect = Craft::$app->request->referrer;
		return $this->redirect($redirect);
	}

	/**
	 * Change Photo
	 *
	 * @return null
	 */
	public function actionChangePhoto()
	{
		$userId = Craft::$app->request->getParam('userId');
		$photoUrl = Craft::$app->request->getParam('photoUrl');

		$user = Craft::$app->users->getUserById($userId);

		Social::$plugin->social->saveRemotePhoto($photoUrl, $user);

		// redirect
		$referrer = Craft::$app->request->referrer;
		return $this->redirect($referrer);
	}

	// Private Methods
	// =========================================================================

	/**
	 * Connect (register, login, link) a user from token
	 *
	 * @param Token $token
	 */
	private function _connectUserFromToken(Token $token)
	{
		$craftUser = Craft::$app->getUser()->getIdentity();

		if ($craftUser)
		{
			return $this->_linkAccountFromToken($token, $craftUser);
		}
		else
		{
			return $this->_registerOrLoginFromToken($token);
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
	private function _linkAccountFromToken(Token $token, $craftUser)
	{
		$this->_cleanSession();

		if (!$this->redirect)
		{
			$this->redirect = $this->referer;
		}

		$socialLoginProvider = Social::$plugin->social_loginProviders->getLoginProvider($token->providerHandle);

		$attributes = $socialLoginProvider->getProfile($token);

		$socialUid = $attributes['id'];

		$account = Social::$plugin->social_loginAccounts->getLoginAccountByUid($socialLoginProvider->getHandle(), $socialUid);

		if ($account)
		{
			if ($craftUser->id == $account->userId)
			{
				// Social::$plugin->social_loginAccounts->saveLoginAccount($account);
                Craft::$app->elements->saveElement($account);

				Craft::$app->getSession()->setNotice(Craft::t('app', 'Login account added.'));

				return $this->redirect($this->redirect);
			}
			else
			{
				throw new Exception("This UID is already associated with another user. Disconnect from your current session and retry.");
			}
		}
		else
		{
			// save social user
			$account = new LoginAccount;
			$account->userId = $craftUser->id;
			$account->providerHandle = $socialLoginProvider->getHandle();
			$account->socialUid = $socialUid;

			// Social::$plugin->social_loginAccounts->saveLoginAccount($account);

            Craft::$app->getElements()->saveElement($account);

			Craft::$app->getSession()->setNotice(Craft::t('app', 'Login account added.'));

			return $this->redirect($this->redirect);
		}
	}

	/**
	 * Register or login user from an OAuth token
	 *
	 * @throws Exception
	 * @return null
	 */
	private function _registerOrLoginFromToken(Token $token)
	{
		$socialLoginProvider = Social::$plugin->social_loginProviders->getLoginProvider($token->providerHandle);

		$attributes = $socialLoginProvider->getProfile($token);

		$socialUid = $attributes['id'];

		$account = Social::$plugin->social_loginAccounts->getLoginAccountByUid($socialLoginProvider->getHandle(), $socialUid);

		if ($account)
		{
			$craftUser = Craft::$app->users->getUserById($account->userId);

			if ($craftUser)
			{
				// save user
				// Social::$plugin->social_loginAccounts->saveLoginAccount($account);
                Craft::$app->elements->saveElement($account);

				// login
				return $this->_login($craftUser, $account, $token);
			}
			else
			{
				throw new Exception("Social account exists but Craft user doesn't");
			}
		}
		else
		{
			// Register user
			$craftUser = Social::$plugin->social_loginAccounts->registerUser($attributes, $socialLoginProvider->getHandle());

			if ($craftUser)
			{
				// Save social user
				$account = new LoginAccount;
				$account->userId = $craftUser->id;
				$account->providerHandle = $socialLoginProvider->getHandle();
				$account->socialUid = $socialUid;
				// Social::$plugin->social_loginAccounts->saveLoginAccount($account);

                Craft::$app->elements->saveElement($account);

				// Login
				return $this->_login($craftUser, $account, $token, true);
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
	private function _login(\craft\elements\User $craftUser, LoginAccount $account, Token $token, $registrationMode = false)
	{
		$this->_cleanSession();

		if (!$this->redirect)
		{
			$this->redirect = $this->referer;
		}

		if(!$account->authenticate($token))
        {
            throw new Exception("Coudln’t authenticate account.");
        }

		if (Craft::$app->getUser()->login($craftUser))
		{
			if ($registrationMode)
			{
				Craft::$app->getSession()->setNotice(Craft::t('app', 'Account created.'));
			}
			else
			{
				Craft::$app->getSession()->setNotice(Craft::t('app', 'Logged in.'));
			}

			return $this->redirect($this->redirect);
		}
		else
		{
			$errorCode = Social::$plugin->social_userSession->getLoginErrorCode();
			$errorMessage = Social::$plugin->social_userSession->getLoginErrorMessage($errorCode, $account->user->username);

			Craft::$app->getSession()->setError($errorMessage);

			return $this->redirect($this->referer);
		}
	}

	/**
	 * Clean session variables
	 *
	 * @return null
	 */
	private function _cleanSession()
	{
		Craft::$app->getSession()->remove('social.referer');
		// Craft::$app->getSession()->remove('social.requestUri');
	}
}
