<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2015, Dukt
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
	 * Get account by provider handle
	 *
	 * @param string $providerHandle
	 *
	 * @return Social_AccountModel|null
	 */
	public function getAccountByProvider($providerHandle)
	{
		$currentUser = craft()->userSession->getUser();
		$userId = $currentUser->id;

		$conditions = 'providerHandle=:providerHandle and userId=:userId';
		$params = [':providerHandle' => $providerHandle, ':userId' => $userId];

		$record = Social_AccountRecord::model()->find($conditions, $params);

		if ($record)
		{
			return Social_AccountModel::populateModel($record);
		}
	}

	/**
	 * Get account by social UID
	 *
	 * @param string $providerHandle
	 * @param string $socialUid
	 *
	 * @return BaseModel
	 */
	public function getAccountByUid($providerHandle, $socialUid)
	{
		$conditions = 'providerHandle=:providerHandle';
		$params = [':providerHandle' => $providerHandle];

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
		$accountRecord->providerHandle = $account->providerHandle;
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

		// save token
		craft()->oauth->saveToken($token);
	}

	/**
	 * Delete account by provider
	 *
	 * @param $providerHandle
	 *
	 * @return bool
	 */
	public function deleteAccountByProvider($providerHandle)
	{
		$currentUser = craft()->userSession->getUser();
		$userId = $currentUser->id;

		$conditions = 'providerHandle=:providerHandle and userId=:userId';
		$params = [':providerHandle' => $providerHandle, ':userId' => $userId];

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
	 * @param string $providerHandle
	 * @param Oauth_TokenModel $token
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function registerUser($attributes, $providerHandle, $token)
	{
		if (!empty($attributes['email']))
		{
			// find user from email
			$user = craft()->users->getUserByUsernameOrEmail($attributes['email']);

			if (!$user)
			{
				$user = $this->_registerUser($attributes, $providerHandle, $token);
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
			throw new Exception("Email address not provided.");
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
	 * @param $providerHandle
	 * @param $token
	 *
	 * @return bool
	 * @throws Exception
	 */
	private function _registerUser($attributes, $providerHandle, $token)
	{
		// get social plugin settings

		$socialPlugin = craft()->plugins->getPlugin('social');
		$settings = $socialPlugin->getSettings();

		if (!$settings['allowSocialRegistration'])
		{
			throw new Exception("Social registration is disabled.");
		}


		// Fire an 'onBeforeRegister' event

		$event = new Event($this, [
			'account' => $attributes,
		]);

		$this->onBeforeRegister($event);

		if ($event->performAction)
		{
			$variables = $attributes;

			$newUser = new UserModel();
			$newUser->username = $attributes['email'];
			$newUser->email = $attributes['email'];


			if($settings['autoFillProfile'])
			{
				// fill user from attributes

				$userMapping = craft()->config->get('userMapping', 'social');

				if(is_array($userMapping))
				{
					foreach($userMapping as $attribute => $template)
					{
						$newUser->{$attribute} = craft()->templates->renderString($template, $variables);
					}
				}


				// fill user fields from attributes

				$userFieldsMapping = craft()->config->get('userFieldsMapping', 'social');

				if(isset($userFieldsMapping[$providerHandle]) && is_array($userFieldsMapping[$providerHandle]))
				{
					foreach($userFieldsMapping[$providerHandle] as $field => $template)
					{
						$newUser->getContent()->{$field} = craft()->templates->renderString($template, $variables);
					}
				}
			}


			// save user

			craft()->users->saveUser($newUser);
			craft()->db->getSchema()->refresh();
			$user = craft()->users->getUserByUsernameOrEmail($attributes['email']);


			// save remote photo

			if($settings['autoFillProfile'])
			{
				if (!empty($attributes['imageUrl']))
				{
					craft()->social->saveRemotePhoto($attributes['imageUrl'], $user);
				}
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
}
