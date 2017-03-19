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
        echo 'Renaming social_accounts `provider` column by `gateway`';

        MigrationHelper::renameColumn('social_accounts', 'provider', 'gateway');

        echo 'Done renaming social_accounts `provider` column by `gateway`';

        return true;
    }
}
