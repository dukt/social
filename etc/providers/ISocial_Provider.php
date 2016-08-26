<?php
namespace Craft;

interface ISocial_Provider
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
	 * @return mixed
	 */
	public function getProfile(Oauth_TokenModel $token);
}