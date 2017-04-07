<?php
namespace dukt\social\migrations;

use craft\db\Migration;
use craft\helpers\MigrationHelper;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m151010_000001_interchange_social_users_and_accounts_tables extends Migration
{
    /**
     * Any migration code in here is wrapped inside of a transaction.
     *
     * @return bool
     */
    public function safeUp()
    {
        if ($this->db->tableExists('{{%social_users}}')) {
            MigrationHelper::renameTable('{{%social_users}}', '{{%social_accounts_temp}}', $this);
        }

        if ($this->db->tableExists('{{%social_accounts}}')) {
            MigrationHelper::renameTable('{{%social_accounts}}', '{{%social_users}}', $this);
        }

        if ($this->db->tableExists('{{%social_accounts_temp}}')) {
            MigrationHelper::renameTable('{{%social_accounts_temp}}', '{{%social_accounts}}', $this);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m151010_000001_interchange_social_users_and_accounts_tables cannot be reverted.\n";

        return false;
    }
}
