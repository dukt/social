<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
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
     * Settings Index
     *
     * @return null
     */
    public function actionIndex()
    {
        $plugin = Craft::$app->getPlugins()->getPlugin('social');
        $variables['settings'] = $plugin->getSettings();

        $accounts = Social::$plugin->getLoginAccounts()->getLoginAccounts();
        $variables['totalAccounts'] = count($accounts);

        return $this->renderTemplate('social/settings/_index', $variables);
    }
}
