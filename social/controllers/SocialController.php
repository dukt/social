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

        // don't go further if social login disabled
        $plugin = craft()->plugins->getPlugin('social');
        $settings = $plugin->getSettings();

        try
        {
            if(!$settings['allowSocialLogin'])
            {
                throw new Exception("Social login disabled");
            }

            if (craft()->getEdition() != Craft::Pro)
            {
                throw new Exception("Craft Pro is required");
            }

            // provider scopes & params
            $scopes = craft()->social->getScopes($handle);
            $params = craft()->social->getParams($handle);

            if ($response = craft()->oauth->connect(array(
                'plugin' => 'social',
                'provider' => $handle,
                'redirect' => $redirect,
                'errorRedirect' => $errorRedirect,
                'scopes' => $scopes,
                'params' => $params
            )))
            {
                $errorRedirect = $response['errorRedirect'];

                if($response['success'])
                {
                    $plugin = craft()->plugins->getPlugin('social');
                    $settings = $plugin->getSettings();

                    if(!$settings['allowSocialLogin'])
                    {
                        throw new Exception("Social login disabled");
                    }

                    $provider = craft()->oauth->getProvider($handle);
                    $token = $response['token'];

                    // current user
                    $user = craft()->userSession->getUser();

                    // logged in ?
                    $isLoggedIn = false;

                    if($user)
                    {
                        $isLoggedIn = true;
                    }

                    // retrieve social user from uid
                    $provider->source->setToken($token);
                    $account = $provider->source->getAccount();
                    $socialUser = craft()->social->getUserByUid($provider->handle, $account['uid']);

                    // error if uid is associated with a different user
                    if($user && $socialUser && $user->id != $socialUser->userId)
                    {
                        throw new Exception("UID is already associated with another user. Disconnect from your current session and retry.");
                    }


                    if(!$user && $socialUser)
                    {
                        $user = craft()->users->getUserById($socialUser->userId);
                    }


                    // create user if it doesn't exists
                    if(!$user)
                    {
                        // $user = craft()->social->registerUser($account);

                        if(!empty($account['email']))
                        {
                            // find with email
                            $user = craft()->users->getUserByUsernameOrEmail($account['email']);

                            if(!$user)
                            {
                                $user = craft()->social->registerUser($account);
                            }
                            else
                            {
                                throw new \Exception("An account already exists with this email");
                            }
                        }
                        else
                        {
                            throw new \Exception("Email address not provided");
                        }

                        // todo
                        // security risks matching existing user email for registration:
                        // craft and oauth providers must enable user email
                        // confirmation to secure the system

                        // if(!empty($account['email']))
                        // {
                        //     // find with email

                        //     $user = craft()->users->getUserByUsernameOrEmail($account['email']);

                        //     if(!$user)
                        //     {
                        //         $user = craft()->social->registerUser($account);
                        //     }
                        // }
                        // else
                        // {
                        //     $user = craft()->social->registerUser($account);
                        // }

                    }


                    // save social user

                    $tokenId = null;
                    $tokenModel = null;

                    if(!$socialUser)
                    {
                        $socialUser = new Social_UserModel();
                    }
                    else
                    {
                        // token
                        $tokenId = $socialUser->tokenId;
                    }


                    if($tokenId)
                    {
                        $tokenModel = craft()->oauth->getTokenById($tokenId);
                    }

                    if(!$tokenModel)
                    {
                        $tokenModel = new Oauth_TokenModel;
                    }

                    $tokenModel->providerHandle = $handle;
                    $tokenModel->pluginHandle = 'social';
                    $tokenModel->encodedToken = craft()->oauth->encodeToken($token);

                    // save token
                    craft()->oauth->saveToken($tokenModel);

                    // set token ID
                    $tokenId = $tokenModel->id;


                    $socialUser->userId = $user->id;
                    $socialUser->provider = $provider->handle;
                    $socialUser->socialUid = $account['uid'];
                    $socialUser->tokenId = $tokenId;

                    craft()->social->saveUser($socialUser);


                    // login if not logged in
                    if(!$isLoggedIn)
                    {
                        craft()->social_userSession->login($tokenModel);
                    }

                    $this->redirect($response['redirect']);
                }
                else
                {
                    throw new \Exception($response['errorMsg'], 1);
                }
            }
        }
        catch(\Exception $e)
        {
            craft()->httpSession->add('error', $e->getMessage());
            $this->redirect($errorRedirect);
        }
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
