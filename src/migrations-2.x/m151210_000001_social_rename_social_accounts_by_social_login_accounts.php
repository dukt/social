<?php
namespace social\migrations;

use craft\db\Migration;
use craft\helpers\MigrationHelper;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m151210_000001_social_rename_social_accounts_by_social_login_accounts extends Migration
{
    /**
     * Any migration code in here is wrapped inside of a transaction.
     *
     * @return bool
     */
    public function safeUp()
    {
        if ($this->db->tableExists('{{%social_accounts}}')) {
            MigrationHelper::renameTable('{{%social_accounts}}', '{{%social_login_accounts}}', $this);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m151210_000001_social_rename_social_accounts_by_social_login_accounts cannot be reverted.\n";

        return false;
    }
}
