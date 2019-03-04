<?php

namespace dukt\social\migrations;

use craft\db\Migration;
use craft\helpers\MigrationHelper;

/**
 * m190304_031715_soft_delete_users migration.
 */
class m190304_031715_soft_delete_users extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Unique social UID should no longer be enforced by the DB to avoid conflicts with soft-deleted users.
        MigrationHelper::dropIndexIfExists('{{%social_login_accounts}}', ['providerHandle', 'socialUid'], true, $this);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190304_031715_soft_delete_users cannot be reverted.\n";
        return false;
    }
}
