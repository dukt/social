<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\services;

use yii\base\Component;
use Dukt\Social\Etc\Users\SocialUserIdentity;

class UserSession extends UserSessionService
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

    /**
     * @inheritdoc
     *
     * @return null
     */
	public function init()
	{
		$this->setStateKeyPrefix(md5('Yii.Craft\UserSessionService.'.craft()->getId()));

		parent::init();

		require_once(CRAFT_PLUGINS_PATH.'social/etc/Users/SocialUserIdentity.php');
	}

	/**
	 * Login a user by social account ID.
	 *
	 * @param $token
	 *
	 * @return bool
	 */
	public function login(Oauth_TokenModel $token)
	{
		$rememberMe = true;

		$this->_identity = new SocialUserIdentity($token);

		// Did we authenticate?
		if ($this->_identity->authenticate())
		{
			return $this->loginByUserId($this->_identity->getUserModel()->id, $rememberMe, true);
		}

		Social::log('Tried to log in unsuccessfully:'.print_r($this->_identity->getUserModel(), true), LogLevel::Error);

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
