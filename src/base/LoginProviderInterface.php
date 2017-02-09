<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Dukt\Social\Base;

use Craft\Oauth_TokenModel;

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
	 * @param Oauth_TokenModel $token
	 *
	 * @return array|null
	 */
	public function getProfile(Oauth_TokenModel $token);
}