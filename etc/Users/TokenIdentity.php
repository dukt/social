<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Dukt\Social\Etc\Users;

/**
 * TokenIdentity represents the data needed to identify a user with a token and an email
 * It contains the authentication method that checks if the provided data can identity the user.
 */

class TokenIdentity extends \Craft\UserIdentity
{
    private $_id;
    public $accountId;

    /**
     * Constructor
     *
     * @param int $accountId
     *
     * @return null
     */
    public function __construct($accountId)
    {
        $this->accountId = $accountId;
    }

	/**
     * Authenticate
     *
     * @return bool
     */
    public function authenticate()
    {
        \Craft\Craft::log(__METHOD__, \Craft\LogLevel::Info, true);

        $account = \Craft\craft()->social_accounts->getAccountById($this->accountId);

        if($account)
        {
            $this->_id = $account->user->id;
            $this->username = $account->user->username;
            $this->errorCode = static::ERROR_NONE;

            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }
}
