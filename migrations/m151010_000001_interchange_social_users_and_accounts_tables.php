<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m151010_000001_interchange_social_users_and_accounts_tables extends BaseMigration
{
    /**
     * Any migration code in here is wrapped inside of a transaction.
     *
     * @return bool
     */
    public function safeUp()
    {
        Craft::log('Interchanging social_users and social_accounts table names', LogLevel::Info, true);

        $this->renameTable('social_users', 'social_accounts_temp');
        $this->renameTable('social_accounts', 'social_users');
        $this->renameTable('social_accounts_temp', 'social_accounts');

        Craft::log('Done interchanging social_users and social_accounts table names', LogLevel::Info, true);

        return true;
    }
}
