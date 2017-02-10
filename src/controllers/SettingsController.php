<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\controllers;

use Craft;
use craft\web\Controller;

class SettingsController extends Controller
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

        \dukt\social\Plugin::getInstance()->social->requireDependencies();
	}

	/**
	 * Settings Index
	 *
	 * @return null
	 */
	public function actionIndex()
	{
		$plugin = Craft::$app->plugins->getPlugin('social');
		$variables['settings'] = $plugin->getSettings();

		$accounts = \dukt\social\Plugin::getInstance()->social_loginAccounts->getLoginAccounts();
		$variables['totalAccounts'] = count($accounts);

		return $this->renderTemplate('social/settings/_index', $variables);
	}
}
