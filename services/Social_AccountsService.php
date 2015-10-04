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
	/**
	 * @param $userId
	 *
	 * @return Social_AccountModel
	 */
	public function getAccountByUserId($userId)
	{
		$conditions = 'userId=:userId';
		$params = [':userId' => $userId];

		$record = Social_AccountRecord::model()->find($conditions, $params);

		if ($record)
		{
			return Social_AccountModel::populateModel($record);
		}
	}

	public function saveAccount(Social_AccountModel $account)
	{
		if ($account->id)
		{
			$accountRecord = Social_AccountRecord::model()->findById($account->id);

			if (!$accountRecord)
			{
				throw new Exception(Craft::t('No social account exists with the ID “{id}”', ['id' => $account->id]));
			}

			$oldSocialUser = Social_AccountModel::populateModel($accountRecord);
			$isNewAccount = false;
		}
		else
		{
			$accountRecord = new Social_AccountRecord;
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