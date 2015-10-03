<?php

namespace Dukt\Social\Etc\Users;

/**
 * TokenIdentity represents the data needed to identify a user with a token and an email
 * It contains the authentication method that checks if the provided data can identity the user.
 */

class TokenIdentity extends \Craft\UserIdentity
{
    private $_id;
    public $socialUserId;

    public function __construct($socialUserId)
    {
        $this->socialUserId = $socialUserId;
    }

    public function authenticate()
    {
        \Craft\Craft::log(__METHOD__, \Craft\LogLevel::Info, true);

        $socialUser = \Craft\craft()->social->getSocialUserById($this->socialUserId);

        if($socialUser)
        {
            $this->_id = $socialUser->user->id;
            $this->username = $socialUser->user->username;
            $this->errorCode = static::ERROR_NONE;

            return true;
        }
        else
        {
            return false;
        }
    }

    public function getId()
    {
        return $this->_id;
    }
}
