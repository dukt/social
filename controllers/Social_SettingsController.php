<?php

/**
 * Social plugin for Craft
 *
 * @package   Craft Social
 * @author    Benjamin David
 * @copyright Copyright (c) 2015, Dukt
 * @link      https://dukt.net/craft/social/
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class Social_SettingsController extends BaseController
{
	// Public Methods
	// =========================================================================

	/**
	 * Settings
	 *
	 * @return null
	 */
	public function actionIndex()
	{
		$plugin = craft()->plugins->getPlugin('social');
		$settings = $plugin->getSettings();
		$socialUsers = craft()->social_users->getUsers();
		$usersCount = count($socialUsers);


		$this->renderTemplate('social/settings', [
			'settings' => $settings,
			'usersCount' => $usersCount
		]);
	}
}
