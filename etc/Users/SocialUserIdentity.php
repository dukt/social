<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Dukt\Social\Etc\Users;

use Craft\Craft;
use Craft\UserIdentity;
use Craft\UserModel;
use Craft\UserStatus;
use Craft\Oauth_TokenModel;

/**
 * SocialUserIdentity represents the data needed to identify a user with a token and an email
 * It contains the authentication method that checks if the provided data can identity the user.
 */

class SocialUserIdentity extends UserIdentity
{
	// Properties
	// =========================================================================

	/**
	 * @var Oauth_TokenModel|null
	 */
	public $token;

	/**
	 * @var int
	 */
	private $_id;

	/**
	 * @var UserModel
	 */
	private $_userModel;

    // Public Methods
    // =========================================================================

    /**
	 * Constructor
	 *
	 * @param Oauth_TokenModel $token
	 *
	 * @return null
	 */
	public function __construct(Oauth_TokenModel $token)
	{
	    $this->token = $token;

        $socialLoginProvider = Craft::app()->social_loginProviders->getLoginProvider($this->token->providerHandle);
        $data = $socialLoginProvider->getProfile($this->token);
        $account = Craft::app()->social_loginAccounts->getLoginAccountByUid($socialLoginProvider->getHandle(), $data['id']);

        if ($account)
        {
            $this->_userModel = $account->getUser();
        }
	}

	/**
	 * Authenticate
	 *
	 * @return bool
	 */
	public function authenticate()
	{
        if ($this->_userModel)
        {
            return $this->_processUserStatus($this->_userModel);
        }
        else
        {
            $this->errorCode = static::ERROR_UNKNOWN_IDENTITY;
            return false;
        }
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->_id;
	}

	/**
	 * @return UserModel
	 */
	public function getUserModel()
	{
		return $this->_userModel;
	}

	// Private Methods
	// =========================================================================

	/**
	 * @param UserModel $user
	 *
	 * @throws Exception
	 * @return null
	 */
	private function _processUserStatus(UserModel $user)
	{
		switch ($user->status)
		{
			// If the account is pending, they don't exist yet.
			case UserStatus::Archived:
			{
				$this->errorCode = static::ERROR_USERNAME_INVALID;
				break;
			}

			case UserStatus::Locked:
			{
				$this->errorCode = $this->_getLockedAccountErrorCode();
				break;
			}

			case UserStatus::Suspended:
			{
				$this->errorCode = static::ERROR_ACCOUNT_SUSPENDED;
				break;
			}

			case UserStatus::Pending:
			{
				$this->errorCode = static::ERROR_PENDING_VERIFICATION;
				break;
			}

			case UserStatus::Active:
			{
				$this->_id = $user->id;
				$this->username = $user->username;

				// Everything is good.
				$this->errorCode = static::ERROR_NONE;

				break;
			}

			default:
			{
				throw new Exception(Craft::t('User has unknown status “{status}”', array($user->status)));
			}
		}

		return $this->errorCode === static::ERROR_NONE;
	}
}
