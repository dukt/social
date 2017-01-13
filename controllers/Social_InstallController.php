<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class Social_InstallController extends BaseController
{
	// Public Methods
	// =========================================================================

	/**
	 * Install
	 *
	 * @return null
	 */
    public function actionIndex()
    {
        $missingDependencies = craft()->social->getMissingDependencies();

        if (count($missingDependencies) > 0)
        {
            $this->renderTemplate('social/_special/install/dependencies', [
                'pluginDependencies' => $missingDependencies
            ]);
        }
        else
        {
            $this->redirect('social/settings');
        }
    }
}
