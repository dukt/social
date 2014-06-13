<?php

/**
 * Social Login for Craft
 *
 * @package   Social Login
 * @author    Benjamin David
 * @copyright Copyright (c) 2013, Dukt
 * @link      http://dukt.net/craft/social/
 * @license   http://dukt.net/craft/social/docs/license
 */

namespace Craft;

class SocialController extends BaseController
{
    public $allowAnonymous = true;

    public function actionConnect()
    {
        $this->actionLogin();
    }

    public function actionDisconnect()
    {
        $handle = craft()->request->getParam('provider');
        craft()->social->deleteUserByProvider($handle);
        $redirect = craft()->request->getUrlReferrer();
        $this->redirect($redirect);
    }

    public function actionLogin()
    {
        // request params
        $handle = craft()->request->getParam('provider');
        $redirect = craft()->request->getParam('redirect');
        $errorRedirect = craft()->request->getParam('errorRedirect');

        // redirect url
        if(!$redirect)
        {
            $redirect = craft()->request->getUrlReferrer();
        }

        // don't go further if social login disabled
        $plugin = craft()->plugins->getPlugin('social');
        $settings = $plugin->getSettings();

        if(!$settings['allowSocialLogin'])
        {
            craft()->httpSession->add('error', "Social login disabled");
            $this->redirect($redirect);
        }

        // provider scopes & params
        $scopes = craft()->social->getScopes($handle);
        $params = craft()->social->getParams($handle);

        craft()->oauth->connect(array(
            'plugin' => 'social',
            'provider' => $handle,
            'redirect' => $redirect,
            'scopes' => $scopes,
            'params' => $params
        ));
    }

    public function actionLogout()
    {
        craft()->userSession->logout(false);

        $redirect = craft()->request->getParam('redirect');

        if(!$redirect)
        {
            $redirect = craft()->request->getUrlReferrer();
        }

        $this->redirect($redirect);
    }
}
