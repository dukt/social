<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m151010_000002_rename_accounts_provider_column_by_gateway extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		Craft::log('Renaming social_accounts `provider` column by `gateway`', LogLevel::Info, true);

		MigrationHelper::renameColumn('social_accounts', 'provider', 'gateway');

		Craft::log('Done renaming social_accounts `provider` column by `gateway`', LogLevel::Info, true);

		return true;
	}
}
