<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2018, Dukt
 * @license   https://dukt.net/social/docs/license
 */

namespace dukt\social\controllers;

use Craft;
use craft\web\Controller;
use dukt\social\web\assets\social\SocialAsset;
use dukt\social\Plugin as Social;
use yii\web\HttpException;
use yii\web\Response;

/**
 * The LoginProvidersController class is a controller that handles various login provider related tasks.
 *
 * Note that all actions in the controller require an authenticated Craft session via [[allowAnonymous]].
 *
 * @author Dukt <support@dukt.net>
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
        if(Craft::$app->getEdition() !== Craft::Pro) {
            return $this->renderTemplate('social/settings/_pro-requirement');
        }

        Craft::$app->getView()->registerAssetBundle(SocialAsset::class);

        $variables['loginProviders'] = Social::$plugin->getLoginProviders()->getLoginProviders(false);

        return $this->renderTemplate('social/loginproviders/_index', $variables);
    }

    /**
     * Edit login provider.
     *
     * @param $handle
     *
     * @return Response
     * @throws HttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionEdit($handle): Response
    {
        if(Craft::$app->getEdition() !== Craft::Pro) {
            return $this->renderTemplate('social/settings/_pro-requirement');
        }

        $loginProvider = Social::$plugin->getLoginProviders()->getLoginProvider($handle, false, true);

        if ($loginProvider) {
            return $this->renderTemplate('social/loginproviders/_edit', [
                'handle' => $handle,
                'infos' => $loginProvider->getInfos(),
                'loginProvider' => $loginProvider
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
}
