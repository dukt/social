<?php
namespace Craft;

interface ISocial_Provider
{
	// Public Methods
	// =========================================================================

	/**
	 * Get Name
	 *
	 * @return string
	 */
	public function getName();

	public function getOauthProviderHandle();

	public function getProfile(Oauth_TokenModel $token);
}