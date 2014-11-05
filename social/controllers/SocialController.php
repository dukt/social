<?php

/**
 * Social Login for Craft
 *
 * @package   Social Login
 * @author    Benjamin David
 * @copyright Copyright (c) 2014, Dukt
 * @link      https://dukt.net/craft/social/
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

use Guzzle\Http\Client;

class SocialController extends BaseController
{
    public $allowAnonymous = true;

    private $provider;
    private $pluginSettings;
    private $socialUid;
    private $redirect;
    private $token;
    private $tokenArray;

    public function actionSettings()
    {
        $plugin = craft()->plugins->getPlugin('social');
        $settings = $plugin->getSettings();

        $this->renderTemplate('social/settings/settings', array(
                'settings' => $settings
            ));
    }

    public function actionChangePhoto()
    {
        $userId = craft()->request->getParam('userId');
        $photoUrl = craft()->request->getParam('photoUrl');

        $user = craft()->users->getUserById($userId);

        craft()->social->saveRemotePhoto($photoUrl, $user);

        $this->redirect($_SERVER['HTTP_REFERER']);
    }

    public function actionDisconnect()
    {
        $handle = craft()->request->getParam('provider');

        if($handle == 'google')
        {

            $currentUser = craft()->userSession->getUser();
            $userId = $currentUser->id;

            $socialUser = craft()->social->getSocialUserByUserId($userId, $handle);
            $token = $socialUser->token->token;

            $accessToken = $token->getAccessToken();
            $client = new Client();

            try {
                $response = $client->get('https://accounts.google.com/o/oauth2/revoke?token='.$accessToken)->send();
            }
            catch(\Exception $e)
            {

            }

            // $this->deleteToken($model);
        }

        craft()->social->deleteUserByProvider($handle);
        $redirect = craft()->request->getUrlReferrer();
        $this->redirect($redirect);
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

    public function actionUserProfile()
    {
        // order

        $routeParams = craft()->urlManager->getRouteParams();

        $socialUserId = $routeParams['variables']['id'];

        $socialUser = craft()->social->getSocialUserById($socialUserId);

        $variables = array(
            'socialUser' => $socialUser
        );

        $this->renderTemplate('social/userProfile', $variables);
    }

    public function actionConnect()
    {
        $this->actionLogin();
    }

    public function actionLogin()
    {
        if(craft()->request->getPost('action') == 'social/completeRegistration')
        {
            $this->actionCompleteRegistration();
        }
        else
        {
            // request params
            $providerHandle = craft()->request->getParam('provider');
            $redirect = craft()->request->getParam('redirect');
            $errorRedirect = craft()->request->getParam('errorRedirect');
            $forcePrompt = craft()->request->getParam('forcePrompt');
            $requestUri = craft()->request->requestUri;
            $extraScopes = craft()->request->getParam('scopes');

            if(!$forcePrompt)
            {
                craft()->httpSession->add('social.requestUri', $requestUri);
            }

            // settings
            $plugin = craft()->plugins->getPlugin('social');
            $this->pluginSettings = $plugin->getSettings();

            try
            {
                if(!$this->pluginSettings['allowSocialLogin'])
                {
                    throw new Exception("Social login disabled");
                }

                if (craft()->getEdition() != Craft::Pro)
                {
                    throw new Exception("Craft Pro is required");
                }

                // provider scopes & params

                $scopes = craft()->social->getScopes($providerHandle);

                if($extraScopes)
                {
                    $extraScopes = unserialize(base64_decode(urldecode($extraScopes)));

                    // foreach($extraScopes as $k => $extraScope)
                    // {
                    //     $extraScopes[$k] = urldecode($extraScope);
                    // }
                    $scopes = array_merge($scopes, $extraScopes);
                }

                // var_dump($scopes);

                $params = craft()->social->getParams($providerHandle);

                if($forcePrompt)
                {
                    $params['approval_prompt'] = 'force';
                }

                if ($response = craft()->oauth->connect(array(
                    'plugin' => 'social',
                    'provider' => $providerHandle,
                    'redirect' => $redirect,
                    'errorRedirect' => $errorRedirect,
                    'scopes' => $scopes,
                    'params' => $params
                )))
                {
                    $this->_handleConnectResponse($providerHandle, $response);
                }
            }
            catch(\Exception $e)
            {
                craft()->httpSession->add('error', $e->getMessage());
                $this->redirect($redirect);
            }
        }
    }

    private function _handleConnectResponse($providerHandle, $response)
    {
        $errorRedirect = $response['errorRedirect'];

        try {

            if($response['success'])
            {
                $this->token = $response['token'];
                $this->tokenArray = craft()->oauth->tokenToArray($this->token);

                $plugin = craft()->plugins->getPlugin('social');

                if(!$this->pluginSettings['allowSocialLogin'])
                {
                    throw new Exception("Social login disabled");
                }

                // redirect url
                $this->redirect = $response['redirect'];

                // provider
                $this->provider = craft()->oauth->getProvider($providerHandle);
                $this->provider->source->setToken($this->token);

                // account
                $account = $this->provider->source->getAccount();

                // socialUid
                $this->socialUid = $account['uid'];

                // user
                $craftUser = craft()->userSession->getUser();

                if ($craftUser)
                {
                    $this->_handleLoggedInUser($craftUser);
                }
                else
                {
                    $this->_handleGuestUser();
                }

                // redirect
                $this->redirect($this->redirect);
            }
            else
            {
                throw new \Exception($response['errorMsg'], 1);
            }
        }
        catch(\Exception $e)
        {
            craft()->httpSession->add('error', $e->getMessage());

            $this->redirect($errorRedirect);
        }
    }

    private function _handleLoggedInUser($craftUser)
    {
        $socialUser = craft()->social->getUserByUid($this->provider->handle, $this->socialUid);

        if($socialUser)
        {
            if($craftUser->id == $socialUser->userId)
            {
                // save token

                $tokenId = $socialUser->tokenId;
                $tokenModel = craft()->oauth->getTokenById($tokenId);

                if(!$tokenModel)
                {
                    $tokenModel = new Oauth_TokenModel;
                }

                $tokenModel->providerHandle = $this->provider->handle;
                $tokenModel->pluginHandle = 'social';
                $tokenModel->encodedToken = craft()->oauth->encodeToken($this->token);
                craft()->oauth->saveToken($tokenModel);

                // save user
                $socialUser->tokenId = $tokenModel->id;
                craft()->social->saveUser($socialUser);
            }
            else
            {
                throw new Exception("UID is already associated with another user. Disconnect from your current session and retry.");
            }
        }
        else
        {
            // save token
            $tokenModel = new Oauth_TokenModel;
            $tokenModel->providerHandle = $this->provider->handle;
            $tokenModel->pluginHandle = 'social';
            $tokenModel->encodedToken = craft()->oauth->encodeToken($this->token);
            craft()->oauth->saveToken($tokenModel);

            // save social user
            $socialUser = new Social_UserModel;
            $socialUser->userId = $craftUser->id;
            $socialUser->provider = $this->provider->handle;
            $socialUser->socialUid = $this->socialUid;
            $socialUser->tokenId = $tokenModel->id;
            craft()->social->saveUser($socialUser);
        }
    }

    private function saveToken($tokenModel)
    {
        if($tokenModel->id)
        {
            // refresh

            if($tokenModel)
            {
                if($oldRefreshToken = $tokenModel->token->getRefreshToken())
                {
                    if(!empty($oldRefreshToken) && !$this->token->getRefreshToken())
                    {
                        // keep old refresh token
                        $this->token->setRefreshToken($oldRefreshToken);
                    }
                }
            }

            // // no refresh token ? reprompt

            if($this->provider->handle == 'google')
            {
                if($tokenModel)
                {
                    $newRefreshToken = $this->token->getRefreshToken();
                    $oldRefreshToken = $tokenModel->token->getRefreshToken();

                    try
                    {
                        $refreshToken = $this->provider->source->service->refreshAccessToken($this->token);
                        $canRefresh = true;
                    }
                    catch(\Exception $e)
                    {
                        $canRefresh = false;
                    }

                    if(!$canRefresh)
                    {
                        // new token doesn't have refresh token, prompt

                        $requestUri = craft()->httpSession->get('social.requestUri');

                        $this->redirect($requestUri.'&forcePrompt=true');
                    }
                }
            }
        }

        // save token
        $tokenModel->providerHandle = $this->provider->handle;
        $tokenModel->pluginHandle = 'social';
        $tokenModel->encodedToken = craft()->oauth->encodeToken($this->token);
        craft()->oauth->saveToken($tokenModel);
    }

    private function _handleGuestUser()
    {
        $socialUser = craft()->social->getUserByUid($this->provider->handle, $this->socialUid);

        if($socialUser)
        {
            $craftUser = craft()->users->getUserById($socialUser->userId);

            if($craftUser)
            {
                $tokenId = $socialUser->tokenId;
                $tokenModel = craft()->oauth->getTokenById($tokenId);

                if(!$tokenModel)
                {
                    $tokenModel = new Oauth_TokenModel;
                }

                $this->saveToken($tokenModel);

                // save user
                $socialUser->tokenId = $tokenModel->id;
                craft()->social->saveUser($socialUser);

                // login
                craft()->social_userSession->login($socialUser->id);
            }
            else
            {
                throw new Exception("Social User exists but craft user doesn't", 1);
            }
        }
        else
        {
            $attributes = $this->provider->source->getAccount();

            if(empty($attributes['email']) && $this->pluginSettings['requireEmailAddress'])
            {
                craft()->httpSession->add('socialToken', $this->tokenArray);
                craft()->httpSession->add('socialUser', $socialUser);
                craft()->httpSession->add('socialUid', $this->socialUid);
                craft()->httpSession->add('socialProviderHandle', $this->provider->handle);
                craft()->httpSession->add('socialRedirect', $this->redirect);

                $this->renderTemplate($this->pluginSettings['completeRegistrationTemplate']);

                return;
            }
            else
            {
                // get profile
                $socialProvider = craft()->social->getProvider($this->provider->class);
                $socialProvider->setToken($this->token);
                $profile = $socialProvider->getProfile();

                // fill attributes from profile
                $this->_fillAttributesFromProfile($attributes, $profile);

                // register user
                $craftUser = $this->_registerUser($attributes);

                if($craftUser)
                {
                    // save token
                    $tokenModel = new Oauth_TokenModel;
                    $tokenModel->providerHandle = $this->provider->handle;
                    $tokenModel->pluginHandle = 'social';
                    $tokenModel->encodedToken = craft()->oauth->encodeToken($this->token);
                    craft()->oauth->saveToken($tokenModel);

                    // save social user
                    $socialUser = new Social_UserModel;
                    $socialUser->userId = $craftUser->id;
                    $socialUser->provider = $this->provider->handle;
                    $socialUser->socialUid = $this->socialUid;
                    $socialUser->tokenId = $tokenModel->id;
                    craft()->social->saveUser($socialUser);

                    // login
                    craft()->social_userSession->login($socialUser->id);
                }
                else
                {
                    throw new Exception("Craft user couldn’t be created.");
                }
            }
        }
    }

    public function actionCompleteRegistration()
    {
        $tokenArray = craft()->httpSession->get('socialToken');
        $this->token = craft()->oauth->arrayToToken($tokenArray);

        $socialUserId = craft()->httpSession->get('socialUserId');
        $providerHandle = craft()->httpSession->get('socialProviderHandle');
        $redirect = craft()->httpSession->get('socialRedirect');
        $this->socialUid = craft()->httpSession->get('socialUid');
        $email = craft()->request->getPost('email');

        // settings
        $plugin = craft()->plugins->getPlugin('social');
        $this->pluginSettings = $plugin->getSettings();

        // provider
        $this->provider = craft()->oauth->getProvider($providerHandle);
        $this->provider->source->setToken($this->token);

        // account
        $account = $this->provider->source->getAccount();

        // attributes
        $attributes = array();
        $attributes['email'] = $email;

        $completeRegistration = new Social_CompleteRegistrationModel;
        $completeRegistration->email = $email;

        if($completeRegistration->validate())
        {
            $emailExists = craft()->users->getUserByUsernameOrEmail($email);

            if(!$emailExists)
            {
                // get profile
                $socialProvider = craft()->social->getProvider($this->provider->class);
                $socialProvider->setToken($this->token);
                $profile = $socialProvider->getProfile();

                // fill attributes from profile
                $this->_fillAttributesFromProfile($attributes, $profile);

                // register user
                $craftUser = $this->_registerUser($attributes);

                if($craftUser)
                {
                    // save token
                    $tokenModel = new Oauth_TokenModel;
                    $tokenModel->providerHandle = $this->provider->handle;
                    $tokenModel->pluginHandle = 'social';
                    $tokenModel->encodedToken = craft()->oauth->encodeToken($this->token);
                    craft()->oauth->saveToken($tokenModel);

                    // save social user
                    $socialUser = new Social_UserModel;
                    $socialUser->userId = $craftUser->id;
                    $socialUser->provider = $this->provider->handle;
                    $socialUser->socialUid = $this->socialUid;
                    $socialUser->tokenId = $tokenModel->id;
                    craft()->social->saveUser($socialUser);

                    // login
                    craft()->social_userSession->login($socialUser->id);

                    // redirect
                    $this->redirect($redirect);
                }
                else
                {
                    throw new Exception("Craft user couldn’t be created.");
                }
            }
            else
            {
                $completeRegistration->addError('email', 'Email already in use by another user.');
            }
        }

        $this->renderTemplate($this->pluginSettings['completeRegistrationTemplate'], array(
            'completeRegistration' => $completeRegistration
        ));

        // Send the account back to the template
        craft()->urlManager->setRouteVariables(array(
            'completeRegistration' => $completeRegistration
        ));
    }

    private function _fillAttributesFromProfile(&$attributes, $profile)
    {
        $plugin = craft()->plugins->getPlugin('social');
        $settings = $plugin->getSettings();

        if($settings->autoFillProfile)
        {
            if(!empty($profile['firstName']))
            {
                $attributes['firstName'] = $profile['firstName'];
            }

            if(!empty($profile['lastName']))
            {
                $attributes['lastName'] = $profile['lastName'];
            }

            if(!empty($profile['photo']))
            {
                $attributes['photo'] = $profile['photo'];
            }
        }
    }

    private function _registerUser($attributes)
    {
        $temporaryPassword = md5(time());

        $attributes['newPassword'] = $temporaryPassword;

        if(!empty($attributes['email']))
        {
            // find with email
            $user = craft()->users->getUserByUsernameOrEmail($attributes['email']);

            if(!$user)
            {
                $user = craft()->social->registerUser($attributes);

                if($user)
                {
                    $socialAccount = new Social_AccountModel;
                    $socialAccount->userId = $user->id;
                    $socialAccount->hasEmail = true;
                    $socialAccount->hasPassword = false;
                    $socialAccount->temporaryPassword = $temporaryPassword;

                    craft()->social->saveAccount($socialAccount);
                }
            }
            else
            {
                throw new \Exception("An account already exists with this email: ".$attributes['email']);
            }
        }
        else
        {
            // no email at this point ? create a fake one

            $attributes['email'] = strtolower($this->provider->handle).'.'.$attributes['uid'].'@example.com';

            $user = craft()->social->registerUser($attributes);

            if($user)
            {
                $socialAccount = new Social_AccountModel;
                $socialAccount->userId = $user->id;
                $socialAccount->hasEmail = false;
                $socialAccount->hasPassword = false;
                $socialAccount->temporaryEmail = $user->email;
                $socialAccount->temporaryPassword = $temporaryPassword;

                craft()->social->saveAccount($socialAccount);
            }

            // if($this->pluginSettings['allowFakeEmail'])
            // {
            // }
            // else
            // {
            //     throw new \Exception("Email address not provided.");
            // }
        }

        // todo
        // security risks matching existing user email for registration:
        // craft and oauth providers must enable user email
        // confirmation to secure the system

        // if(!empty($attributes['email']))
        // {
        //     // find with email

        //     $user = craft()->users->getUserByUsernameOrEmail($attributes['email']);

        //     if(!$user)
        //     {
        //         $user = craft()->social->registerUser($attributes);
        //     }
        // }
        // else
        // {
        //     $user = craft()->social->registerUser($attributes);
        // }

        return $user;
    }
}
