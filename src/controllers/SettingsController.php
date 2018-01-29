<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2018, Dukt
 * @license   https://dukt.net/social/docs/license
 */

namespace dukt\social\controllers;

use Craft;
use craft\web\Controller;
use dukt\social\Plugin as Social;

/**
 * The SettingsController class is a controller that handles various settings related tasks.
 *
 * Note that all actions in the controller require an authenticated Craft session via [[allowAnonymous]].
 *
 * @author Dukt <support@dukt.net>
 * @since   1.0
 */
class SettingsController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * General settings.
     *
     * @return null
     */
    public function actionSettings()
    {
        if(Craft::$app->getEdition() !== Craft::Pro) {
            return $this->renderTemplate('social/settings/_pro-requirement');
        }

        $plugin = Craft::$app->getPlugins()->getPlugin('social');
        $variables['settings'] = $plugin->getSettings();

        $accounts = Social::$plugin->getLoginAccounts()->getLoginAccounts();
        $variables['totalAccounts'] = count($accounts);

        return $this->renderTemplate('social/settings/settings', $variables);
    }
}
