<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\controllers;

use Craft;
use craft\web\Controller;
use dukt\social\web\assets\social\SocialAsset;
use dukt\social\Plugin as Social;
use yii\web\HttpException;
use dukt\social\models\Token;
use dukt\social\elements\LoginAccount;
use Exception;

class LoginAccountsController extends Controller
{
    // Properties
    // =========================================================================

    /**
     * @inheritdoc
     *
     * @var array
     */
    protected $allowAnonymous = ['actionLogin'];

    /**
     * Redirect URL
     *
     * @var string
     */
    private $redirect;

    /**
     * Referer URL
     *
     * @var string
     */
    private $referer;

    // Public Methods
    // =========================================================================

    /**
     * Login Accounts Index
     *
     * @return null
     */
    public function actionIndex()
    {
        Craft::$app->getView()->registerAssetBundle(SocialAsset::class);

        return $this->renderTemplate('social/loginaccounts/_index');
    }

    /**
     * Edit User's Login Accounts
     *
     * @param array $variable Route variables
     *
     * @throws HttpException
     * @return null
     */
    public function actionEdit($userId)
    {
        $user = Craft::$app->users->getUserById($userId);

        if ($user)
        {
            $loginAccounts = Social::$plugin->getLoginAccounts()->getLoginAccountsByUserId($user->id);

            Craft::$app->getView()->registerAssetBundle(SocialAsset::class);

            return $this->renderTemplate('social/loginaccounts/_edit', [
                'userId' => $userId,
                'user' => $user,
                'loginAccounts' => $loginAccounts
            ]);
        }
        else
        {
            throw new HttpException(404);
        }
    }

    /**
     * Delete Login Account
     *
     * @return null
     */
    public function actionDeleteLoginAccount()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $loginAccountId = Craft::$app->getRequest()->getRequiredBodyParam('id');

        Social::$plugin->getLoginAccounts()->deleteLoginAccountById($loginAccountId);

        return $this->asJson(array('success' => true));
    }

    /**
     * Login
     *
     * @return null
     */
    public function actionLogin()
    {
        Craft::$app->getSession()->set('social.loginReferrer', Craft::$app->getRequest()->getAbsoluteUrl());

        $this->referer = Craft::$app->getSession()->get('social.referer');

        if (!$this->referer)
        {
            $this->referer = Craft::$app->getRequest()->referrer;
            Craft::$app->getSession()->set('social.referer', $this->referer);
        }

        $this->redirect = Craft::$app->getRequest()->getParam('redirect');


        // Connect

        // Request params
        $providerHandle = Craft::$app->getRequest()->getParam('provider');

        // Settings
        $plugin = Craft::$app->getPlugins()->getPlugin('social');
        $pluginSettings = $plugin->getSettings();


        // Try to connect

        /*
        try
        {
        */

        if (!$pluginSettings['enableSocialLogin'])
        {
            throw new Exception("Social login is disabled");
        }

        if (Craft::$app->getEdition() != Craft::Pro)
        {
            throw new Exception("Craft Pro is required");
        }

        $loginProvider = Social::$plugin->getLoginProviders()->getLoginProvider($providerHandle);

        if (!$loginProvider)
        {
            throw new Exception("Login provider is not configured");
        }

        if ($response = Social::$plugin->getOauth()->connect([
            'loginProviderHandle' => $providerHandle,
            'scope'   => $loginProvider->getScope(),
            'authorizationOptions'   => $loginProvider->getAuthorizationOptions()
        ]))
        {
            if($response && is_object($response) && !$response->data)
            {
                return $response;
            }

            if($response['success'])
            {
                $token = new Token();
                $token->providerHandle = $providerHandle;
                $token->token = $response['token'];

                return $this->_connectUserFromToken($token);
            }
            else
            {
                throw new \Exception($response['errorMsg']);
            }
        }

        /*
        }
        catch(\Guzzle\Http\Exception\BadResponseException $e)
        {
            $response = $e->getResponse();

            // Social::log((string) $response, LogLevel::Error);

            $body = $response->getBody();
            $json = json_decode($body, true);

            if($json)
            {
                $errorMsg = $json['error']['message'];
            }
            else
            {
                $errorMsg = "Couldn’t login.";
            }

            Craft::$app->getSession()->setFlash('error', $errorMsg);
            $this->_cleanSession();
            return $this->redirect($this->referer);
        }
        catch (\Exception $e)
        {
            $errorMsg = $e->getMessage();
            Craft::$app->getSession()->setFlash('error', $errorMsg);
            $this->_cleanSession();
            return $this->redirect($this->referer);
        }
        */
    }

    /**
     * Logout
     *
     * @return null
     */
    public function actionLogout()
    {
        Craft::$app->getUser()->logout(false);

        $redirect = Craft::$app->getRequest()->getParam('redirect');

        if (!$redirect)
        {
            $redirect = Craft::$app->getRequest()->referrer;
        }

        return $this->redirect($redirect);
    }

    /**
     * Connect a login account (link)
     *
     * @return null
     */
    public function actionConnectLoginAccount()
    {
        return $this->actionLogin();
    }

    /**
     * Disconnect a login account (unlink)
     *
     * @return null
     */
    public function actionDisconnectLoginAccount()
    {
        $handle = Craft::$app->getRequest()->getParam('provider');

        // delete social user
        Social::$plugin->getLoginAccounts()->deleteLoginAccountByProvider($handle);

        Craft::$app->getSession()->setNotice(Craft::t('app', 'Login account disconnected.'));

        // redirect
        $redirect = Craft::$app->getRequest()->referrer;
        return $this->redirect($redirect);
    }

    /**
     * Change Photo
     *
     * @return null
     */
    public function actionChangePhoto()
    {
        $userId = Craft::$app->getRequest()->getParam('userId');
        $photoUrl = Craft::$app->getRequest()->getParam('photoUrl');

        $user = Craft::$app->users->getUserById($userId);

        Social::$plugin->getLoginAccounts()->saveRemotePhoto($photoUrl, $user);

        // redirect
        $referrer = Craft::$app->getRequest()->referrer;
        return $this->redirect($referrer);
    }

    // Private Methods
    // =========================================================================

    /**
     * Connect (register, login, link) a user from token
     *
     * @param Token $token
     */
    private function _connectUserFromToken(Token $token)
    {
        $craftUser = Craft::$app->getUser()->getIdentity();

        if ($craftUser)
        {
            return $this->_linkAccountFromToken($token, $craftUser);
        }
        else
        {
            return $this->_registerOrLoginFromToken($token);
        }
    }

    /**
     * Link account from token
     *
     * @param object $craftUser The logged-in user object
     *
     * @throws Exception
     * @return null
     */
    private function _linkAccountFromToken(Token $token, $craftUser)
    {
        $this->_cleanSession();

        if (!$this->redirect)
        {
            $this->redirect = $this->referer;
        }

        $socialLoginProvider = Social::$plugin->getLoginProviders()->getLoginProvider($token->providerHandle);

        $attributes = $socialLoginProvider->getProfile($token);

        $socialUid = $attributes['id'];

        $account = Social::$plugin->getLoginAccounts()->getLoginAccountByUid($socialLoginProvider->getHandle(), $socialUid);

        if ($account)
        {
            if ($craftUser->id == $account->userId)
            {
                // Social::$plugin->getLoginAccounts()->saveLoginAccount($account);
                Craft::$app->elements->saveElement($account);

                Craft::$app->getSession()->setNotice(Craft::t('app', 'Login account added.'));

                return $this->redirect($this->redirect);
            }
            else
            {
                throw new Exception("This UID is already associated with another user. Disconnect from your current session and retry.");
            }
        }
        else
        {
            // save social user
            $account = new LoginAccount;
            $account->userId = $craftUser->id;
            $account->providerHandle = $socialLoginProvider->getHandle();
            $account->socialUid = $socialUid;

            // Social::$plugin->getLoginAccounts()->saveLoginAccount($account);

            Craft::$app->getElements()->saveElement($account);

            Craft::$app->getSession()->setNotice(Craft::t('app', 'Login account added.'));

            return $this->redirect($this->redirect);
        }
    }

    /**
     * Register or login user from an OAuth token
     *
     * @throws Exception
     * @return null
     */
    private function _registerOrLoginFromToken(Token $token)
    {
        $socialLoginProvider = Social::$plugin->getLoginProviders()->getLoginProvider($token->providerHandle);

        $attributes = $socialLoginProvider->getProfile($token);

        $socialUid = $attributes['id'];

        $account = Social::$plugin->getLoginAccounts()->getLoginAccountByUid($socialLoginProvider->getHandle(), $socialUid);

        if ($account)
        {
            $craftUser = Craft::$app->users->getUserById($account->userId);

            if ($craftUser)
            {
                // save user
                // Social::$plugin->getLoginAccounts()->saveLoginAccount($account);
                Craft::$app->elements->saveElement($account);

                // login
                return $this->_login($craftUser, $account, $token);
            }
            else
            {
                throw new Exception("Social account exists but Craft user doesn't");
            }
        }
        else
        {
            // Register user
            $craftUser = Social::$plugin->getLoginAccounts()->registerUser($attributes, $socialLoginProvider->getHandle());

            if ($craftUser)
            {
                // Save social user
                $account = new LoginAccount;
                $account->userId = $craftUser->id;
                $account->providerHandle = $socialLoginProvider->getHandle();
                $account->socialUid = $socialUid;
                // Social::$plugin->getLoginAccounts()->saveLoginAccount($account);

                Craft::$app->elements->saveElement($account);

                // Login
                return $this->_login($craftUser, $account, $token, true);
            }
            else
            {
                throw new Exception("Craft user couldn’t be created.");
            }
        }
    }

    /**
     * Login user from login account
     *
     * @return null
     */
    private function _login(\craft\elements\User $craftUser, LoginAccount $account, Token $token, $registrationMode = false)
    {
        $this->_cleanSession();

        if (!$this->redirect)
        {
            $this->redirect = $this->referer;
        }

        if(!$account->authenticate($token))
        {
            throw new Exception("Coudln’t authenticate account.");
        }

        if (Craft::$app->getUser()->login($craftUser))
        {
            if ($registrationMode)
            {
                Craft::$app->getSession()->setNotice(Craft::t('app', 'Account created.'));
            }
            else
            {
                Craft::$app->getSession()->setNotice(Craft::t('app', 'Logged in.'));
            }

            return $this->redirect($this->redirect);
        }
        else
        {
            $errorCode = Social::$plugin->getUserSession()->getLoginErrorCode();
            $errorMessage = Social::$plugin->getUserSession()->getLoginErrorMessage($errorCode, $account->user->username);

            Craft::$app->getSession()->setError($errorMessage);

            return $this->redirect($this->referer);
        }
    }

    /**
     * Clean session variables
     *
     * @return null
     */
    private function _cleanSession()
    {
        Craft::$app->getSession()->remove('social.referer');
        // Craft::$app->getSession()->remove('social.requestUri');
    }
}
