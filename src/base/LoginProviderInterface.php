<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\base;

use dukt\oauth\models\Token;

interface LoginProviderInterface
{
	// Public Methods
	// =========================================================================

	/**
	 * Returns the name of the login provider
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Returns the handle of the OAuth provider
	 *
	 * @return string
	 */
	public function getOauthProviderHandle();

	/**
	 * Returns a profile from an OAuth token
	 *
	 * @param Token $token
	 *
	 * @return array|null
	 */
	public function getProfile(Token $token);
}