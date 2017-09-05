<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/social/docs/license
 */

namespace dukt\social\controllers;

use Craft;
use craft\web\Controller;
use dukt\social\Plugin;
use dukt\social\web\assets\social\SocialAsset;
use dukt\social\Plugin as Social;
use GuzzleHttp\Exception\BadResponseException;
use yii\web\HttpException;
use craft\elements\User;
use dukt\social\models\Token;
use dukt\social\elements\LoginAccount;
use Exception;
use yii\base\Event;


class LoginAccountsController extends Controller
{
    // Constants
    // =========================================================================

    /**
     * @event RegisterComponentTypesEvent The event that is triggered when registering element types.
     */
    const EVENT_BEFORE_REGISTER = 'beforeRegister';

    // Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected $allowAnonymous = ['login', 'callback'];

    /**
     * URL to redirect to after login.
     *
     * @var string
     */
    private $redirect;

    /**
     * URL where the login was initiated from.
     *
     * @var string
     */
    private $originUrl;

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
     * @param int $userId The user ID
     *
     * @throws HttpException
     * @return null
     */
    public function actionEdit($userId)
    {
        $user = Craft::$app->users->getUserById($userId);

        if ($user) {
            $loginAccounts = Social::$plugin->getLoginAccounts()->getLoginAccountsByUserId($user->id);

            Craft::$app->getView()->registerAssetBundle(SocialAsset::class);

            return $this->renderTemplate('social/loginaccounts/_edit', [
                'userId' => $userId,
                'user' => $user,
                'loginAccounts' => $loginAccounts
            ]);
        } else {
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

        return $this->asJson(['success' => true]);
    }

    /**
     * Login
     *
     * @return null
     * @throws Exception
     */
    public function actionLogin()
    {
        Craft::$app->getSession()->set('social.loginControllerUrl', Craft::$app->getRequest()->getAbsoluteUrl());

        $this->originUrl = Craft::$app->getSession()->get('social.originUrl');

        if (!$this->originUrl) {
            $this->originUrl = Craft::$app->getRequest()->referrer;
            Craft::$app->getSession()->set('social.originUrl', $this->originUrl);
        }

        $this->redirect = Craft::$app->getRequest()->getParam('redirect');


        // Connect

        // Request params
        $providerHandle = Craft::$app->getRequest()->getParam('provider');

        // Settings
        $plugin = Craft::$app->getPlugins()->getPlugin('social');
        $pluginSettings = $plugin->getSettings();


        // Try to connect


        try {
            if (!$pluginSettings['enableSocialLogin']) {
                throw new Exception("Social login is disabled");
            }

            if (Craft::$app->getEdition() != Craft::Pro) {
                throw new Exception("Craft Pro is required");
            }

            $loginProvider = Social::$plugin->getLoginProviders()->getLoginProvider($providerHandle);

            if (!$loginProvider) {
                throw new Exception("Login provider is not configured");
            }

            if ($response = $this->oauthConnect($providerHandle)) {
                if ($response && is_object($response) && !$response->data) {
                    return $response;
                }

                if ($response['success']) {
                    $token = new Token();
                    $token->providerHandle = $providerHandle;
                    $token->token = $response['token'];

                    return $this->connectUserFromToken($token);
                } else {
                    throw new \Exception($response['errorMsg']);
                }
            }

        }
        catch(BadResponseException $e)
        {
            $response = $e->getResponse();
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

            Craft::error((string) $response, __METHOD__);
            Craft::$app->getSession()->setFlash('error', $errorMsg);
            $this->_cleanSession();
            return $this->redirect($this->originUrl);
        }
        catch (\Exception $e)
        {
            $errorMsg = $e->getMessage();
            Craft::$app->getSession()->setFlash('error', $errorMsg);
            $this->_cleanSession();
            return $this->redirect($this->originUrl);
        }

    }

    /**
     * OAuth callback.
     *
     * @return null
     */
    public function actionCallback()
    {
        Craft::$app->getSession()->set('social.callback', true);

        $url = Craft::$app->getSession()->get('social.loginControllerUrl');

        if (strpos($url, '?') === false) {
            $url .= '?';
        } else {
            $url .= '&';
        }


        // Pass the existing string containing oauth data to the next redirect

        $queryParams = Craft::$app->getRequest()->getQueryParams();

        if (isset($queryParams['p'])) {
            unset($queryParams['p']);
        }

        $url .= http_build_query($queryParams);

        return $this->redirect($url);
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

        Craft::$app->getSession()->setNotice(Craft::t('social', 'Login account disconnected.'));

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
     * OAuth connect.
     *
     * @param $loginProviderHandle
     *
     * @return mixed
     */
    private function oauthConnect($loginProviderHandle)
    {
        $loginProvider = Social::$plugin->getLoginProviders()->getLoginProvider($loginProviderHandle);

        Craft::$app->getSession()->set('social.loginProvider', $loginProviderHandle);

        if (!Craft::$app->getSession()->get('social.callback')) {
            return $loginProvider->oauthConnect();
        } else {
            Craft::$app->getSession()->remove('social.callback');

            return $loginProvider->oauthCallback();
        }
    }

    /**
     * Connect (register, login, link) a user from token
     *
     * @param Token $token
     *
     * @return null
     */
    private function connectUserFromToken(Token $token)
    {
        $craftUser = Craft::$app->getUser()->getIdentity();

        if ($craftUser) {
            return $this->linkAccountFromToken($token, $craftUser);
        } else {
            return $this->registerOrLoginFromToken($token);
        }
    }

    /**
     * Link account from token
     *
     * @param Token  $token
     * @param object $craftUser The logged-in user object
     *
     * @return null
     * @throws Exception
     */
    private function linkAccountFromToken(Token $token, $craftUser)
    {
        $this->_cleanSession();

        if (!$this->redirect) {
            $this->redirect = $this->originUrl;
        }

        $socialLoginProvider = Social::$plugin->getLoginProviders()->getLoginProvider($token->providerHandle);

        $attributes = $socialLoginProvider->getProfile($token);

        $socialUid = $attributes['id'];

        $account = Social::$plugin->getLoginAccounts()->getLoginAccountByUid($socialLoginProvider->getHandle(), $socialUid);

        if ($account) {
            if ($craftUser->id == $account->userId) {
                // Social::$plugin->getLoginAccounts()->saveLoginAccount($account);
                Craft::$app->elements->saveElement($account);

                Craft::$app->getSession()->setNotice(Craft::t('social', 'Login account added.'));

                return $this->redirect($this->redirect);
            } else {
                throw new Exception("This UID is already associated with another user. Disconnect from your current session and retry.");
            }
        } else {
            // save social user
            $account = new LoginAccount;
            $account->userId = $craftUser->id;
            $account->providerHandle = $socialLoginProvider->getHandle();
            $account->socialUid = $socialUid;

            // Social::$plugin->getLoginAccounts()->saveLoginAccount($account);

            Craft::$app->getElements()->saveElement($account);

            Craft::$app->getSession()->setNotice(Craft::t('social', 'Login account added.'));

            return $this->redirect($this->redirect);
        }
    }

    /**
     * Register or login user from an OAuth token
     *
     * @param Token $token
     *
     * @return null
     * @throws Exception
     */
    private function registerOrLoginFromToken(Token $token)
    {
        $socialLoginProvider = Social::$plugin->getLoginProviders()->getLoginProvider($token->providerHandle);

        $attributes = $socialLoginProvider->getProfile($token);

        $socialUid = $attributes['id'];

        $account = Social::$plugin->getLoginAccounts()->getLoginAccountByUid($socialLoginProvider->getHandle(), $socialUid);

        if ($account) {
            $craftUser = Craft::$app->users->getUserById($account->userId);

            if ($craftUser) {
                // save user
                // Social::$plugin->getLoginAccounts()->saveLoginAccount($account);
                Craft::$app->elements->saveElement($account);

                // login
                return $this->login($craftUser, $account, $token);
            } else {
                throw new Exception("Social account exists but Craft user doesn't");
            }
        } else {
            // Register user
            $craftUser = $this->registerUser($attributes, $socialLoginProvider->getHandle());

            if ($craftUser) {
                // Save social user
                $account = new LoginAccount;
                $account->userId = $craftUser->id;
                $account->providerHandle = $socialLoginProvider->getHandle();
                $account->socialUid = $socialUid;
                // Social::$plugin->getLoginAccounts()->saveLoginAccount($account);

                Craft::$app->elements->saveElement($account);

                // Login
                return $this->login($craftUser, $account, $token, true);
            } else {
                throw new Exception("Craft user couldn’t be created.");
            }
        }
    }

    /**
     * Register a user.
     *
     * @param array  $attributes Attributes of the user we want to register
     * @param string $providerHandle
     *
     * @throws Exception
     * @return User|null
     */
    private function registerUser($attributes, $providerHandle)
    {
        if (!empty($attributes['email'])) {
            // check domain locking

            $lockDomains = Social::$plugin->getSettings()->lockDomains;

            if (count($lockDomains) > 0) {
                $domainRejected = true;

                foreach ($lockDomains as $lockDomain) {
                    if (strpos($attributes['email'], '@'.$lockDomain) !== false) {
                        $domainRejected = false;
                    }
                }

                if ($domainRejected) {
                    throw new Exception("Couldn’t register with this email (domain is not allowed): ".$attributes['email']);
                }
            }

            // find user from email
            $user = Craft::$app->users->getUserByUsernameOrEmail($attributes['email']);

            if (!$user) {
                // Register Craft user
                // $user = $this->registerCraftUser($attributes, $providerHandle);
                // get social plugin settings

                $socialPlugin = Craft::$app->getPlugins()->getPlugin('social');
                $settings = $socialPlugin->getSettings();

                if (!$settings['enableSocialRegistration']) {
                    throw new Exception("Social registration is disabled.");
                }


                // Fire a 'beforeRegister' event
                if ($this->hasEventHandlers(self::EVENT_BEFORE_REGISTER)) {
                    $this->trigger(self::EVENT_BEFORE_REGISTER, new Event([
                        'account' => &$attributes,
                    ]));
                }

                $variables = $attributes;

                $loginProviderConfig = Plugin::$plugin->getLoginProviderConfig($providerHandle);

                $userMapping = null;

                if (isset($loginProviderConfig['userMapping'])) {
                    $userMapping = $loginProviderConfig['userMapping'];
                }

                $userModelAttributes = ['email', 'username', 'firstName', 'lastName', 'preferredLocale', 'weekStartDay'];

                $newUser = new User();

                if ($settings['autoFillProfile'] && is_array($userMapping)) {
                    $userContent = [];

                    foreach ($userMapping as $key => $template) {
                        // Check whether they try to set an attribute or a custom field
                        if (in_array($key, $userModelAttributes)) {
                            $attribute = $key;

                            if (array_key_exists($attribute, $newUser->getAttributes())) {
                                try {
                                    $newUser->{$attribute} = Craft::$app->getView()->renderString($template, $variables);
                                } catch (\Exception $e) {
                                    Craft::warning('Could not map:'.print_r([$attribute, $template, $variables, $e->getMessage()], true), __METHOD__);
                                }
                            }
                        } else {
                            $fieldHandle = $key;

                            // Check to make sure custom field exists for user profile
                            if (isset($newUser->{$fieldHandle})) {
                                try {
                                    $userContent[$fieldHandle] = Craft::$app->getView()->renderString($template, $variables);
                                } catch (\Exception $e) {
                                    Craft::warning('Could not map:'.print_r([$template, $variables, $e->getMessage()], true), __METHOD__);
                                }
                            }
                        }
                    }

                    foreach ($userContent as $field => $value) {
                        $newUser->setFieldValue($field, $value);
                    }
                }


                // fill default email and username if not already done

                if (!$newUser->email) {
                    $newUser->email = $attributes['email'];
                }

                if (!$newUser->username) {
                    $newUser->username = $attributes['email'];
                }


                // save user

                if (!Craft::$app->elements->saveElement($newUser)) {
                    Craft::error('There was a problem creating the user:'.print_r($newUser->getErrors(), true), __METHOD__);
                    throw new Exception("Craft user couldn’t be created.");
                }

                // save remote photo
                if ($settings['autoFillProfile']) {
                    $photoUrl = false;

                    if (isset($userMapping['photoUrl'])) {
                        try {
                            $photoUrl = Craft::$app->getView()->renderString($userMapping['photoUrl'], $variables);
                            $photoUrl = html_entity_decode($photoUrl);
                        } catch (\Exception $e) {
                            Craft::warning('Could not map:'.print_r(['photoUrl', $userMapping['photoUrl'], $variables, $e->getMessage()], true), __METHOD__);
                        }
                    } else {
                        if (!empty($attributes['photoUrl'])) {
                            $photoUrl = $attributes['photoUrl'];
                        }
                    }

                    if ($photoUrl) {
                        Social::$plugin->getLoginAccounts()->saveRemotePhoto($photoUrl, $newUser);
                    }
                }

                // save groups
                if (!empty($settings['defaultGroup'])) {
                    Craft::$app->userGroups->assignUserToGroups($newUser->id, [$settings['defaultGroup']]);
                }

                Craft::$app->elements->saveElement($newUser);

                return $newUser;
            } else {
                if (Social::$plugin->getSettings()->allowEmailMatch !== true) {
                    throw new Exception("An account already exists with this email: ".$attributes['email']);
                }
            }
        } else {
            throw new Exception("Email address not provided.");
        }

        return $user;
    }

    /**
     * Login user from login account
     *
     * @param User         $craftUser
     * @param LoginAccount $account
     * @param Token        $token
     * @param bool         $registrationMode
     *
     * @return null
     * @throws Exception
     */
    private function login(User $craftUser, LoginAccount $account, Token $token, $registrationMode = false)
    {
        $this->_cleanSession();

        if (!$this->redirect) {
            $this->redirect = $this->originUrl;
        }

        if (!$account->authenticate($token)) {
            throw new Exception("Couldn’t authenticate account.");
        }

        if (Craft::$app->getUser()->login($craftUser)) {
            if ($registrationMode) {
                Craft::$app->getSession()->setNotice(Craft::t('social', 'Account created.'));
            } else {
                Craft::$app->getSession()->setNotice(Craft::t('social', 'Logged in.'));
            }

            return $this->redirect($this->redirect);
        } else {
            $errorCode = Social::$plugin->getUserSession()->getLoginErrorCode();
            $errorMessage = Social::$plugin->getUserSession()->getLoginErrorMessage($errorCode, $account->user->username);

            Craft::$app->getSession()->setError($errorMessage);

            return $this->redirect($this->originUrl);
        }
    }

    /**
     * Clean session variables
     *
     * @return null
     */
    private function _cleanSession()
    {
        Craft::$app->getSession()->remove('social.originUrl');
        // Craft::$app->getSession()->remove('social.requestUri');
    }
}
