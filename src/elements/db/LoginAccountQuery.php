<?php
namespace dukt\social\elements\db;

use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use dukt\social\elements\LoginAccount;

class LoginAccountQuery extends ElementQuery
{
    public $userId;
    public $providerHandle;

    public function userId($value)
    {
        $this->userId = $value;

        return $this;
    }

    public function providerHandle($value)
    {
        $this->providerHandle = $value;

        return $this;
    }

    protected function beforePrepare(): bool
    {
        // join in the products table
        $this->joinElementTable('social_login_accounts');

        // select the userId column
        $this->query->select([
            'social_login_accounts.userId',
            'social_login_accounts.providerHandle',
        ]);

        if ($this->userId) {
            $this->subQuery->andWhere(Db::parseParam('social_login_accounts.userId', $this->userId));
        }

        if ($this->providerHandle) {
            $this->subQuery->andWhere(Db::parseParam('social_login_accounts.providerHandle', $this->providerHandle));
        }

        return parent::beforePrepare();
    }
}