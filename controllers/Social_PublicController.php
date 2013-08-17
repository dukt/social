<?php

namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'social/vendor/autoload.php');

use Guzzle\Http\Client;

class Social_PublicController extends BaseController
{
	// --------------------------------------------------------------------

	public $allowAnonymous = true;

	// --------------------------------------------------------------------

	public function actionLogout()
	{
		craft()->userSession->logout(false);

		$redirect = '';

		if(isset($_SERVER['HTTP_REFERER'])) {
			$redirect = $_SERVER['HTTP_REFERER'];
		}

		$this->redirect($redirect);
	}

	// --------------------------------------------------------------------

	public function actionLogin()
	{
		// providerClass

		$providerClass = craft()->request->getParam('provider');


		// social callbackUrl

        $callbackUrl = UrlHelper::getSiteUrl(craft()->config->get('actionTrigger').'/social/public/loginCallback');


		// set session variables

		craft()->oauth->httpSessionAdd('oauth.social', true);
		craft()->oauth->httpSessionAdd('oauth.socialCallback', $callbackUrl);
		craft()->oauth->httpSessionAdd('oauth.socialReferer', $_SERVER['HTTP_REFERER']);
		craft()->oauth->httpSessionAdd('oauth.userMode', true);
		craft()->oauth->httpSessionAdd('oauth.providerClass', $providerClass);


		// connect to provider (with default scope)

		$this->redirect(UrlHelper::getSiteUrl(
				craft()->config->get('actionTrigger').'/oauth/public/connect',
				array('provider' => $providerClass)
			));

		// ...

		// script goes on in oauth/public/connect
		// and then redirected to social/public/loginCallback
	}
