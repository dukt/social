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
    public $socialUid;
    public $username;
    public $email;
    public $firstName;
    public $lastName;
    public $lastLoginDate;

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

    public function socialUid($value)
    {
        $this->socialUid = $value;

        return $this;
    }

    public function username($value)
    {
        $this->username = $value;

        return $this;
    }

    public function email($value)
    {
        $this->email = $value;

        return $this;
    }

    public function firstName($value)
    {
        $this->firstName = $value;

        return $this;
    }

    public function lastName($value)
    {
        $this->lastName = $value;

        return $this;
    }

    public function lastLoginDate($value)
    {
        $this->lastLoginDate = $value;

        return $this;
    }

    protected function beforePrepare(): bool
    {
        // join in the products table

        /*$query->join('social_login_accounts login_accounts', 'login_accounts.id = elements.id');
        $query->leftJoin('users users', 'login_accounts.userId = users.id');*/

        $this->joinElementTable('social_login_accounts');
        $this->query->leftJoin('{{%users}} users', '[[social_login_accounts.userId]] = [[users.id]]');

        // select the userId column
        $this->query->select([
            'social_login_accounts.userId',
            'social_login_accounts.providerHandle',
            'social_login_accounts.socialUid',

            'users.username',
            'users.email',
            'users.firstName',
            'users.lastName',
            'users.lastLoginDate',
        ]);

        if ($this->userId) {
            $this->subQuery->andWhere(Db::parseParam('social_login_accounts.userId', $this->userId));
        }

        if ($this->providerHandle) {
            $this->subQuery->andWhere(Db::parseParam('social_login_accounts.providerHandle', $this->providerHandle));
        }

        if ($this->socialUid) {
            $this->subQuery->andWhere(Db::parseParam('social_login_accounts.socialUid', $this->socialUid));
        }

        if ($this->username) {
            $this->subQuery->andWhere(Db::parseParam('users.username', $this->username));
        }

        if ($this->email) {
            $this->subQuery->andWhere(Db::parseParam('users.email', $this->email));
        }

        if ($this->firstName) {
            $this->subQuery->andWhere(Db::parseParam('users.firstName', $this->firstName));
        }

        if ($this->lastName) {
            $this->subQuery->andWhere(Db::parseParam('users.lastName', $this->lastName));
        }

        if ($this->lastLoginDate) {
            $this->subQuery->andWhere(Db::parseParam('users.lastLoginDate', $this->lastLoginDate));
        }


        return parent::beforePrepare();
    }
}