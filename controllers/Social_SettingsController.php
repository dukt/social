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
	 * Init
	 *
	 * @return null
	 */
    public function init()
    {
        $plugin = craft()->plugins->getPlugin('social');
        $pluginDependencies = $plugin->getPluginDependencies();

        if (count($pluginDependencies) > 0)
        {
            $this->redirect('social/install');
        }
    }

	/**
	 * Settings
	 *
	 * @return null
	 */
	public function actionIndex()
	{
		$plugin = craft()->plugins->getPlugin('social');
		$variables['settings'] = $plugin->getSettings();

		$accounts = craft()->social_accounts->getAccounts();
		$variables['totalAccounts'] = count($accounts);

		$this->renderTemplate('social/settings', $variables);
	}
}
