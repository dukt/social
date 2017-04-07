<?php
namespace dukt\social\migrations;

use craft\db\Migration;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m140620_122653_create_social_users extends Migration
{
    /**
     * Any migration code in here is wrapped inside of a transaction.
     *
     * @return bool
     */
    public function safeUp()
    {
        $this->createTable('{{%social_users}}', [
            'id' => $this->primaryKey(),
            'userId' => $this->integer()->notNull(),
            'provider' => $this->string()->notNull(),
            'socialUid' => $this->string()->notNull(),
            'tokenId' => $this->integer(),

            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        // Add indexes to craft_social_users
        $this->createIndex($this->db->getIndexName('{{%social_users}}', 'provider,socialUid', true), '{{%social_users}}', 'provider,socialUid', true);

        // Add foreign keys to craft_social_users
        $this->addForeignKey($this->db->getForeignKeyName('{{%social_users}}', 'userId'), '{{%social_users}}', 'userId', '{{%users}}', 'id', 'CASCADE', null);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m140620_122653_create_social_users cannot be reverted.\n";

        return false;
    }
}
