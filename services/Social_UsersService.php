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

	/**
	 * Get social user from a Craft user ID
	 *
	 * @param $userId
	 *
	 * @return Social_UserModel
	 */
	public function getSocialUserByUserId($userId)
	{
		$conditions = 'userId=:userId';
		$params = [':userId' => $userId];

		$record = Social_UserRecord::model()->find($conditions, $params);

		if ($record)
		{
			return Social_UserModel::populateModel($record);
		}
	}

	/**
	 * Save social user
	 *
	 * @param Social_UserModel $account
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function saveSocialUser(Social_UserModel $account)
	{
		if ($account->id)
		{
			$accountRecord = Social_UserRecord::model()->findById($account->id);

			if (!$accountRecord)
			{
				throw new Exception(Craft::t('No social account exists with the ID “{id}”', ['id' => $account->id]));
			}

			$oldSocialUser = Social_UserModel::populateModel($accountRecord);
			$isNewAccount = false;
		}
		else
		{
			$accountRecord = new Social_UserRecord;
			$isNewAccount = true;
		}

		// populate
		$accountRecord->userId = $account->userId;
		$accountRecord->hasEmail = $account->hasEmail;
		$accountRecord->hasPassword = $account->hasPassword;
		$accountRecord->temporaryEmail = $account->temporaryEmail;
		$accountRecord->temporaryPassword = $account->temporaryPassword;

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
}
