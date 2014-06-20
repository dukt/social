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
    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function authenticate()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $socialUserRecord = Social_UserRecord::model()->find('userId is not null and encodedToken=:token', array(':token' => $this->token));

        if($socialUserRecord) {
            $this->_id = $socialUserRecord->user->id;
            $this->username = $socialUserRecord->user->username;
            $this->errorCode = static::ERROR_NONE;

            return true;
        } else {
            return false;
        }
    }

    public function getId()
    {
        return $this->_id;
    }
}
