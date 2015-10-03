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

	public function registerUser($account)
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
				$this->saveRemotePhoto($account['photo'], $user);
			}

			// save groups

			if (!empty($settings['defaultGroup']))
			{
				craft()->userGroups->assignUserToGroups($user->id, [$settings['defaultGroup']]);
			}

			return $user;
		}

		return false;
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
}
