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

require_once(CRAFT_PLUGINS_PATH.'social/vendor/autoload.php');

use Guzzle\Http\Client;

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
        $this->redirect($_SERVER['HTTP_REFERER']);
    }

    public function actionLogin()
    {
        // request params
        $handle = craft()->request->getParam('provider');
        $redirect = craft()->request->getParam('redirect');
        $errorRedirect = craft()->request->getParam('errorRedirect');

        // provider scopes & params
        $scopes = $this->getScopes($handle);
        $params = $this->getParams($handle);

        // session vars

        craft()->oauth->sessionClean();

        craft()->httpSession->add('oauth.referer', (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null));
        craft()->httpSession->add('oauth.scopes', $scopes);
        craft()->httpSession->add('oauth.params', $params);


        // redirect
        $redirect = UrlHelper::getActionUrl('oauth/public/connect/', array('provider' => $handle));
        $this->redirect($redirect);
    }

    public function actionLogout()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        craft()->userSession->logout(false);

        $redirect = craft()->request->getParam('redirect');

        if(!$redirect) {
            if(isset($_SERVER['HTTP_REFERER'])) {
                $redirect = $_SERVER['HTTP_REFERER'];
            }

            $redirect = '';
        }

        $this->redirect($redirect);
    }








    private function getScopes($handle)
    {
        switch($handle)
        {
            case 'google':

                return array(
                    'userinfo_profile',
                    'userinfo_email'
                );

                break;
        }

        return array();
    }

    private function getParams($handle)
    {
        switch($handle)
        {
            case 'google':

                return array(
                    'access_type' => 'offline'
                );

                break;
        }

        return array();
    }
}
