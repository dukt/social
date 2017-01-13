<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

use Dukt\Social\Etc\Users\SocialUserIdentity;

class Social_UserSessionService extends UserSessionService
{
	// Properties
	// =========================================================================

	/**
	 * Allow auto login
	 *
	 * @var bool
	 */
	public $allowAutoLogin = true;

	/**
	 * Stores the user identity.
	 *
	 * @var UserIdentity
	 */
	private $_identity;

	// Public Methods
	// =========================================================================

	public function init()
	{
		$this->setStateKeyPrefix(md5('Yii.Craft\UserSessionService.'.craft()->getId()));

		parent::init();

		require_once(CRAFT_PLUGINS_PATH.'social/etc/Users/SocialUserIdentity.php');
	}

	/**
	 * Login a user by social account ID.
	 *
	 * @param $accountId
	 *
	 * @return bool
	 */
	public function login($accountId)
	{
		$rememberMe = true;

		$this->_identity = new SocialUserIdentity($accountId);

		// Did we authenticate?
		if ($this->_identity->authenticate())
		{
			return $this->loginByUserId($this->_identity->getUserModel()->id, $rememberMe, true);
		}

		SocialPlugin::log('Tried to log in unsuccessfully:'.print_r($this->_identity->getUserModel(), true), LogLevel::Error);
		return false;
	}

	/**
	 * Returns the login error code from the user identity.
	 *
	 * @return int|null The login error code, or `null` if there isn’t one.
	 */
	public function getLoginErrorCode()
	{
		if (isset($this->_identity))
		{
			return $this->_identity->errorCode;
		}
	}
}
