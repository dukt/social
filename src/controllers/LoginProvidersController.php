<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2021, Dukt
 * @license   https://github.com/dukt/social/blob/v2/LICENSE.md
 */

namespace dukt\social\controllers;

use Craft;
use craft\web\Controller;
use dukt\social\Plugin;
use dukt\social\web\assets\social\SocialAsset;
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
class LoginProvidersController extends BaseController
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

        return $this->renderTemplate('social/loginproviders/_index', [
            'loginProviders' => Plugin::getInstance()->getLoginProviders()->getLoginProviders(false)
        ]);
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

        $loginProvider = Plugin::getInstance()->getLoginProviders()->getLoginProvider($handle, false);
        $oauthProviderConfig = Plugin::getInstance()->getOauthProviderConfig($handle, false);

        if ($loginProvider !== null) {
            return $this->renderTemplate('social/loginproviders/_oauth', [
                'loginProvider' => $loginProvider,
                'oauthProviderConfig' => $oauthProviderConfig,
            ]);
        }

        throw new HttpException(404);
    }

    /**
     * Login provider’s user field mapping.
     *
     * @param $handle
     *
     * @return Response
     * @throws HttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUserFieldMapping($handle): Response
    {
        if (Craft::$app->getEdition() !== Craft::Pro) {
            return $this->renderTemplate('social/settings/_pro-requirement');
        }

        $loginProvider = Plugin::getInstance()->getLoginProviders()->getLoginProvider($handle, false);

        if ($loginProvider !== null) {
            return $this->renderTemplate('social/loginproviders/_user-field-mapping', [
                'loginProvider' => $loginProvider,
            ]);
        }

        throw new HttpException(404);
    }

    /**
     * Enable login provider.
     *
     * @return Response
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionEnableLoginProvider(): Response
    {
        $this->requirePostRequest();
        $loginProvider = Craft::$app->getRequest()->getRequiredBodyParam('loginProvider');

        if (Plugin::getInstance()->getLoginProviders()->enableLoginProvider($loginProvider)) {
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
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDisableLoginProvider(): Response
    {
        $this->requirePostRequest();
        $loginProvider = Craft::$app->getRequest()->getRequiredBodyParam('loginProvider');

        if (Plugin::getInstance()->getLoginProviders()->disableLoginProvider($loginProvider)) {
            Craft::$app->getSession()->setNotice(Craft::t('social', 'Login provider disabled.'));
        } else {
            Craft::$app->getSession()->setError(Craft::t('social', 'Couldn’t disable login provider.'));
        }

        return $this->redirectToPostedUrl();
    }

    /**
     * Saves an OAuth provider.
     *
     * @return null|Response
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveOauthProvider()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();

        $handle = $request->getBodyParam('handle');

        $settings = [
            'oauth' => [
                'options' => [
                    'clientId' => $request->getBodyParam('clientId'),
                    'clientSecret' => $request->getBodyParam('clientSecret'),
                ]
            ]
        ];

        if (Plugin::getInstance()->saveLoginProviderSettings($handle, $settings)) {
            Craft::$app->getSession()->setNotice(Craft::t('social', 'Provider saved.'));

            return $this->redirectToPostedUrl();
        }

        Craft::$app->getSession()->setError(Craft::t('social', 'Couldn’t save provider.'));

        return null;
    }
}
