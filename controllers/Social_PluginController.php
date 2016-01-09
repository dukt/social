<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class Social_PluginController extends BaseController
{
    // Public Methods
    // =========================================================================

	/**
     * Install
     *
     * @return null
     */
    public function actionInstall()
    {
        $variables['pluginDependencies'] = craft()->social_plugin->getPluginDependencies();

        if (count($variables['pluginDependencies']) > 0)
        {
            $this->renderTemplate('social/install/_index', $variables);
        }
        else
        {
            $this->redirect('social/settings');
        }
    }
}