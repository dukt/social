<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2018, Dukt
 * @license   https://github.com/dukt/social/blob/v2/LICENSE.md
 */

namespace dukt\social\controllers;

use Craft;
use craft\web\Controller;
use dukt\social\Plugin;
use yii\web\Response;

/**
 * The SettingsController class is a controller that handles various settings related tasks.
 *
 * Note that all actions in the controller require an authenticated Craft session via [[allowAnonymous]].
 *
 * @author  Dukt <support@dukt.net>
 * @since   1.0
 */
class SettingsController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * General settings.
     *
     * @return Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionSettings(): Response
    {
        if (Craft::$app->getEdition() !== Craft::Pro) {
            return $this->renderTemplate('social/settings/_pro-requirement');
        }

        $plugin = Craft::$app->getPlugins()->getPlugin('social');
        $variables['settings'] = $plugin->getSettings();

        $accounts = Plugin::getInstance()->getLoginAccounts()->getLoginAccounts();
        $variables['totalAccounts'] = count($accounts);

        return $this->renderTemplate('social/settings/settings', $variables);
    }

    /**
     * Saves the settings.
     *
     * @return null|Response
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveSettings()
    {
        $this->requirePostRequest();
        $settings = Craft::$app->getRequest()->getBodyParam('settings', []);
        $plugin = Craft::$app->getPlugins()->getPlugin('social');

        if ($plugin === null) {
            throw new NotFoundHttpException('Plugin not found');
        }

        if (!Plugin::getInstance()->savePluginSettings($settings, $plugin)) {
            Craft::$app->getSession()->setError(Craft::t('app', 'Couldn’t save plugin settings.'));

            // Send the plugin back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'plugin' => $plugin
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('app', 'Plugin settings saved.'));

        return $this->redirectToPostedUrl();
    }
}
