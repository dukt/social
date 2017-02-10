<?php
/**
 * OAuth plugin for Craft CMS 3.x
 *
 * Consume OAuth-based web services.
 *
 * @link      https://dukt.net/
 * @copyright Copyright (c) 2017 Dukt
 */

namespace dukt\social\migrations;

use Craft;
use craft\db\Migration;
use craft\services\Config;

/**
 * OAuth Install Migration
 *
 * If your plugin needs to create any custom database tables when it gets installed,
 * create a migrations/ folder within your plugin folder, and save an Install.php file
 * within it using the following template:
 *
 * If you need to perform any additional actions on install/uninstall, override the
 * safeUp() and safeDown() methods.
 *
 * @author    Dukt
 * @package   OAuth
 * @since     3.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * This method contains the logic to be executed when applying this migration.
     * This method differs from [[up()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[up()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->get('driver', Config::CATEGORY_DB);
        $this->createTables();
        $this->createIndexes();
        $this->addForeignKeys();
        $this->insertDefaultData();

        return true;
    }

    /**
     * This method contains the logic to be executed when removing this migration.
     * This method differs from [[down()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[down()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->get('driver', Config::CATEGORY_DB);
        $this->removeIndexes();
        $this->removeTables();
        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function createTables()
    {
        $this->createTable(
            '{{%social_login_accounts}}',
            [
                'id' => $this->integer()->notNull(),
                'userId' => $this->integer()->notNull(),
                'providerHandle' => $this->string(255)->notNull(),
                'socialUid' => $this->string(255)->notNull(),

                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
                'PRIMARY KEY(id)',
            ]
        );
    }

    /**
     * Creates the indexes needed for the Records used by the plugin
     *
     * @return void
     */
    protected function createIndexes()
    {
        $this->createIndex($this->db->getIndexName('{{%social_login_accounts}}', 'providerHandle,socialUid', true), '{{%social_login_accounts}}', 'providerHandle,socialUid', true);
    }

    /**
     * Creates the foreign keys needed for the Records used by the plugin
     *
     * @return void
     */
    protected function addForeignKeys()
    {
        $this->addForeignKey($this->db->getForeignKeyName('{{%social_login_accounts}}', 'id'), '{{%social_login_accounts}}', 'id', '{{%elements}}', 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName('{{%social_login_accounts}}', 'userId'), '{{%social_login_accounts}}', 'userId', '{{%users}}', 'id', 'CASCADE', null);
    }

    /**
     * Populates the DB with the default data.
     *
     * @return void
     */
    protected function insertDefaultData()
    {
    }

    /**
     * Removes the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeTables()
    {
        $this->dropTable('{{%social_login_accounts}}');
    }

    /**
     * Removes the indexes needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeIndexes()
    {
        $this->dropIndex($this->db->getIndexName('{{%social_login_accounts}}', 'providerHandle,socialUid', true), '{{%social_login_accounts}}');
    }
}
