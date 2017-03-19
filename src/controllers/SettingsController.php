<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/social/docs/license
 */

namespace dukt\social\controllers;

use Craft;
use craft\web\Controller;
use dukt\social\Plugin as Social;

class SettingsController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * General settings.
     *
     * @return null
     */
    public function actionGeneral()
    {
        $plugin = Craft::$app->getPlugins()->getPlugin('social');
        $variables['settings'] = $plugin->getSettings();

        $accounts = Social::$plugin->getLoginAccounts()->getLoginAccounts();
        $variables['totalAccounts'] = count($accounts);

        return $this->renderTemplate('social/settings/_general', $variables);
    }
}
