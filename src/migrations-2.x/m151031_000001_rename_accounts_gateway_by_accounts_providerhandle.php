<?php
namespace dukt\social\migrations;

use craft\db\Migration;
use craft\helpers\MigrationHelper;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m151031_000001_rename_accounts_gateway_by_accounts_providerhandle extends Migration
{
    /**
     * Any migration code in here is wrapped inside of a transaction.
     *
     * @return bool
     */
    public function safeUp()
    {
        if (!$this->db->columnExists('{{%social_accounts}}', 'providerHandle')) {
            MigrationHelper::renameColumn('{{%social_accounts}}', 'gateway', 'providerHandle', $this);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m151031_000001_rename_accounts_gateway_by_accounts_providerhandle cannot be reverted.\n";

        return false;
    }
}
