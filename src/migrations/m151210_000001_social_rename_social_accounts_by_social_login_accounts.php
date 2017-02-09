<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m151210_000001_social_rename_social_accounts_by_social_login_accounts extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		Craft::log('Renaming `social_accounts` table to `social_login_accounts`', LogLevel::Info, true);

		MigrationHelper::renameTable('social_accounts', 'social_login_accounts');

		Craft::log('Done renaming `social_accounts` table to `social_login_accounts`', LogLevel::Info, true);

		return true;
	}
}
