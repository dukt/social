<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2018, Dukt
 * @license   https://dukt.net/social/docs/license
 */

namespace dukt\social\controllers;

use Craft;
use craft\web\Controller;
use dukt\social\Plugin;
use dukt\social\web\assets\social\SocialAsset;
use dukt\social\Plugin as Social;
use yii\web\HttpException;
use yii\web\Response;

/**
 * The LoginProvidersController class is a controller that handles various login provider related tasks.
 *
 * Note that all actions in the controller require an authenticated Craft session via [[allowAnonymous]].
 *
 * @author  Dukt <support@dukt.net>
 * @since   1.0
 */
class LoginProvidersController extends Controller
{

    // Public Methods
    // =========================================================================

    /**
     * Login Providers index.
     *
     * @return Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex(): Response
    {
        if (Craft::$app->getEdition() !== Craft::Pro) {
            return $this->renderTemplate('social/settings/_pro-requirement');
        }

        Craft::$app->getView()->registerAssetBundle(SocialAsset::class);

        $variables['loginProviders'] = Social::$plugin->getLoginProviders()->getLoginProviders(false);

        return $this->renderTemplate('social/loginproviders/_index', $variables);
    }

    /**
     * Login provider’s OAuth settings.
     *
     * @param $handle
     *
     * @return Response
     * @throws HttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionOauth($handle): Response
    {
        if (Craft::$app->getEdition() !== Craft::Pro) {
            return $this->renderTemplate('social/settings/_pro-requirement');
        }

        $loginProvider = Social::$plugin->getLoginProviders()->getLoginProvider($handle, false, true);
        $oauthProviderConfig = Social::getInstance()->getOauthProviderConfig($handle);

        if ($loginProvider) {
            return $this->renderTemplate('social/loginproviders/_oauth', [
                'loginProvider' => $loginProvider,
                'oauthProviderConfig' => $oauthProviderConfig,
            ]);
        }

        throw new HttpException(404);
    }

    /**
     * Login provider’s user mapping.
     *
     * @param $handle
     *
     * @return Response
     * @throws HttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUserMapping($handle): Response
    {
        if (Craft::$app->getEdition() !== Craft::Pro) {
            return $this->renderTemplate('social/settings/_pro-requirement');
        }

        $loginProvider = Social::$plugin->getLoginProviders()->getLoginProvider($handle, false, true);
        $userMapping = Social::$plugin->getLoginProviders()->getUserMapping($handle);

        if ($loginProvider) {
            return $this->renderTemplate('social/loginproviders/_usermapping', [
                'loginProvider' => $loginProvider,
                'userMapping' => $userMapping,
            ]);
        }

        throw new HttpException(404);
    }

    /**
     * Enable login provider.
     *
     * @return Response
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionEnableLoginProvider(): Response
    {
        $this->requirePostRequest();
        $loginProvider = Craft::$app->getRequest()->getRequiredBodyParam('loginProvider');

        if (Social::$plugin->getLoginProviders()->enableLoginProvider($loginProvider)) {
            Craft::$app->getSession()->setNotice(Craft::t('social', 'Login provider enabled.'));
        } else {
            Craft::$app->getSession()->setError(Craft::t('social', 'Couldn’t enable login provider.'));
        }

        return $this->redirectToPostedUrl();
    }

    /**
     * Disable login provider.
     *
     * @return Response
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDisableLoginProvider(): Response
    {
        $this->requirePostRequest();
        $loginProvider = Craft::$app->getRequest()->getRequiredBodyParam('loginProvider');

        if (Social::$plugin->getLoginProviders()->disableLoginProvider($loginProvider)) {
            Craft::$app->getSession()->setNotice(Craft::t('social', 'Login provider disabled.'));
        } else {
            Craft::$app->getSession()->setError(Craft::t('social', 'Couldn’t disable login provider.'));
        }

        return $this->redirectToPostedUrl();
    }

    public function actionSaveOauthProvider()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();

        $handle = $request->getBodyParam('handle');
        $settings = (array) Plugin::getInstance()->getSettings();
        $oauthProviders = $settings['oauthProviders'];

        $oauthProviders[$handle] = [
            'clientId' => $request->getBodyParam('clientId'),
            'clientSecret' => $request->getBodyParam('clientSecret'),
        ];

        $settings['oauthProviders'] = $oauthProviders;

        $plugin = Craft::$app->getPlugins()->getPlugin('social');

        if (Craft::$app->getPlugins()->savePluginSettings($plugin, $settings)) {
            Craft::$app->getSession()->setNotice(Craft::t('analytics', 'Provider saved.'));

            return $this->redirectToPostedUrl();
        }

        Craft::$app->getSession()->setError(Craft::t('analytics', 'Couldn’t save provider.'));

        // Send the plugin back to the template
        Craft::$app->getUrlManager()->setRouteParams([
            'plugin' => $plugin
        ]);

        return null;
    }
}
