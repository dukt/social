<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\controllers;

use craft\web\Controller;

class InstallController extends Controller
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
        $missingDependencies = \dukt\social\Plugin::getInstance()->social->getMissingDependencies();

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
