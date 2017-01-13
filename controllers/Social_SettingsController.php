<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class Social_SettingsController extends BaseController
{
	// Public Methods
	// =========================================================================

	/**
	 * Init
	 *
	 * @return null
	 */
	public function init()
	{
		parent::init();

        craft()->social->requireDependencies();
	}

	/**
	 * Settings Index
	 *
	 * @return null
	 */
	public function actionIndex()
	{
		$plugin = craft()->plugins->getPlugin('social');
		$variables['settings'] = $plugin->getSettings();

		$accounts = craft()->social_loginAccounts->getLoginAccounts();
		$variables['totalAccounts'] = count($accounts);

		$this->renderTemplate('social/settings/_index', $variables);
	}
}
