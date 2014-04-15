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

class Social_PublicController extends BaseController
{
	public $allowAnonymous = true;

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

    public function actionLogin()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $providerClass = craft()->request->getParam('provider');
        $redirect = craft()->request->getParam('redirect');
        $errorRedirect = craft()->request->getParam('errorRedirect');
        $scope = craft()->request->getParam('scope');


        // social callbackUrl

        $callbackUrl = UrlHelper::getSiteUrl(craft()->config->get('actionTrigger').'/social/public/loginCallback');


        // set session variables

        craft()->oauth->sessionClean();

        craft()->httpSession->add('oauth.social', true);
        craft()->oauth->sessionAdd('oauth.socialCallback', $callbackUrl);
        craft()->oauth->sessionAdd('oauth.socialRedirect', $redirect);

        if($errorRedirect)
        {
            craft()->oauth->sessionAdd('oauth.socialReferer', $errorRedirect);
        }
        elseif(isset($_SERVER['HTTP_REFERER']))
        {
            craft()->oauth->sessionAdd('oauth.socialReferer', $_SERVER['HTTP_REFERER']);
        }

        $this->redirect(UrlHelper::getSiteUrl(
                craft()->config->get('actionTrigger').'/oauth/connect',
                array(
                    'provider' => $providerClass,
                    'scope' => $scope
                )
            ));
    }
}
