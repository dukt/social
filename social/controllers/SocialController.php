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


require_once(CRAFT_PLUGINS_PATH.'oauth/vendor/autoload.php');
require_once(CRAFT_PLUGINS_PATH.'oauth/providers/BaseOAuthProviderSource.php');


use Guzzle\Http\Client;

class SocialController extends BaseController
{
    public $allowAnonymous = true;

    private $provider;
    private $pluginSettings;
    private $socialUid;
    private $redirect;

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
        if(craft()->request->getPost('action') == 'social/askEmail')
        {
            $this->actionAskEmail();
        }
        else
        {
            // request params
            $providerHandle = craft()->request->getParam('provider');
            $redirect = craft()->request->getParam('redirect');
            $errorRedirect = craft()->request->getParam('errorRedirect');

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
                $params = craft()->social->getParams($providerHandle);

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
            $token = $response['token'];

            if($response['success'])
            {
                $plugin = craft()->plugins->getPlugin('social');

                if(!$this->pluginSettings['allowSocialLogin'])
                {
                    throw new Exception("Social login disabled");
                }

                // redirect url
                $this->redirect = $response['redirect'];

                // provider
                $this->provider = craft()->oauth->getProvider($providerHandle);
                $this->provider->source->setToken($token);

                // account
                $account = $this->provider->source->getAccount();

                // socialUid
                $this->socialUid = $account['uid'];

                // user
                $craftUser = craft()->userSession->getUser();

                if ($craftUser)
                {
                    $this->_handleLoggedInUser($token, $craftUser);
                }
                else
                {
                    $this->_handleGuestUser($token);
                }

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

    private function _handleLoggedInUser($token, $craftUser)
    {
        // social user
        $socialUser = craft()->social->getUserByUid($this->provider->handle, $this->socialUid);

        if($socialUser)
        {
            if($craftUser->id == $socialUser->userId)
            {
                // token model
                $tokenModel = $this->_getTokenById($socialUser->tokenId, $token);

                // save token
                $this->_saveToken($tokenModel, $token);

                // save socialUser
                $this->_saveSocialUser($socialUser, $craftUser, $tokenModel);
            }
            else
            {
                throw new Exception("UID is already associated with another user. Disconnect from your current session and retry.");
            }
        }
        else
        {
            // social user doesn't exist
            $socialUser = new Social_UserModel();
            $tokenModel = new Oauth_TokenModel;

            $this->_saveToken($tokenModel, $token);
            $this->_saveSocialUser($socialUser, $craftUser, $tokenModel);
        }
    }

    private function _handleGuestUser($token)
    {
        // social user
        $socialUser = craft()->social->getUserByUid($this->provider->handle, $this->socialUid);

        if($socialUser)
        {
            $craftUser = craft()->users->getUserById($socialUser->userId);

            if($craftUser)
            {
                // token model
                $tokenModel = $this->_getTokenById($socialUser->tokenId, $token);

                // save token
                $this->_saveToken($tokenModel, $token);

                // save socialUser
                $this->_saveSocialUser($socialUser, $craftUser, $tokenModel);

                // login
                craft()->social_userSession->login($tokenModel);
            }
            else
            {
                $this->_createUser($token, $socialUser);
            }
        }
        else
        {
            // social user doesn't exist
            $socialUser = new Social_UserModel();

            $this->_createUser($token, $socialUser);
        }
    }

    public function actionAskEmail()
    {
        $token = craft()->httpSession->get('socialToken');
        $socialUser = craft()->httpSession->get('socialUser');
        $providerHandle = craft()->httpSession->get('socialProviderHandle');
        $redirect = craft()->httpSession->get('socialRedirect');
        $this->socialUid = craft()->httpSession->get('socialUid');
        $email = craft()->request->getPost('email');

        // settings
        $plugin = craft()->plugins->getPlugin('social');
        $this->pluginSettings = $plugin->getSettings();

        // provider
        $this->provider = craft()->oauth->getProvider($providerHandle);
        $this->provider->source->setToken($token);

        // account
        $account = $this->provider->source->getAccount();

        // attributes
        $attributes = array();
        $attributes['email'] = $email;

        $askEmail = new Social_AskEmailModel;
        $askEmail->email = $email;

        // var_dump($redirect);

        if($askEmail->validate())
        {
            $emailExists = craft()->users->getUserByUsernameOrEmail($email);

            if(!$emailExists)
            {
                // create user
                $craftUser = $this->_registerUser($attributes);

                if($craftUser)
                {
                    // token model
                    $tokenModel = new Oauth_TokenModel;

                    // save token
                    $this->_saveToken($tokenModel, $token);

                    // save socialUser
                    $this->_saveSocialUser($socialUser, $craftUser, $tokenModel);

                    // login
                    craft()->social_userSession->login($tokenModel);

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
                $askEmail->addError('email', 'Email already in use by another user.');
            }
        }

        $this->renderTemplate($this->pluginSettings['askEmailTemplate'], array(
            'askEmail' => $askEmail
        ));

        // Send the account back to the template
        craft()->urlManager->setRouteVariables(array(
            'askEmail' => $askEmail
        ));
    }

    private function _createUser($token, $socialUser)
    {
        $attributes = $this->provider->source->getAccount();

        if(empty($attributes['email']) && $this->pluginSettings['requireEmailAddress'])
        {
            craft()->httpSession->add('socialToken', $token);
            craft()->httpSession->add('socialUser', $socialUser);
            craft()->httpSession->add('socialUid', $this->socialUid);
            craft()->httpSession->add('socialProviderHandle', $this->provider->handle);
            craft()->httpSession->add('socialRedirect', $this->redirect);

            $this->renderTemplate($this->pluginSettings['askEmailTemplate']);
            // $this->actionAskEmail();
            return;
        }

        // fill account from profile

        $socialProvider = craft()->social->getSocialProvider($this->provider->class);
        $socialProvider->setToken($token);
        $profile = $socialProvider->getProfile();

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


        // create user
        $craftUser = $this->_registerUser($attributes);

        if($craftUser)
        {
            // token model
            $tokenModel = $this->_getTokenById($socialUser->tokenId, $token);

            // save token
            $this->_saveToken($tokenModel, $token);

            // save socialUser
            $this->_saveSocialUser($socialUser, $craftUser, $tokenModel);

            // login
            craft()->social_userSession->login($tokenModel);
        }
        else
        {
            throw new Exception("Craft user couldn’t be created.");
        }
    }

    private function _saveToken(&$tokenModel, $token)
    {
        // save token

        $tokenModel->providerHandle = $this->provider->handle;
        $tokenModel->pluginHandle = 'social';
        $tokenModel->encodedToken = craft()->oauth->encodeToken($token);

        craft()->oauth->saveToken($tokenModel);
    }

    private function _saveSocialUser($socialUser, $craftUser, $tokenModel)
    {
        // save social user

        $socialUser->userId = $craftUser->id;
        $socialUser->provider = $this->provider->handle;
        $socialUser->socialUid = $this->socialUid;
        $socialUser->tokenId = $tokenModel->id;

        if(!craft()->social->saveUser($socialUser))
        {
            var_dump($socialUser);
            throw new \Exception("Could not save social user", 1);

        }
    }

    private function _getTokenById($tokenId, $token)
    {
        if($tokenId)
        {
            $tokenModel = craft()->oauth->getTokenById($tokenId);

            if($tokenModel)
            {
                $oldToken = $tokenModel->getToken();

                if(!$token->getRefreshToken() && method_exists($this->provider->source->service, 'refreshAccessToken'))
                {
                    $refreshToken = $oldToken->getRefreshToken();
                    $token->setRefreshToken($refreshToken);
                }
            }
            else
            {
                $tokenModel = new Oauth_TokenModel;
            }
        }
        else
        {
            $tokenModel = new Oauth_TokenModel;
        }

        return $tokenModel;
    }

    private function _registerUser($attributes)
    {
        if(!empty($attributes['email']))
        {
            // find with email
            $user = craft()->users->getUserByUsernameOrEmail($attributes['email']);

            if(!$user)
            {
                $user = craft()->social->registerUser($attributes);
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
