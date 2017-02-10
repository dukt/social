<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m141106_220045_social_add_accounts_table extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		if (!craft()->db->tableExists('social_accounts'))
		{
			// Create the craft_social_accounts table
			craft()->db->createCommand()->createTable('social_accounts', array(
				'userId'            => array('column' => 'integer', 'required' => true),
				'hasEmail'          => array('maxLength' => 1, 'default' => false, 'required' => true, 'column' => 'tinyint', 'unsigned' => true),
				'hasPassword'       => array('maxLength' => 1, 'default' => false, 'required' => true, 'column' => 'tinyint', 'unsigned' => true),
				'temporaryEmail'    => array('required' => false),
				'temporaryPassword' => array('required' => true),
			), null, true);

			// Add indexes to craft_social_accounts
			craft()->db->createCommand()->createIndex('social_accounts', 'userId', true);

			// Add foreign keys to craft_social_accounts
			craft()->db->createCommand()->addForeignKey('social_accounts', 'userId', 'users', 'id', 'CASCADE', null);
		}

		return true;
	}
}
