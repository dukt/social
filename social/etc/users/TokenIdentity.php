<?php

/**
 * Social Login for Craft
 *
 * @package   Social Login
 * @author    Benjamin David
 * @copyright Copyright (c) 2014, Dukt
 * @link      https://dukt.net/craft/social/
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

/**
 * TokenIdentity represents the data needed to identify a user with a token and an email
 * It contains the authentication method that checks if the provided data can identity the user.
 */

class TokenIdentity extends UserIdentity
{
    private $_id;
    public $socialUserId;

    public function __construct($socialUserId)
    {
        $this->socialUserId = $socialUserId;
    }

    public function authenticate()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $socialUser = craft()->social->getSocialUserById($this->socialUserId);

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
