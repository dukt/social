<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\controllers;

use Craft;
use craft\web\Controller;

class OauthController extends Controller
{
    protected $allowAnonymous = true;

    // Public Methods
    // =========================================================================
    /**
     * OAuth callback.
     *
     * @return null
     */
    public function actionCallback()
    {
        /*$handle = Craft::$app->getSession()->get('social.loginProvider');

        $loginProvider = Social::$plugin->getLoginProviders()->getLoginProvider($handle);
        $token = $loginProvider->oauthCallback();*/

        Craft::$app->getSession()->set('social.callback', true);

        $url = Craft::$app->getSession()->get('social.loginReferrer');

        if(strpos($url, '?') === false)
        {
            $url .= '?';
        }
        else
        {
            $url .= '&';
        }

        $url .= Craft::$app->getRequest()->getQueryString();

        return $this->redirect($url);
    }
}
