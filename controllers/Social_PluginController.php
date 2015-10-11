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

class Social_PluginController extends BaseController
{
    // Public Methods
    // =========================================================================

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