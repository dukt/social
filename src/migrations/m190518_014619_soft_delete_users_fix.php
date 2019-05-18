<?php

namespace dukt\social\migrations;

use craft\db\Migration;
use craft\helpers\MigrationHelper;

/**
 * m190518_014619_soft_delete_users_fix migration.
 */
class m190518_014619_soft_delete_users_fix extends Migration
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
        echo "m190518_014619_soft_delete_users_fix cannot be reverted.\n";
        return false;
    }
}
