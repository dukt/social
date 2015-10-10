<?php

/**
 * Social plugin for Craft
 *
 * @package   Craft Social
 * @author    Benjamin David
 * @copyright Copyright (c) 2015, Dukt
 * @link      https://dukt.net/craft/social/
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class SocialController extends BaseController
{
	// Properties
	// =========================================================================

	protected $allowAnonymous = ['actionLogin'];

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
		craft()->social->checkRequirements();


		// Referer

		$this->referer = craft()->httpSession->get('social.referer');

		if (!$this->referer)
		{
			$this->referer = craft()->request->getUrlReferrer();
			craft()->httpSession->add('social.referer', $this->referer);
		}


		// Redirect

		$this->redirect = craft()->request->getParam('redirect');


		// Connect or complete registration

		if (craft()->request->getPost('action') != 'social/completeRegistration')
		{
			$this->_connect();
		}
		else
		{
			$this->_completeRegistration();
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
		craft()->social->checkRequirements();

		$handle = craft()->request->getParam('gateway');

		// delete token and social user
		craft()->social_accounts->deleteAccountByProvider($handle);

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

        $this->redirect($_SERVER['HTTP_REFERER']);
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
		$gatewayHandle = craft()->request->getParam('gateway');
		$forcePrompt = craft()->request->getParam('forcePrompt');
		$requestUri = craft()->request->requestUri;
		$extraScopes = craft()->request->getParam('scopes');

		if (!$forcePrompt)
		{
			craft()->httpSession->add('social.requestUri', $requestUri);
		}

		// settings
		$plugin = craft()->plugins->getPlugin('social');
		$this->pluginSettings = $plugin->getSettings();


		// try to connect

		try
		{
			if (!$this->pluginSettings['allowSocialLogin'])
			{
				throw new Exception("Social login disabled");
			}

			if (craft()->getEdition() != Craft::Pro)
			{
				throw new Exception("Craft Pro is required");
			}

			// gateway scopes & params

			$scopes = craft()->social_gateways->getGatewayScopes($gatewayHandle);

			if ($extraScopes)
			{
				$extraScopes = unserialize(base64_decode(urldecode($extraScopes)));

				$scopes = array_merge($scopes, $extraScopes);
			}

			$params = craft()->social_gateways->getGatewayParams($gatewayHandle);

			if ($forcePrompt)
			{
				$params['approval_prompt'] = 'force';
			}

			if ($response = craft()->oauth->connect([
				'plugin'   => 'social',
				'provider' => $gatewayHandle,
				'scopes'   => $scopes,
				'params'   => $params
			]))
			{
				$this->_handleOAuthResponse($gatewayHandle, $response);
			}

			$this->_cleanSession();

			if (!$this->redirect)
			{
				$this->redirect = $this->referer;
			}

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
	 * @param string $gatewayHandle	Handle of the gateway
	 * @param string $response      Provider response as an array
	 *
	 * @throws Exception
	 * @return null
	 */
	private function _handleOAuthResponse($gatewayHandle, $response)
	{
		if ($response['success'])
		{
			$token = $response['token'];

			$this->token = $token;

			$plugin = craft()->plugins->getPlugin('social');

			if (!$this->pluginSettings['allowSocialLogin'])
			{
				throw new Exception("Social login disabled");
			}

			// OAuth Provider
			$this->oauthProvider = craft()->oauth->getProvider($gatewayHandle);
			$this->oauthProvider->setToken($this->token);

			// account

			if(method_exists($this->oauthProvider->getAccount(), 'getArrayCopy'))
			{
				$oauthProviderAccount = (array) $this->oauthProvider->getAccount()->getArrayCopy();
			}
			elseif(method_exists($this->oauthProvider->getAccount(), 'getIterator'))
			{
				$oauthProviderAccount = (array) $this->oauthProvider->getAccount()->getIterator();
			}
			else
			{
				throw Exception("Couldn’t get account");
			}

			// socialUid
			$this->socialUid = $oauthProviderAccount['uid'];

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
			throw new \Exception($response['errorMsg'], 1);
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
		$account = craft()->social_accounts->getAccountByUid($this->oauthProvider->getHandle(), $this->socialUid);

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
			$account->gateway = $this->oauthProvider->getHandle();
			$account->socialUid = $this->socialUid;
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
		$account = craft()->social_accounts->getAccountByUid($this->oauthProvider->getHandle(), $this->socialUid);

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
            // account

            if(method_exists($this->oauthProvider->getAccount(), 'getArrayCopy'))
            {
                $attributes = (array) $this->oauthProvider->getAccount()->getArrayCopy();
            }
            elseif(method_exists($this->oauthProvider->getAccount(), 'getIterator'))
            {
                $attributes = (array) $this->oauthProvider->getAccount()->getIterator();
            }
            else
            {
                throw Exception("Couldn’t get account");
            }

			if (empty($attributes['email']) && craft()->config->get('requireEmail', 'social'))
			{
				craft()->httpSession->add('social.token', OauthHelper::tokenToArray($this->token));
				craft()->httpSession->add('social.uid', $this->socialUid);
				craft()->httpSession->add('social.gatewayHandle', $this->oauthProvider->getHandle());

				$completeRegistrationTemplate = craft()->config->get('completeRegistrationTemplate', 'social');

				if (!empty($completeRegistrationTemplate))
				{
					if (!craft()->templates->doesTemplateExist($completeRegistrationTemplate))
					{
						throw new Exception("Complete registration template not set");
					}
				}
				else
				{
					throw new Exception("Complete registration template not set");
				}

				$this->renderTemplate($completeRegistrationTemplate);

				return;
			}
			else
			{
				$gatewayHandle	= $this->oauthProvider->getHandle();

				// register user
				$craftUser = craft()->social_accounts->registerUser($attributes, $gatewayHandle);

				if ($craftUser)
				{
					// save token
					craft()->social_accounts->saveToken($this->token);

					// save social user
					$account = new Social_AccountModel;
					$account->userId = $craftUser->id;
					$account->gateway = $this->oauthProvider->getHandle();
					$account->socialUid = $this->socialUid;
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
	}

	/**
	 * Complete Registration
	 *
	 * @return null
	 */
	private function _completeRegistration()
	{
		craft()->social->checkRequirements();

		// get session variables
		$token = OauthHelper::arrayToToken(craft()->httpSession->get('social.token'));
		$gatewayHandle = craft()->httpSession->get('social.gatewayHandle');
		$socialUid = crft()->httpSession->get('social.uid');

		// get post
		$email = craft()->request->getPost('email');

		// settings
		$plugin = craft()->plugins->getPlugin('social');
		$pluginSettings = $plugin->getSettings();

		// OAuth Provider
		$this->oauthProvider = craft()->oauth->getProvider($gatewayHandle);
		$this->oauthProvider->setToken($token)

		// account
		$oauthProviderAccount = $this->oauthProvider->getAccount();

		// attributes
		$attributes = [];
		$attributes['email'] = $email;

		$completeRegistrationTemplate = craft()->config->get('completeRegistrationTemplate', 'social');

		$completeRegistration = new Social_CompleteRegistrationModel;
		$completeRegistration->email = $email;

		$errorMessage = null;
		$variables = [];

		try
		{
			if ($completeRegistration->validate())
			{
				$emailExists = craft()->users->getUserByUsernameOrEmail($email);

				if (!$emailExists)
				{
					// register user
					$craftUser = craft()->social_accounts->registerUser($attributes, $gatewayHandle);
					if ($craftUser)
					{
						// save token
						craft()->social_accounts->saveToken($token);

						// save social user
						$account = new Social_AccountModel;
						$account->userId = $craftUser->id;
						$account->gateway = $gatewayHandle;
						$account->socialUid = $socialUid;
						$account->tokenId = $token->id;
						craft()->social_accounts->saveAccount($account);

						// login
						craft()->social_userSession->login($account->id);

						// redirect

						$this->_cleanSession();

						if (!$this->redirect)
						{
							$this->redirect = $this->referer;
						}

						$this->redirect($this->redirect);
					}
					else
					{
						throw new Exception("Craft user couldn’t be created.");
					}
				}
				else
				{
					$completeRegistration->addError('email', 'Email already in use by another user.');
				}
			}

			if (!empty($completeRegistrationTemplate))
			{
				if (!craft()->templates->doesTemplateExist($completeRegistrationTemplate))
				{
					throw new Exception("Complete registration template not set");
				}
			}
			else
			{
				throw new Exception("Complete registration template not set");
			}
		}
		catch (\Exception $e)
		{
			$variables['errorMessage'] = $e->getMessage();
		}

		$variables['completeRegistration'] = $completeRegistration;

		// Render template
		$this->renderTemplate($completeRegistrationTemplate, $variables);

		// Send the account back to the template
		craft()->urlManager->setRouteVariables($variables);
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
		craft()->httpSession->remove('social.gatewayHandle');
	}
}