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

class Social_GatewaysController extends BaseController
{
	// Public Methods
	// =========================================================================

    public function init()
    {
        $pluginDependencies = craft()->social_plugin->getPluginDependencies();

        if (count($pluginDependencies) > 0)
        {
            $this->redirect('social/install');
        }
    }

	public function actionIndex()
	{
		$variables['gateways'] = craft()->social_gateways->getGateways(false);

		$this->renderTemplate('social/gateways/_index', $variables);
	}
}
