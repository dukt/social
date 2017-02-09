<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160510_000001_social_make_elemental extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		Craft::log('Creating elements for all rows in the `social_login_accounts` table', LogLevel::Info, true);

		MigrationHelper::makeElemental('social_login_accounts', 'Social_LoginAccount');

		Craft::log('Done creating elements for all rows in the `social_login_accounts` table', LogLevel::Info, true);

		return true;
	}
}
