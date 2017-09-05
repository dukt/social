<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/social/docs/license
 */

namespace dukt\social\controllers;

use Craft;
use craft\web\Controller;
use dukt\social\web\assets\social\SocialAsset;
use dukt\social\Plugin as Social;
use yii\web\HttpException;

class LoginProvidersController extends Controller
{

    // Public Methods
    // =========================================================================

    /**
     * Login Providers Index
     *
     * @return null
     */
    public function actionIndex()
    {
        Craft::$app->getView()->registerAssetBundle(SocialAsset::class);

        $variables['loginProviders'] = Social::$plugin->getLoginProviders()->getLoginProviders(false);

        return $this->renderTemplate('social/loginproviders/_index', $variables);
    }

    /**
     * Edit Login Provider
     *
     * @param string $handle Login provider’s handle
     *
     * @throws HttpException
     * @return null
     */
    public function actionEdit($handle)
    {
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
     * Enable Login Provider
     *
     * @return null
     */
    public function actionEnableLoginProvider()
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
     * Disable Login Provider
     *
     * @return null
     */
    public function actionDisableLoginProvider()
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
