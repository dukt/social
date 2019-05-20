<?php

namespace dukt\social\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use dukt\social\elements\LoginAccount;

/**
 * m190519_185640_soft_delete_login_accounts migration.
 */
class m190519_185640_soft_delete_login_accounts extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // List login accounts from trashed users
        $results = (new Query())
            ->select(['social_login_accounts.id', 'social_login_accounts.userId'])
            ->from(['{{%social_login_accounts}} social_login_accounts'])
            ->leftJoin('{{%elements}} userElements', '[[userElements.id]] = [[social_login_accounts.userId]]')
            ->leftJoin('{{%elements}} loginAccountElements', '[[loginAccountElements.id]] = [[social_login_accounts.userId]]')
            ->where('userElements.dateDeleted IS NOT NULL')
            ->all();

        // Set each login account element as trashed
        $date = DateTimeHelper::currentUTCDateTime();
        $dbDate = Db::prepareDateForDb($date);

        foreach ($results as $result) {
            $this->update('{{%elements}}', [
                'dateDeleted' => $dbDate
            ], ['id' => $result['id']]);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190519_185640_soft_delete_login_accounts cannot be reverted.\n";
        return false;
    }
}
