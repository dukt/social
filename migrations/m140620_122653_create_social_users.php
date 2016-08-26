<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m140620_122653_create_social_users extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		if (!craft()->db->tableExists('social_users'))
		{
			Craft::log('Creating the social_users table.', LogLevel::Info, true);

			// Create the craft_social_users table
			craft()->db->createCommand()->createTable('social_users', array(
				'userId'    => array('column' => 'integer', 'required' => true),
				'provider'  => array('required' => true),
				'socialUid' => array('required' => true),
				'tokenId'   => array('maxLength' => 11, 'decimals' => 0, 'required' => false, 'unsigned' => false, 'length' => 10, 'column' => 'integer'),
			), null, true);

			// Add indexes to craft_social_users
			craft()->db->createCommand()->createIndex('social_users', 'provider,socialUid', true);

			// Add foreign keys to craft_social_users
			craft()->db->createCommand()->addForeignKey('social_users', 'userId', 'users', 'id', 'CASCADE', null);
		}

		return true;
	}
}
