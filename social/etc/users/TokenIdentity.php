<?php
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

        $tokenRecord = Oauth_TokenRecord::model()->find('userId is not null and token=:token', array(':token' => $this->token));

        if($tokenRecord) {
            $this->_id = $tokenRecord->user->id;
            $this->username = $tokenRecord->user->username;
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
