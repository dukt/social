<?php
namespace dukt\social\migrations;

use craft\db\Migration;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m141106_220045_social_add_accounts_table extends Migration
{
    /**
     * Any migration code in here is wrapped inside of a transaction.
     *
     * @return bool
     */
    public function safeUp()
    {
        $this->createTable('{{%social_accounts}}', [
            'id' => $this->primaryKey(),
            'userId' => $this->integer()->notNull(),
            'hasEmail' => $this->boolean()->notNull()->defaultValue(false),
            'hasPassword' => $this->boolean()->notNull()->defaultValue(false),
            'temporaryEmail' => $this->string(),
            'temporaryPassword' => $this->string()->notNull(),

            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);


        // Add indexes to craft_social_users
        $this->createIndex($this->db->getIndexName('{{%social_accounts}}', 'userId', true), '{{%social_accounts}}', 'userId', true);

        // Add foreign keys to craft_social_accounts
        $this->addForeignKey($this->db->getForeignKeyName('{{%social_accounts}}', 'userId'), '{{%social_accounts}}', 'userId', '{{%users}}', 'id', 'CASCADE', null);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m141106_220045_social_add_accounts_table cannot be reverted.\n";

        return false;
    }
}
