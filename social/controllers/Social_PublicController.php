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

        $redirect = craft()->request->getParam('redirect');

        if(!$redirect) {
            if(isset($_SERVER['HTTP_REFERER'])) {
                $redirect = $_SERVER['HTTP_REFERER'];
            }

            $redirect = '';
        }

		$this->redirect($redirect);
	}

	// --------------------------------------------------------------------

    public function actionLogin()
    {
        $providerClass = craft()->request->getParam('provider');
        $redirect = craft()->request->getParam('redirect');
        $scope = craft()->request->getParam('scope');


        // social callbackUrl

        $callbackUrl = UrlHelper::getSiteUrl(craft()->config->get('actionTrigger').'/social/public/loginCallback');


        // set session variables

        craft()->oauth->sessionClean();

        craft()->httpSession->add('oauth.social', true);
        craft()->oauth->sessionAdd('oauth.socialCallback', $callbackUrl);
        craft()->oauth->sessionAdd('oauth.socialReferer', $redirect);

        $this->redirect(UrlHelper::getSiteUrl(
                craft()->config->get('actionTrigger').'/oauth/connect',
                array('provider' => $providerClass, $scope)
            ));
    }
	public function actionLogin2()
	{
		// providerClass

        $providerClass = craft()->request->getParam('provider');


        if(!$redirect) {
            $redirect = $_SERVER['HTTP_REFERER'];
        }

		// social callbackUrl

        $callbackUrl = UrlHelper::getSiteUrl(craft()->config->get('actionTrigger').'/social/public/loginCallback');


		// set session variables

        craft()->oauth->sessionClean();

		craft()->oauth->sessionAdd('oauth.social', true);
		craft()->oauth->sessionAdd('oauth.socialCallback', $callbackUrl);
		craft()->oauth->sessionAdd('oauth.socialReferer', $redirect);
        // echo $redirect;
        // echo craft()->httpSession->get('oauth.socialReferer');
        // die();
		craft()->oauth->sessionAdd('oauth.userMode', true);
		craft()->oauth->sessionAdd('oauth.providerClass', $providerClass);


		// connect to provider (with default scope)

		$this->redirect(UrlHelper::getSiteUrl(
				craft()->config->get('actionTrigger').'/oauth/public/connect',
				array('provider' => $providerClass)
			));

		// ...

		// script goes on in oauth/public/connect
		// and then redirected to social/public/loginCallback
	}

	// --------------------------------------------------------------------

	public function actionLoginCallback()
	{
		// get httpSession variables

		$providerClass = craft()->httpSession->get('oauth.providerClass');
		$socialReferer = craft()->httpSession->get('oauth.socialReferer');

        // echo craft()->httpSession->get('oauth.socialReferer');
        // die('socialReferer');

        // ----------------------
        // instantiate provider
        // ----------------------


        // set token

        $token = craft()->httpSession->get('oauth.token');
        $token = @unserialize(base64_decode($token));


        // instantiate provider

        $provider = craft()->oauth->getProvider($providerClass);

        $provider->setToken($token);

        // get account

        $account = $provider->getAccount();


        // ----------------------
        // find a matching user
        // ----------------------

        $user = null;
        $userId =  craft()->userSession->id;


        // define user with current user

        if($userId) {
            $user = craft()->users->getUserById($userId);
        }


        // no current user ? check with account email

        if(!$user) {
            if(isset($account->email)) {
                $user = craft()->users->getUserByUsernameOrEmail($account['email']);
            }
        }


        // still no user ? check with account mapping

        if(!$user) {

            $criteriaConditions = '
                provider=:provider AND
                userMapping=:userMapping
                ';

            $criteriaParams = array(
                ':provider' => $providerClass,
                ':userMapping' => $account['uid']
                );

            $tokenRecord = Oauth_TokenRecord::model()->find($criteriaConditions, $criteriaParams);

            if($tokenRecord) {
                $userId = $tokenRecord->userId;
                $user = craft()->users->getUserById($userId);
            }
        }


        // no matching user, create one

        if(!$user) {

        	// new user

            if(isset($account['email'])) {
                // define email

                $usernameOrEmail = $account['email'];
            } else {
                // if no email, we create a fake one

                $usernameOrEmail = $account['uid'].'@'.strtolower($providerClass).'.social.dukt.net';
            }

            $newUser = new UserModel();
            $newUser->username = $usernameOrEmail;
            $newUser->email = $usernameOrEmail;

            $newUser->newPassword = md5(base64_encode(serialize($provider->getToken())));


            // save user

            craft()->users->saveUser($newUser);

            $user = craft()->users->getUserByUsernameOrEmail($usernameOrEmail);
        }


        // ----------------------
        // save token record
        // ----------------------

        // try to find an existing token

        $tokenRecord = null;

        if($user) {
	        $criteriaConditions = '
	            provider=:provider AND
	            userMapping=:userMapping AND
	            userId is not null
	            ';

	        $criteriaParams = array(
	            ':provider' => $providerClass,
	            ':userMapping' => $account['uid']
	            );

	        $tokenRecord = Oauth_TokenRecord::model()->find($criteriaConditions, $criteriaParams);

	        if($tokenRecord) {
	        	if($tokenRecord->user->id != $user->id) {
	        		// provider account already in use by another user
	        		die('provider account already in use by another craft user');
	        	}
	        }
        }



        // or create a new one

        if(!$tokenRecord) {
            $tokenRecord = new Oauth_TokenRecord();
            $tokenRecord->userId = $user->id;
            $tokenRecord->provider = $providerClass;

            $tokenRecord->userMapping = $account['uid'];
        }

        //scope

        $scope = craft()->httpSession->get('oauth.scope');

        if(!$scope) {
            $scope = $provider->getScope();
        }


        // update token variables

        $tokenRecord->token = base64_encode(serialize($provider->getToken()));

        $tokenRecord->scope = $scope;


        // save token

        $tokenRecord->save();


        // login user to craft

        if($provider->getToken()) {
            craft()->social_userSession->login(base64_encode(serialize($provider->getToken())));
        }

        // clean session variables

		craft()->oauth->sessionClean();


		// redirect

		$this->redirect($socialReferer);
	}

    // --------------------------------------------------------------------
}
