<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2015, Dukt
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
        $pluginDependencies = craft()->social_plugin->getPluginDependencies();

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

		$accounts = craft()->social_loginAccounts->getLoginAccounts();
		$variables['totalAccounts'] = count($accounts);

		$this->renderTemplate('social/settings', $variables);
	}
}
