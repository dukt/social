<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class SocialController extends BaseController
{
	// Properties
	// =========================================================================

	protected $allowAnonymous = ['actionLogin'];

	private $oauthProviderHandle;
	private $oauthProvider;
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
	public function actionLink()
	{
		$this->actionLogin();
	}

	/**
	 * Unlink Account
	 *
	 * @return null
	 */
	public function actionUnlink()
	{
		craft()->social_plugin->checkRequirements();

		$handle = craft()->request->getParam('provider');

		// delete token and social user
		craft()->social_accounts->deleteAccountByProvider($handle);

		craft()->userSession->setNotice(Craft::t('Account unlinked.'));

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
		$requestUri = craft()->request->requestUri;
		$extraScopes = craft()->request->getParam('scope');
		craft()->httpSession->add('social.requestUri', $requestUri);

		// settings
		$plugin = craft()->plugins->getPlugin('social');
		$this->pluginSettings = $plugin->getSettings();


		// try to connect

		try
		{
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

			$this->_cleanSession();

			if (!$this->redirect)
			{
				$this->redirect = $this->referer;
			}

			craft()->userSession->setNotice(Craft::t('Account linked.'));

			$this->redirect($this->redirect);
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
		$this->oauthProviderHandle = $providerHandle;
		$this->oauthProvider = craft()->oauth->getProvider($this->oauthProviderHandle);

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
		$remoteAccount = $this->oauthProvider->getAccount($this->token);

		$socialUid = $remoteAccount['uid'];

		$account = craft()->social_accounts->getAccountByUid($this->oauthProviderHandle, $socialUid);

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

				craft()->social_accounts->saveToken($this->token);

				// save user
				$account->tokenId = $this->token->id;
				craft()->social_accounts->saveAccount($account);
			}
			else
			{
				throw new Exception("This UID is already associated with another user. Disconnect from your current session and retry.");
			}
		}
		else
		{
			// save token
			craft()->social_accounts->saveToken($this->token);

			// save social user
			$account = new Social_AccountModel;
			$account->userId = $craftUser->id;
			$account->providerHandle = $this->oauthProviderHandle;
			$account->socialUid = $socialUid;
			$account->tokenId = $this->token->id;

			craft()->social_accounts->saveAccount($account);
		}
	}

	/**
	 * Handle Guest User
	 *
	 * @return null
	 */
	private function _login()
	{
		$attributes = $this->oauthProvider->getAccount($this->token);
		$socialUid = $attributes['uid'];

		$account = craft()->social_accounts->getAccountByUid($this->oauthProviderHandle, $socialUid);

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
				craft()->social_accounts->saveToken($this->token);

				// save user
				$account->tokenId = $this->token->id;
				craft()->social_accounts->saveAccount($account);

				// login
				craft()->social_userSession->login($account->id);
			}
			else
			{
				throw new Exception("Social account exists but Craft user doesn't");
			}
		}
		else
		{
			// register user
			$craftUser = craft()->social_accounts->registerUser($attributes, $this->oauthProviderHandle, $this->token);

			if ($craftUser)
			{
				// save token
				craft()->social_accounts->saveToken($this->token);

				// save social user
				$account = new Social_AccountModel;
				$account->userId = $craftUser->id;
				$account->providerHandle = $this->oauthProviderHandle;
				$account->socialUid = $socialUid;
				$account->tokenId = $this->token->id;
				craft()->social_accounts->saveAccount($account);

				// login
				craft()->social_userSession->login($account->id);
			}
			else
			{
				throw new Exception("Craft user couldn’t be created.");
			}
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