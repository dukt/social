<?php
namespace dukt\social\migrations;

use craft\db\Migration;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160518_000001_social_drop_tokenid_column extends Migration
{
    /**
     * Any migration code in here is wrapped inside of a transaction.
     *
     * @return bool
     */
    public function safeUp()
    {
        $this->dropColumn('{{%social_login_accounts}}', 'tokenId');

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m160510_000001_social_make_elemental cannot be reverted.\n";

        return false;
    }
}
