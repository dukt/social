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

use Guzzle\Http\Client;

class SocialController extends BaseController
{
	// Properties
	// =========================================================================

	protected $allowAnonymous = ['actionLogin'];

	private $provider;
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

		// referer

		$this->referer = craft()->httpSession->get('social.referer');

		if (!$this->referer)
		{
			$this->referer = craft()->request->getUrlReferrer();
			craft()->httpSession->add('social.referer', $this->referer);
		}

		// redirect
		$this->redirect = craft()->request->getParam('redirect');

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
	 * Connect
	 *
	 * @return null
	 */
	public function actionLink()
	{
		$this->actionLogin();
	}

	/**
	 * Disconnect
	 *
	 * @return null
	 */
	public function actionUnlink()
	{
		craft()->social->checkRequirements();

		$handle = craft()->request->getParam('provider');

		// delete token and social user
		craft()->social_users->deleteUserByProvider($handle);

		// redirect
		$redirect = craft()->request->getUrlReferrer();
		$this->redirect($redirect);
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

			// provider scopes & params

			$scopes = craft()->social_providers->getProviderScopes($providerHandle);

			if ($extraScopes)
			{
				$extraScopes = unserialize(base64_decode(urldecode($extraScopes)));

				$scopes = array_merge($scopes, $extraScopes);
			}

			$params = craft()->social_providers->getProviderParams($providerHandle);

			if ($forcePrompt)
			{
				$params['approval_prompt'] = 'force';
			}

			if ($response = craft()->oauth->connect([
				'plugin'   => 'social',
				'provider' => $providerHandle,
				'scopes'   => $scopes,
				'params'   => $params
			]))
			{
				$this->_handleConnectResponse($providerHandle, $response);
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

	/**
	 * Handle Connect Response
	 *
	 * @param string $providerHandle Handle of the provider
	 * @param string $response       Provider response as an array
	 *
	 * @throws Exception
	 * @return null
	 */
	private function _handleConnectResponse($providerHandle, $response)
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

			// provider
			$this->provider = craft()->oauth->getProvider($providerHandle);
			$this->provider->setToken($this->token);

			// account

			if(method_exists($this->provider->getAccount(), 'getArrayCopy'))
			{
				$account = (array) $this->provider->getAccount()->getArrayCopy();
			}
			elseif(method_exists($this->provider->getAccount(), 'getIterator'))
			{
				$account = (array) $this->provider->getAccount()->getIterator();
			}
			else
			{
				throw Exception("Couldn’t get account");
			}

			// socialUid
			$this->socialUid = $account['uid'];

			// user
			$craftUser = craft()->userSession->getUser();

			if ($craftUser)
			{
				$this->_handleLoggedInUser($craftUser);
			}
			else
			{
				$this->_handleGuestUser();
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
	private function _handleLoggedInUser($craftUser)
	{
		$socialUser = craft()->social_users->getUserByUid($this->provider->getHandle(), $this->socialUid);

		if ($socialUser)
		{
			if ($craftUser->id == $socialUser->userId)
			{
				// save token

				$tokenId = $socialUser->tokenId;
				$existingToken = craft()->oauth->getTokenById($tokenId);

				if ($existingToken)
				{
					$this->token->id = $existingToken->id;
				}

				$this->_saveToken($this->token);

				// save user
				$socialUser->tokenId = $this->token->id;
				craft()->social_users->saveUser($socialUser);
			}
			else
			{
				throw new Exception("UID is already associated with another user. Disconnect from your current session and retry.");
			}
		}
		else
		{
			// save token

			$this->_saveToken($this->token);

			// save social user
			$socialUser = new Social_UserModel;
			$socialUser->userId = $craftUser->id;
			$socialUser->provider = $this->provider->getHandle();
			$socialUser->socialUid = $this->socialUid;
			$socialUser->tokenId = $this->token->id;
			craft()->social_users->saveUser($socialUser);
		}
	}

	/**
	 * Handle Guest User
	 *
	 * @return null
	 */
	private function _handleGuestUser()
	{
		$socialUser = craft()->social_users->getUserByUid($this->provider->getHandle(), $this->socialUid);

		if ($socialUser)
		{
			$craftUser = craft()->users->getUserById($socialUser->userId);

			if ($craftUser)
			{
				// existing token
				if (!empty($socialUser->tokenId))
				{
					$this->token->id = $socialUser->tokenId;
				}

				// save token
				$this->_saveToken($this->token);

				// save user
				$socialUser->tokenId = $this->token->id;
				craft()->social_users->saveUser($socialUser);

				// login
				craft()->social_userSession->login($socialUser->id);
			}
			else
			{
				throw new Exception("Social User exists but craft user doesn't");
			}
		}
		else
		{
            // account

            if(method_exists($this->provider->getAccount(), 'getArrayCopy'))
            {
                $attributes = (array) $this->provider->getAccount()->getArrayCopy();
            }
            elseif(method_exists($this->provider->getAccount(), 'getIterator'))
            {
                $attributes = (array) $this->provider->getAccount()->getIterator();
            }
            else
            {
                throw Exception("Couldn’t get account");
            }

			if (empty($attributes['email']) && craft()->config->get('requireEmailAddress', 'social'))
			{
				craft()->httpSession->add('social.token', OauthHelper::tokenToArray($this->token));
				craft()->httpSession->add('social.uid', $this->socialUid);
				craft()->httpSession->add('social.providerHandle', $this->provider->getHandle());

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
				// get profile

				$socialProvider = craft()->social_providers->getProvider($this->provider->getClass());
				$socialProvider->setToken($this->token);
				$profile = $socialProvider->getProfile();

				// fill attributes from profile
				$this->_fillAttributesFromProfile($attributes, $profile);

				// register user
				$craftUser = $this->_registerUser($attributes);

				if ($craftUser)
				{
					// save token
					$this->_saveToken($this->token);

					// save social user
					$socialUser = new Social_UserModel;
					$socialUser->userId = $craftUser->id;
					$socialUser->provider = $this->provider->getHandle();
					$socialUser->socialUid = $this->socialUid;
					$socialUser->tokenId = $this->token->id;
					craft()->social_users->saveUser($socialUser);

					// login
					craft()->social_userSession->login($socialUser->id);
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
		$providerHandle = craft()->httpSession->get('social.providerHandle');
		$socialUid = craft()->httpSession->get('social.uid');

		// get post
		$email = craft()->request->getPost('email');

		// settings
		$plugin = craft()->plugins->getPlugin('social');
		$pluginSettings = $plugin->getSettings();

		// provider
		$this->provider = craft()->oauth->getProvider($providerHandle);
		$this->provider->setToken($token);

		// account
		$account = $this->provider->getAccount();

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
					// get profile
					$socialProvider = craft()->social_providers->getProvider($providerHandle);
					$socialProvider->setToken($token);
					$profile = $socialProvider->getProfile();

					// fill attributes from profile
					$this->_fillAttributesFromProfile($attributes, $profile);

					// register user
					$craftUser = $this->_registerUser($attributes);

					if ($craftUser)
					{
						// save token
						$this->_saveToken($token);

						// save social user
						$socialUser = new Social_UserModel;
						$socialUser->userId = $craftUser->id;
						$socialUser->provider = $providerHandle;
						$socialUser->socialUid = $socialUid;
						$socialUser->tokenId = $token->id;
						craft()->social_users->saveUser($socialUser);

						// login
						craft()->social_userSession->login($socialUser->id);

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
	 * Fill Attributes From Profile
	 *
	 * @param array $attributes Attributes we want to fill the profile with
	 * @param array $profile    The profile we want to fill attributes with
	 *
	 * @throws Exception
	 * @return null
	 */
	private function _fillAttributesFromProfile(&$attributes, $profile)
	{
		$plugin = craft()->plugins->getPlugin('social');
		$settings = $plugin->getSettings();

		if ($settings->autoFillProfile)
		{
			if (!empty($profile['firstName']))
			{
				$attributes['firstName'] = $profile['firstName'];
			}

			if (!empty($profile['lastName']))
			{
				$attributes['lastName'] = $profile['lastName'];
			}

			if (!empty($profile['photo']))
			{
				$attributes['photo'] = $profile['photo'];
			}
		}
	}

	/**
	 * Register User
	 *
	 * @param array $attributes Attributes of the user we want to register
	 *
	 * @throws Exception
	 * @return null
	 */
	private function _registerUser($attributes)
	{
		$temporaryPassword = md5(time());

		$attributes['newPassword'] = $temporaryPassword;

		if (!empty($attributes['email']))
		{
			// find with email
			$user = craft()->users->getUserByUsernameOrEmail($attributes['email']);

			if (!$user)
			{
				$user = craft()->social_users->registerUser($attributes);

				if ($user)
				{
					$socialAccount = new Social_AccountModel;
					$socialAccount->userId = $user->id;
					$socialAccount->hasEmail = true;
					$socialAccount->hasPassword = false;
					$socialAccount->temporaryPassword = $temporaryPassword;

					craft()->social_accounts->saveAccount($socialAccount);
				}
			}
			else
			{
				if (craft()->config->get('allowEmailMatch', 'social') !== true)
				{
					throw new \Exception("An account already exists with this email: ".$attributes['email']);
				}
			}
		}
		else
		{
			// no email at this point ? create a fake one

			$providerHandle = $this->provider->getHandle();

			$attributes['email'] = strtolower($providerHandle).'.'.$attributes['uid'].'@example.com';

			$user = craft()->social_users->registerUser($attributes);

			if ($user)
			{
				$socialAccount = new Social_AccountModel;
				$socialAccount->userId = $user->id;
				$socialAccount->hasEmail = false;
				$socialAccount->hasPassword = false;
				$socialAccount->temporaryEmail = $user->email;
				$socialAccount->temporaryPassword = $temporaryPassword;

				craft()->social_accounts->saveAccount($socialAccount);
			}
		}

		return $user;
	}

	/**
	 * Save Token
	 *
	 * @param object $tokenModel The token object we want to save
	 *
	 * @return null
	 */
	private function _saveToken(Oauth_TokenModel $token)
	{
		$existingToken = null;

		if ($token->id)
		{
			$existingToken = craft()->oauth->getTokenById($token->id);

			if (!$existingToken)
			{
				$existingToken = null;
				$token->id = null;
			}
		}

		if ($token->providerHandle == 'google')
		{
			if (empty($token->refreshToken))
			{
				if ($existingToken)
				{
					if (!empty($existingToken->refreshToken))
					{
						// existing token has a refresh token so we keep it
						$token->refreshToken = $existingToken->refreshToken;
					}
				}


				// still no refresh token ? re-prompt

				if (empty($token->refreshToken))
				{
					$requestUri = craft()->httpSession->get('social.requestUri');
					$this->redirect($requestUri.'&forcePrompt=true');
				}
			}
		}

		// save token
		craft()->oauth->saveToken($token);
	}

}