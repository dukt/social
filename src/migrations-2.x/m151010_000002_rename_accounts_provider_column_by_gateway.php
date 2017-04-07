<?php
namespace dukt\social\migrations;

use craft\db\Migration;
use craft\helpers\MigrationHelper;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m151010_000002_rename_accounts_provider_column_by_gateway extends Migration
{
    /**
     * Any migration code in here is wrapped inside of a transaction.
     *
     * @return bool
     */
    public function safeUp()
    {
        if (!$this->db->columnExists('{{%social_accounts}}', 'gateway')) {
            MigrationHelper::renameColumn('{{%social_accounts}}', 'provider', 'gateway', $this);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m151010_000002_rename_accounts_provider_column_by_gateway cannot be reverted.\n";

        return false;
    }
}
