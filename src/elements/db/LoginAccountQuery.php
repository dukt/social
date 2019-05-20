<?php

namespace dukt\social\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

/**
 * LoginAccountQuery represents a SELECT SQL statement for login accounts in a way that is independent of DBMS.
 *
 * @author Dukt <support@dukt.net>
 * @since  2.0
 */
class LoginAccountQuery extends ElementQuery
{
    // Properties
    // =========================================================================

    // General parameters
    // -------------------------------------------------------------------------

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
    public $email;

    /**
     * @var
     */
    public $username;

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

        $this->subQuery->leftJoin('{{%elements}} elements_users', '[[social_login_accounts.userId]] = [[elements_users.id]]');

        $this->query->select([
            'social_login_accounts.userId',
            'social_login_accounts.providerHandle',
            'social_login_accounts.socialUid',
        ]);

        $this->addWhere('userId', 'social_login_accounts.userId');
        $this->addWhere('providerHandle', 'social_login_accounts.providerHandle');
        $this->addWhere('socialUid', 'social_login_accounts.socialUid');
        $this->addWhere('email', 'users.email');
        $this->addWhere('username', 'users.username');
        $this->addWhere('firstName', 'users.firstName');
        $this->addWhere('lastName', 'users.lastName');
        $this->addWhere('lastLoginDate', 'users.lastLoginDate');

        return parent::beforePrepare();
    }

    /**
     * @param string $property
     * @param string $column
     */
    private function addWhere(string $property, string $column)
    {
        if ($this->{$property}) {
            $this->subQuery->andWhere(Db::parseParam($column, $this->{$property}));
        }
    }
}
