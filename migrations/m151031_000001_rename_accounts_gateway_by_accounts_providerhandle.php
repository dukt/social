<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m151031_000001_rename_accounts_gateway_by_accounts_providerhandle extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		Craft::log('Renaming social_accounts `gateway` column by `providerHandle`', LogLevel::Info, true);

		MigrationHelper::renameColumn('social_accounts', 'gateway', 'providerHandle');

		Craft::log('Done renaming social_accounts `gateway` column by `providerHandle`', LogLevel::Info, true);

		return true;
	}
}
