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

class Social_UsersService extends BaseApplicationComponent
{
    // Public Methods
    // =========================================================================

	public function getUserById($id)
	{
		$record = Social_UserRecord::model()->findByPk($id);

		if ($record)
		{
			return Social_UserModel::populateModel($record);
		}
	}

	public function getUserByProvider($handle)
	{
		$currentUser = craft()->userSession->getUser();
		$userId = $currentUser->id;

		$conditions = 'provider=:provider and userId=:userId';
		$params = [':provider' => $handle, ':userId' => $userId];

		$record = Social_UserRecord::model()->find($conditions, $params);

		if ($record)
		{
			return Social_UserModel::populateModel($record);
		}
	}

	public function deleteUserByProvider($handle)
	{
		$currentUser = craft()->userSession->getUser();
		$userId = $currentUser->id;

		$conditions = 'provider=:provider and userId=:userId';
		$params = [':provider' => $handle, ':userId' => $userId];

		$record = Social_UserRecord::model()->find($conditions, $params);

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

	public function deleteUserByUserId($userId)
    {
        $conditions = 'userId=:userId';
        $params = array(':userId' => $userId);

        $socialUserRecords = Social_UserRecord::model()->findAll($conditions, $params);

        foreach($socialUserRecords as $socialUserRecord)
        {
            if($socialUserRecord->tokenId)
            {
                $tokenRecord = Oauth_TokenRecord::model()->findByPk($socialUserRecord->tokenId);

                if($tokenRecord)
                {
                    $tokenRecord->delete();
                }
            }

            $socialUserRecord->delete();
        }

        return true;
    }

	public function getUsers()
	{
		$conditions = '';
		$params = [];

		$records = Social_UserRecord::model()->findAll($conditions, $params);

		if ($records)
		{
			return Social_UserModel::populateModels($records);
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
	public function registerUser($attributes, $providerHandle)
	{
		$this->_fillAttributes($attributes, $providerHandle);

		$temporaryPassword = md5(time());

		$attributes['newPassword'] = $temporaryPassword;

		if (!empty($attributes['email']))
		{
			// find with email
			$user = craft()->users->getUserByUsernameOrEmail($attributes['email']);

			if (!$user)
			{
				$user = craft()->social_users->registerUser($attributes, $providerHandle);

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

			$user = craft()->social_users->registerUser($attributes, $providerHandle);

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

	public function getUserByUid($handle, $socialUid)
	{
		$conditions = 'provider=:provider';
		$params = [':provider' => $handle];

		$conditions .= ' AND socialUid=:socialUid';
		$params[':socialUid'] = $socialUid;

		$record = Social_UserRecord::model()->find($conditions, $params);

		if ($record)
		{
			return Social_UserModel::populateModel($record);
		}
	}

	public function saveUser(Social_UserModel $socialUser)
	{
		if ($socialUser->id)
		{
			$socialUserRecord = Social_UserRecord::model()->findById($socialUser->id);

			if (!$socialUserRecord)
			{
				throw new Exception(Craft::t('No social user exists with the ID “{id}”', ['id' => $socialUser->id]));
			}

			$oldSocialUser = Social_UserModel::populateModel($socialUserRecord);
			$isNewUser = false;
		}
		else
		{
			$socialUserRecord = new Social_UserRecord;
			$isNewUser = true;
		}

		// populate
		$socialUserRecord->userId = $socialUser->userId;
		$socialUserRecord->tokenId = $socialUser->tokenId;
		$socialUserRecord->provider = $socialUser->provider;
		$socialUserRecord->socialUid = $socialUser->socialUid;

		// validate
		$socialUserRecord->validate();

		$socialUser->addErrors($socialUserRecord->getErrors());

		if (!$socialUser->hasErrors())
		{
			$socialUserRecord->save(false);

			if (!$socialUser->id)
			{
				$socialUser->id = $socialUserRecord->id;
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	public function saveRemotePhoto($photoUrl, $user)
	{
		$filename = 'photo';

		$tempPath = craft()->path->getTempPath().'social/userphotos/'.$user->email.'/';
		IOHelper::createFolder($tempPath);
		$tempFilepath = $tempPath.$filename;
		$client = new \Guzzle\Http\Client();
		$response = $client->get($photoUrl)
			->setResponseBody($tempPath.$filename)
			->send();


		$extension = substr($response->getContentType(), strpos($response->getContentType(), "/") + 1);

		IOHelper::rename($tempPath.$filename, $tempPath.$filename.'.'.$extension);

		craft()->users->deleteUserPhoto($user);

		$image = craft()->images->loadImage($tempPath.$filename.'.'.$extension);
		$imageWidth = $image->getWidth();
		$imageHeight = $image->getHeight();

		$dimension = min($imageWidth, $imageHeight);
		$horizontalMargin = ($imageWidth - $dimension) / 2;
		$verticalMargin = ($imageHeight - $dimension) / 2;
		$image->crop($horizontalMargin, $imageWidth - $horizontalMargin, $verticalMargin, $imageHeight - $verticalMargin);

		craft()->users->saveUserPhoto($filename.'.'.$extension, $image, $user);

		IOHelper::deleteFile($tempPath.$filename.'.'.$extension);

		return true;
	}

	public function onBeforeRegister(Event $event)
	{
		$this->raiseEvent('onBeforeRegister', $event);
	}


    // Private Methods
    // =========================================================================

	private function _registerUser($account, $providerHandle)
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


			// save user

			craft()->users->saveUser($newUser);
			craft()->db->getSchema()->refresh();
			$user = craft()->users->getUserByUsernameOrEmail($usernameOrEmail);

			// save photo

			if (!empty($account['photo']))
			{
				craft()->social_users->saveRemotePhoto($account['photo'], $user);
			}


			// save profile attributes

			$profileFieldsMapping = craft()->config->get('profileFieldsMapping', 'social');

			if(isset($profileFieldsMapping[$providerHandle]))
			{
				$variables = $account;

				foreach($profileFieldsMapping[$providerHandle] as $field => $template)
				{
					$user->getContent()->{$field} = craft()->templates->renderString($template, $variables);
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

	/**
	 * Fill Attributes From Profile
	 *
	 * @param array $attributes Attributes we want to fill the profile with
	 * @param array $profile    The profile we want to fill attributes with
	 *
	 * @throws Exception
	 * @return null
	 */
	private function _fillAttributes(&$attributes, $providerHandle)
	{
		$socialProvider = craft()->social_providers->getProvider($providerHandle);
		$socialProvider->setToken($this->token);
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
