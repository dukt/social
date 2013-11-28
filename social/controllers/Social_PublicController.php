<?php

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
        $scope = craft()->request->getParam('scope');


        // social callbackUrl

        $callbackUrl = UrlHelper::getSiteUrl(craft()->config->get('actionTrigger').'/social/public/loginCallback');


        // set session variables

        craft()->oauth->sessionClean();

        craft()->httpSession->add('oauth.social', true);
        craft()->oauth->sessionAdd('oauth.socialCallback', $callbackUrl);
        craft()->oauth->sessionAdd('oauth.socialRedirect', $redirect);
        craft()->oauth->sessionAdd('oauth.socialReferer', $_SERVER['HTTP_REFERER']);

        $this->redirect(UrlHelper::getSiteUrl(
                craft()->config->get('actionTrigger').'/oauth/connect',
                array('provider' => $providerClass, $scope)
            ));
    }

	public function actionLoginCallback()
	{
        Craft::log(__METHOD__, LogLevel::Info, true);

		// get httpSession variables

		$providerClass = craft()->httpSession->get('oauth.providerClass');
        $socialRedirect = craft()->httpSession->get('oauth.socialRedirect');
		$socialReferer = craft()->httpSession->get('oauth.socialReferer');

        // echo craft()->httpSession->get('oauth.socialRedirect');
        // die('socialRedirect');

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

        try {
            $account = $provider->getAccount();
        } catch(\Exception $e) {
            craft()->userSession->setError(Craft::t($e->getMessage()));

            $this->redirect($socialReferer);
        }


        if(!$account) {

            craft()->userSession->setError(Craft::t("Couldn't connect to your account."));

            $this->redirect($socialReferer);
        }


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

                // get social plugin settings

                $socialPlugin = craft()->plugins->getPlugin('social');
                $settings = $socialPlugin->getSettings();


                // no email allowed ?

                if($settings['allowFakeEmail']) {

                    // no email, we create a fake one

                    $usernameOrEmail = md5($account['uid']).'@'.strtolower($providerClass).'.social.dukt.net';
                } else {
                    // no email here ? we abort, craft requires at least a valid email

                    // add error before redirecting

                    craft()->userSession->setError(Craft::t("This OAuth provider doesn't provide email sharing. Please try another one."));

                    $this->redirect($socialReferer);
                }
            }

            $newUser = new UserModel();
            $newUser->username = $usernameOrEmail;
            $newUser->email = $usernameOrEmail;

            $newUser->newPassword = md5(serialize($provider->getToken()));


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

		$this->redirect($socialRedirect);
	}
}
