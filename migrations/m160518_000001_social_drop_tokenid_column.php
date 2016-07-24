<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160518_000001_social_drop_tokenid_column extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		Craft::log('Removing the `tokenId` column from the `social_login_accounts` table', LogLevel::Info, true);

		craft()->db->createCommand()->dropColumn('social_login_accounts', 'tokenId');

		Craft::log('Done removing the `tokenId` column from the `social_login_accounts` table', LogLevel::Info, true);

		return true;
	}
}
