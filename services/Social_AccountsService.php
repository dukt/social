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

class Social_AccountsService extends BaseApplicationComponent
{
    // Public Methods
    // =========================================================================

	/**
	 * Get accounts
	 *
	 * @return array
	 */
	public function getAccounts()
	{
		$conditions = '';
		$params = [];

		$records = Social_AccountRecord::model()->findAll($conditions, $params);

		if ($records)
		{
			return Social_AccountModel::populateModels($records);
		}
	}

	/**
	 * Get account by ID
	 *
	 * @param int $id
	 *
	 * @return Social_AccountModel|null
	 */
	public function getAccountById($id)
	{
		$record = Social_AccountRecord::model()->findByPk($id);

		if ($record)
		{
			return Social_AccountModel::populateModel($record);
		}
	}

	/**
	 * Get account by gateway handle
	 *
	 * @param string $gatewayHandle
	 *
	 * @return Social_AccountModel|null
	 */
	public function getAccountByGateway($gatewayHandle)
	{
		$currentUser = craft()->userSession->getUser();
		$userId = $currentUser->id;

		$conditions = 'gateway=:gateway and userId=:userId';
		$params = [':gateway' => $gatewayHandle, ':userId' => $userId];

		$record = Social_AccountRecord::model()->find($conditions, $params);

		if ($record)
		{
			return Social_AccountModel::populateModel($record);
		}
	}

	/**
	 * Get account by social UID
	 *
	 * @param string $gatewayHandle
	 * @param string $socialUid
	 *
	 * @return BaseModel
	 */
	public function getAccountByUid($gatewayHandle, $socialUid)
	{
		$conditions = 'gateway=:gateway';
		$params = [':gateway' => $gatewayHandle];

		$conditions .= ' AND socialUid=:socialUid';
		$params[':socialUid'] = $socialUid;

		$record = Social_AccountRecord::model()->find($conditions, $params);

		if ($record)
		{
			return Social_AccountModel::populateModel($record);
		}
	}

	/**
	 * Save Account
	 *
	 * @param Social_AccountModel $account
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function saveAccount(Social_AccountModel $account)
	{
		if ($account->id)
		{
			$accountRecord = Social_AccountRecord::model()->findById($account->id);

			if (!$accountRecord)
			{
				throw new Exception(Craft::t('No social user exists with the ID “{id}”', ['id' => $account->id]));
			}

			$oldSocialUser = Social_AccountModel::populateModel($accountRecord);
			$isNewUser = false;
		}
		else
		{
			$accountRecord = new Social_AccountRecord;
			$isNewUser = true;
		}

		// populate
		$accountRecord->userId = $account->userId;
		$accountRecord->tokenId = $account->tokenId;
		$accountRecord->gateway = $account->gateway;
		$accountRecord->socialUid = $account->socialUid;

		// validate
		$accountRecord->validate();

		$account->addErrors($accountRecord->getErrors());

		if (!$account->hasErrors())
		{
			$accountRecord->save(false);

			if (!$account->id)
			{
				$account->id = $accountRecord->id;
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Save Token
	 *
	 * @param Oauth_TokenModel $token
	 *
	 * @return null
	 */
	public function saveToken(Oauth_TokenModel $token)
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

		// onBeforeSaveToken
		$gateway = craft()->social_gateways->getGateway($token->providerHandle);
		$gateway->onBeforeSaveToken($token, $existingToken);

		// save token
		craft()->oauth->saveToken($token);
	}

	/**
	 * Delete account by provider
	 *
	 * @param $gatewayHandle
	 *
	 * @return bool
	 */
	public function deleteAccountByProvider($gatewayHandle)
	{
		$currentUser = craft()->userSession->getUser();
		$userId = $currentUser->id;

		$conditions = 'gateway=:gateway and userId=:userId';
		$params = [':gateway' => $gatewayHandle, ':userId' => $userId];

		$record = Social_AccountRecord::model()->find($conditions, $params);

		$tokenId = $record->tokenId;

		if ($tokenId)
		{
			$tokenRecord = Oauth_TokenRecord::model()->findByPk($tokenId);

			if ($tokenRecord)
			{
				$tokenRecord->delete();
			}
		}


		if ($record)
		{
			return $record->delete();
		}

		return false;
	}

	/**
	 * Delete account by user ID
	 *
	 * @param int $userId
	 *
	 * @return bool
	 */
	public function deleteAccountByUserId($userId)
    {
        $conditions = 'userId=:userId';
        $params = array(':userId' => $userId);

        $accountRecords = Social_AccountRecord::model()->findAll($conditions, $params);

        foreach($accountRecords as $accountRecord)
        {
            if($accountRecord->tokenId)
            {
                $tokenRecord = Oauth_TokenRecord::model()->findByPk($accountRecord->tokenId);

                if($tokenRecord)
                {
                    $tokenRecord->delete();
                }
            }

            $accountRecord->delete();
        }

        return true;
    }

	/**
	 * Register User
	 *
	 * @param array $attributes Attributes of the user we want to register
	 * @param string $gatewayHandle
	 * @param Oauth_TokenModel $token
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function registerUser($attributes, $gatewayHandle, $token)
	{
		$this->_fillAttributes($attributes, $gatewayHandle, $token);

		$temporaryPassword = md5(time());

		$attributes['newPassword'] = $temporaryPassword;

		if (!empty($attributes['email']))
		{
			// find with email
			$user = craft()->users->getUserByUsernameOrEmail($attributes['email']);

			if (!$user)
			{
				$user = $this->_registerUser($attributes, $gatewayHandle, $token);

				if ($user)
				{
					$socialUser = new Social_UserModel;
					$socialUser->userId = $user->id;
					$socialUser->hasEmail = true;
					$socialUser->hasPassword = false;
					$socialUser->temporaryPassword = $temporaryPassword;

					craft()->social_users->saveSocialUser($socialUser);
				}
			}
			else
			{
				if (craft()->config->get('allowEmailMatch', 'social') !== true)
				{
					throw new Exception("An account already exists with this email: ".$attributes['email']);
				}
			}
		}
		else
		{
			// no email at this point ? create a fake one

			$attributes['email'] = strtolower($gatewayHandle).'.'.$attributes['uid'].'@example.com';

			$user = $this->_registerUser($attributes, $gatewayHandle, $token);

			if ($user)
			{
				$socialUser = new Social_UserModel;
				$socialUser->userId = $user->id;
				$socialUser->hasEmail = false;
				$socialUser->hasPassword = false;
				$socialUser->temporaryEmail = $user->email;
				$socialUser->temporaryPassword = $temporaryPassword;

				craft()->social_users->saveSocialUser($socialUser);
			}
		}

		return $user;
	}

	/**
	 * Fires an 'onBeforeRegister' event.
	 *
	 * @param Event $event
	 *
	 * @return null
	 */
	public function onBeforeRegister(Event $event)
	{
		$this->raiseEvent('onBeforeRegister', $event);
	}

    // Private Methods
    // =========================================================================

	/**
	 * Register user
	 * @param $account
	 * @param $gatewayHandle
	 * @param $token
	 *
	 * @return bool
	 * @throws Exception
	 */
	private function _registerUser($account, $gatewayHandle, $token)
	{
		// get social plugin settings

		$socialPlugin = craft()->plugins->getPlugin('social');
		$settings = $socialPlugin->getSettings();

		if (!$settings['allowSocialRegistration'])
		{
			throw new Exception("Social registration is disabled.");
		}


		// new user

		if (isset($account['email']))
		{
			// define email
			$usernameOrEmail = $account['email'];
		}
		else
		{
			throw new Exception("Email address not provided.");
		}


		// Fire an 'onBeforeRegister' event

		$event = new Event($this, [
			'account' => $account,
		]);

		$this->onBeforeRegister($event);

		if ($event->performAction)
		{
			$newUser = new UserModel();
			$newUser->username = $usernameOrEmail;
			$newUser->email = $usernameOrEmail;

			if (!empty($account['firstName']))
			{
				$newUser->firstName = $account['firstName'];
			}

			if (!empty($account['lastName']))
			{
				$newUser->lastName = $account['lastName'];
			}

			$newUser->newPassword = $account['newPassword'];


			// save profile attributes

			$profileFieldsMapping = craft()->config->get('profileFieldsMapping', 'social');

			if(isset($profileFieldsMapping[$gatewayHandle]))
			{
				$variables = $account;

				foreach($profileFieldsMapping[$gatewayHandle] as $field => $template)
				{
					$newUser->getContent()->{$field} = craft()->templates->renderString($template, $variables);
				}
			}


			// save user

			craft()->users->saveUser($newUser);
			craft()->db->getSchema()->refresh();
			$user = craft()->users->getUserByUsernameOrEmail($usernameOrEmail);


			// save photo

			if (!empty($account['photo']))
			{
				craft()->social->saveRemotePhoto($account['photo'], $user);
			}


			// save groups

			if (!empty($settings['defaultGroup']))
			{
				craft()->userGroups->assignUserToGroups($user->id, [$settings['defaultGroup']]);
			}

			craft()->users->saveUser($user);

			return $user;
		}

		return false;
	}

	/**
	 * Fill Attributes
	 *
	 * @param $attributes
	 * @param $gatewayHandle
	 * @param $token
	 */
	private function _fillAttributes(&$attributes, $gatewayHandle, $token)
	{
		$socialProvider = craft()->social_gateways->getGateway($gatewayHandle);
		$socialProvider->setToken($token);
		$profile = $socialProvider->getProfile();

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
}
