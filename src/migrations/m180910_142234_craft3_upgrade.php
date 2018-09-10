<?php

namespace dukt\social\migrations;

use Craft;
use craft\db\Migration;
use dukt\social\elements\LoginAccount;

/**
 * m180910_142234_craft3_upgrade migration.
 */
class m180910_142234_craft3_upgrade extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update('{{%elements}}', [
            'type' => LoginAccount::class
        ], ['type' => 'Social_LoginAccount']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180910_142234_craft3_upgrade cannot be reverted.\n";
        return false;
    }
}
