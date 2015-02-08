<?php

/**
 * Social plugin for Craft
 *
 * @package   Craft Social
 * @author    Benjamin David
 * @copyright Copyright (c) 2015, Dukt
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

    public function actionUsers()
    {
        $socialUsers = craft()->social->getUsers();

        $this->renderTemplate('social/users', array(
                'socialUsers' => $socialUsers
            ));
    }

    public function actionSettings()
    {
        $plugin = craft()->plugins->getPlugin('social');
        $settings = $plugin->getSettings();

        $this->renderTemplate('social/settings', array(
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
        craft()->social->requireOAuth();

        $handle = craft()->request->getParam('provider');

        // delete token and social user
        craft()->social->deleteUserByProvider($handle);

        // redirect
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
        craft()->social->requireOAuth();

        // order

        $routeParams = craft()->urlManager->getRouteParams();

        $socialUserId = $routeParams['variables']['id'];

        $socialUser = craft()->social->getSocialUserById($socialUserId);

        $variables = array(
            'socialUser' => $socialUser
        );

        $this->renderTemplate('social/users/_profile', $variables);
    }

    public function actionConnect()
    {
        $this->actionLogin();
    }

    public function actionLogin()
    {
        craft()->social->requireOAuth();

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

                    $scopes = array_merge($scopes, $extraScopes);
                }

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
                craft()->userSession->setFlash('error', $e->getMessage());
                $this->redirect($redirect);
            }
        }
    }

    public function actionCompleteRegistration()
    {
        craft()->social->requireOAuth();

        $this->token = craft()->httpSession->get('socialToken');

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
                    $this->token->providerHandle = $this->provider->getHandle();
                    $this->token->pluginHandle = 'social';
                    craft()->oauth->saveToken($this->token);

                    // save social user
                    $socialUser = new Social_UserModel;
                    $socialUser->userId = $craftUser->id;
                    $socialUser->provider = $this->provider->getHandle();
                    $socialUser->socialUid = $this->socialUid;
                    $socialUser->tokenId = $this->token->id;
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

        if(!empty($this->pluginSettings['completeRegistrationTemplate']))
        {
            if(!craft()->templates->doesTemplateExist($this->pluginSettings['completeRegistrationTemplate']))
            {
                throw new Exception("Complete registration template not set");
            }
        }
        else
        {
            throw new Exception("Complete registration template not set");
        }

        $this->renderTemplate($this->pluginSettings['completeRegistrationTemplate'], array(
            'completeRegistration' => $completeRegistration
        ));

        // Send the account back to the template
        craft()->urlManager->setRouteVariables(array(
            'completeRegistration' => $completeRegistration
        ));
    }

    private function _handleConnectResponse($providerHandle, $response)
    {
        $errorRedirect = $response['errorRedirect'];

        try
        {
            if($response['success'])
            {
                $this->token = $response['token'];

                $plugin = craft()->plugins->getPlugin('social');

                if(!$this->pluginSettings['allowSocialLogin'])
                {
                    throw new Exception("Social login disabled");
                }

                // redirect url
                $this->redirect = $response['redirect'];

                // provider
                $this->provider = craft()->oauth->getProvider($providerHandle);
                $this->provider->setToken($this->token);

                // account
                $account = $this->provider->getAccount();

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
            craft()->userSession->setFlash('error', $e->getMessage());
            $this->redirect($errorRedirect);
        }
    }

    private function _handleLoggedInUser($craftUser)
    {
        $socialUser = craft()->social->getUserByUid($this->provider->getHandle(), $this->socialUid);

        if($socialUser)
        {
            if($craftUser->id == $socialUser->userId)
            {
                // save token

                $tokenId = $socialUser->tokenId;
                $this->token = craft()->oauth->getTokenById($tokenId);

                if(!$this->token)
                {
                    $this->token = new Oauth_TokenModel;
                }

                $this->token->providerHandle = $this->provider->getHandle();
                $this->token->pluginHandle = 'social';

                craft()->oauth->saveToken($this->token);

                // save user
                $socialUser->tokenId = $this->token->id;
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

            $this->token->providerHandle = $this->provider->getHandle();
            $this->token->pluginHandle = 'social';

            craft()->oauth->saveToken($this->token);

            // save social user
            $socialUser = new Social_UserModel;
            $socialUser->userId = $craftUser->id;
            $socialUser->provider = $this->provider->getHandle();
            $socialUser->socialUid = $this->socialUid;
            $socialUser->tokenId = $this->token->id;
            craft()->social->saveUser($socialUser);
        }
    }

    private function saveToken($tokenModel)
    {

        // Todo: Reimplement refresh token reprompt

        // if($tokenModel->id)
        // {
        //     // refresh

        //     if($tokenModel)
        //     {
        //         if($oldRefreshToken = $tokenModel->refreshToken)
        //         {
        //             if(!empty($oldRefreshToken) && !$this->refreshToken)
        //             {
        //                 // keep old refresh token
        //                 $this->token->setRefreshToken($oldRefreshToken);
        //             }
        //         }
        //     }

        //     // no refresh token ? reprompt

        //     if($this->provider->getHandle() == 'google')
        //     {
        //         if($tokenModel)
        //         {
        //             $newRefreshToken = $this->token->refreshToken;
        //             $oldRefreshToken = $tokenModel->refreshToken;

        //             try
        //             {
        //                 $refreshToken = $this->provider->refreshAccessToken($this->token);
        //                 $canRefresh = true;
        //             }
        //             catch(\Exception $e)
        //             {
        //                 $canRefresh = false;
        //             }

        //             if(!$canRefresh)
        //             {
        //                 // new token doesn't have refresh token, prompt

        //                 $requestUri = craft()->httpSession->get('social.requestUri');

        //                 $this->redirect($requestUri.'&forcePrompt=true');
        //             }
        //         }
        //     }
        // }

        // save token
        $tokenModel->providerHandle = $this->provider->getHandle();
        $tokenModel->pluginHandle = 'social';

        craft()->oauth->saveToken($tokenModel);
    }

    private function _handleGuestUser()
    {
        $socialUser = craft()->social->getUserByUid($this->provider->getHandle(), $this->socialUid);

        if($socialUser)
        {
            $craftUser = craft()->users->getUserById($socialUser->userId);

            if($craftUser)
            {
                $tokenId = $socialUser->tokenId;
                $existingToken = craft()->oauth->getTokenById($tokenId);

                if($existingToken)
                {
                    $this->token->id = $tokenId;
                }

                $this->token->providerHandle = $this->provider->getHandle();
                $this->token->pluginHandle = 'social';

                $this->saveToken($this->token);

                // save user
                $socialUser->tokenId = $this->token->id;
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
            $attributes = $this->provider->getAccount();

            if(empty($attributes['email']) && $this->pluginSettings['requireEmailAddress'])
            {
                craft()->httpSession->add('socialToken', $this->token);
                craft()->httpSession->add('socialUser', $socialUser);
                craft()->httpSession->add('socialUid', $this->socialUid);
                craft()->httpSession->add('socialProviderHandle', $this->provider->getHandle());
                craft()->httpSession->add('socialRedirect', $this->redirect);

                if(!empty($this->pluginSettings['completeRegistrationTemplate']))
                {
                    if(!craft()->templates->doesTemplateExist($this->pluginSettings['completeRegistrationTemplate']))
                    {
                        throw new Exception("Complete registration template not set");
                    }
                }
                else
                {
                    throw new Exception("Complete registration template not set");
                }

                $this->renderTemplate($this->pluginSettings['completeRegistrationTemplate']);

                return;
            }
            else
            {
                // get profile

                $socialProvider = craft()->social->getProvider($this->provider->getClass());
                $socialProvider->setToken($this->token);
                $profile = $socialProvider->getProfile();

                // fill attributes from profile
                $this->_fillAttributesFromProfile($attributes, $profile);

                // register user
                $craftUser = $this->_registerUser($attributes);

                if($craftUser)
                {
                    // save token
                    $this->token->providerHandle = $this->provider->getHandle();
                    $this->token->pluginHandle = 'social';

                    craft()->oauth->saveToken($this->token);

                    // save social user
                    $socialUser = new Social_UserModel;
                    $socialUser->userId = $craftUser->id;
                    $socialUser->provider = $this->provider->getHandle();
                    $socialUser->socialUid = $this->socialUid;
                    $socialUser->tokenId = $this->token->id;
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
                if(craft()->config->get('allowEmailMatch', 'social') !== true)
                {
                    throw new \Exception("An account already exists with this email: ".$attributes['email']);
                }
            }
        }
        else
        {
            // no email at this point ? create a fake one

            $attributes['email'] = strtolower($this->provider->getHandle()).'.'.$attributes['uid'].'@example.com';

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
        }

        return $user;
    }
}
