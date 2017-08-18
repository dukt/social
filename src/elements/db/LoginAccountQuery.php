<?php
namespace dukt\social\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class LoginAccountQuery extends ElementQuery
{
    // Properties
    // =========================================================================

    /**
     * @var
     */
    public $userId;
    /**
     * @var
     */
    public $providerHandle;
    /**
     * @var
     */
    public $socialUid;
    /**
     * @var
     */
    public $username;
    /**
     * @var
     */
    public $email;
    /**
     * @var
     */
    public $firstName;
    /**
     * @var
     */
    public $lastName;
    /**
     * @var
     */
    public $lastLoginDate;

    // Public Methods
    // =========================================================================

    /**
     * Sets the [[userId]] property.
     *
     * @param int|int[]|null $value The property value
     *
     * @return static self reference
     */
    public function userId($value)
    {
        $this->userId = $value;

        return $this;
    }

    /**
     * Sets the [[providerHandle]] property.
     *
     * @param string|string[]|null $value The property value
     *
     * @return static self reference
     */
    public function providerHandle($value)
    {
        $this->providerHandle = $value;

        return $this;
    }

    /**
     * Sets the [[socialUid]] property.
     *
     * @param string|string[]|null $value The property value
     *
     * @return static self reference
     */
    public function socialUid($value)
    {
        $this->socialUid = $value;

        return $this;
    }

    /**
     * Sets the [[username]] property.
     *
     * @param string|string[]|null $value The property value
     *
     * @return static self reference
     */
    public function username($value)
    {
        $this->username = $value;

        return $this;
    }

    /**
     * Sets the [[email]] property.
     *
     * @param string|string[]|null $value The property value
     *
     * @return static self reference
     */
    public function email($value)
    {
        $this->email = $value;

        return $this;
    }

    /**
     * Sets the [[firstName]] property.
     *
     * @param string|string[]|null $value The property value
     *
     * @return static self reference
     */
    public function firstName($value)
    {
        $this->firstName = $value;

        return $this;
    }

    /**
     * Sets the [[lastName]] property.
     *
     * @param string|string[]|null $value The property value
     *
     * @return static self reference
     */
    public function lastName($value)
    {
        $this->lastName = $value;

        return $this;
    }

    /**
     * Sets the [[lastLoginDate]] property.
     *
     * @param string|string[]|null $value The property value
     *
     * @return static self reference
     */
    public function lastLoginDate($value)
    {
        $this->lastLoginDate = $value;

        return $this;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function beforePrepare(): bool
    {
        $this->joinElementTable('social_login_accounts');
        $this->query->leftJoin('{{%users}} users', '[[social_login_accounts.userId]] = [[users.id]]');
        $this->subQuery->leftJoin('{{%users}} users', '[[social_login_accounts.userId]] = [[users.id]]');

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