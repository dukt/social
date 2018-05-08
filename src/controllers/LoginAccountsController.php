<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2018, Dukt
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
use yii\web\Response;

/**
 * The LoginAccountsController class is a controller that handles various login account related tasks.
 *
 * Note that all actions in the controller, except [[actionLogin]], [[actionCallback]], require an authenticated Craft session via [[allowAnonymous]].
 *
 * @author  Dukt <support@dukt.net>
 * @since   1.0
 */
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
     * Login Accounts Index.
     *
     * @return Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex(): Response
    {
        Craft::$app->getView()->registerAssetBundle(SocialAsset::class);

        return $this->renderTemplate('social/loginaccounts/_index');
    }

    /**
     * Edit login accounts.
     *
     * @param $userId
     *
     * @return Response
     * @throws HttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionEdit($userId): Response
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
     * Delete login account.
     *
     * @return Response
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDeleteLoginAccount(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $loginAccountId = Craft::$app->getRequest()->getRequiredBodyParam('id');

        Social::$plugin->getLoginAccounts()->deleteLoginAccountById($loginAccountId);

        return $this->asJson(['success' => true]);
    }

    /**
     * Login.
     *
     * @return Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function actionLogin(): Response
    {
        Craft::$app->getSession()->set('social.loginControllerUrl', Craft::$app->getRequest()->getAbsoluteUrl());

        $this->originUrl = Craft::$app->getSession()->get('social.originUrl');

        if (!$this->originUrl) {
            $this->originUrl = Craft::$app->getRequest()->referrer;
            Craft::$app->getSession()->set('social.originUrl', $this->originUrl);
        }

        $this->redirect = Craft::$app->getRequest()->getParam('redirect');


        // Connect

        $providerHandle = Craft::$app->getRequest()->getParam('provider');
        $plugin = Craft::$app->getPlugins()->getPlugin('social');
        $pluginSettings = $plugin->getSettings();

        try {
            if (!$pluginSettings['enableSocialLogin']) {
                throw new Exception("Social login is disabled");
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
                }

                throw new \Exception($response['errorMsg']);
            }
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
            $body = $response->getBody();
            $json = json_decode($body, true);

            if ($json) {
                $errorMsg = $json['error']['message'];
            } else {
                $errorMsg = "Couldn’t login.";
            }

            Craft::error((string)$response, __METHOD__);
            Craft::$app->getSession()->setFlash('error', $errorMsg);
            $this->_cleanSession();

            return $this->redirect($this->originUrl);
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            Craft::$app->getSession()->setFlash('error', $errorMsg);
            $this->_cleanSession();

            return $this->redirect($this->originUrl);
        }
    }

    /**
     * OAuth callback.
     *
     * @return Response
     */
    public function actionCallback(): Response
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
     * Connect a login account (link).
     *
     * @return Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function actionConnectLoginAccount(): Response
    {
        return $this->actionLogin();
    }

    /**
     * Disconnect a login account (unlink).
     *
     * @return Response
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    public function actionDisconnectLoginAccount(): Response
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
     * Change photo.
     *
     * @return Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \craft\errors\ImageException
     * @throws \craft\errors\VolumeException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionChangePhoto(): Response
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
     * @return array|null
     * @throws \yii\base\InvalidConfigException
     */
    private function oauthConnect($loginProviderHandle)
    {
        $loginProvider = Social::$plugin->getLoginProviders()->getLoginProvider($loginProviderHandle);

        Craft::$app->getSession()->set('social.loginProvider', $loginProviderHandle);

        if (!Craft::$app->getSession()->get('social.callback')) {
            return $loginProvider->oauthConnect();
        }

        Craft::$app->getSession()->remove('social.callback');

        return $loginProvider->oauthCallback();
    }

    /**
     * Connect (register, login, link) a user from token.
     *
     * @param Token $token
     *
     * @return null|\yii\web\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \craft\errors\ImageException
     * @throws \craft\errors\VolumeException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    private function connectUserFromToken(Token $token)
    {
        $craftUser = Craft::$app->getUser()->getIdentity();

        if ($craftUser) {
            return $this->linkAccountFromToken($token, $craftUser);
        }

        return $this->registerOrLoginFromToken($token);
    }

    /**
     * Link account from token.
     *
     * @param Token $token
     * @param       $craftUser
     *
     * @return Response
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    private function linkAccountFromToken(Token $token, $craftUser): Response
    {
        $this->_cleanSession();

        if (!$this->redirect) {
            $this->redirect = $this->originUrl;
        }

        $socialLoginProvider = Social::$plugin->getLoginProviders()->getLoginProvider($token->providerHandle);

        $attributes = $socialLoginProvider->getProfile($token);

        $socialUid = $attributes['id'];

        $account = Social::$plugin->getLoginAccounts()->getLoginAccountByUid($socialLoginProvider->getHandle(), $socialUid);


        // Existing login account

        if ($account) {
            if ($craftUser->id == $account->userId) {
                Craft::$app->elements->saveElement($account);

                Craft::$app->getSession()->setNotice(Craft::t('social', 'Login account added.'));

                return $this->redirect($this->redirect);
            }

            throw new Exception("This UID is already associated with another user. Disconnect from your current session and retry.");
        }


        // New login account

        $account = new LoginAccount;
        $account->userId = $craftUser->id;
        $account->providerHandle = $socialLoginProvider->getHandle();
        $account->socialUid = $socialUid;

        Craft::$app->getElements()->saveElement($account);

        Craft::$app->getSession()->setNotice(Craft::t('social', 'Login account added.'));

        return $this->redirect($this->redirect);
    }

    /**
     * Register or login user from an OAuth token.
     *
     * @param Token $token
     *
     * @return null
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \craft\errors\ImageException
     * @throws \craft\errors\VolumeException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    private function registerOrLoginFromToken(Token $token)
    {
        $socialLoginProvider = Social::$plugin->getLoginProviders()->getLoginProvider($token->providerHandle);

        $attributes = $socialLoginProvider->getProfile($token);

        $socialUid = $attributes['id'];

        $account = Social::$plugin->getLoginAccounts()->getLoginAccountByUid($socialLoginProvider->getHandle(), $socialUid);


        // Existing user

        if ($account) {
            $craftUser = Craft::$app->users->getUserById($account->userId);

            if ($craftUser) {
                Craft::$app->elements->saveElement($account);

                return $this->login($craftUser, $account, $token);
            }

            throw new Exception("Social account exists but Craft user doesn't");
        }


        // Register new user

        $craftUser = $this->registerUser($attributes, $socialLoginProvider->getHandle());

        if ($craftUser) {
            // Save social user
            $account = new LoginAccount;
            $account->userId = $craftUser->id;
            $account->providerHandle = $socialLoginProvider->getHandle();
            $account->socialUid = $socialUid;

            Craft::$app->elements->saveElement($account);

            return $this->login($craftUser, $account, $token, true);
        }

        throw new Exception("Craft user couldn’t be created.");
    }

    /**
     * Register a user.
     *
     * @param $attributes
     * @param $providerHandle
     *
     * @return User
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \craft\errors\ImageException
     * @throws \craft\errors\VolumeException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    private function registerUser($attributes, $providerHandle): User
    {
        if (empty($attributes['email'])) {
            throw new Exception("Email address not provided.");
        }


        // Existing user with matching email

        $user = Craft::$app->users->getUserByUsernameOrEmail($attributes['email']);

        if ($user) {
            if (Social::$plugin->getSettings()->allowEmailMatch !== true) {
                throw new Exception("An account already exists with this email: ".$attributes['email']);
            }

            return $user;
        }


        // Register a new user

        Craft::$app->requireEdition(Craft::Pro);

        $socialPlugin = Craft::$app->getPlugins()->getPlugin('social');
        $settings = $socialPlugin->getSettings();

        if (!$settings['enableSocialRegistration']) {
            throw new Exception("Social registration is disabled.");
        }

        // Lock domains
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
            Craft::$app->users->assignUserToGroups($newUser->id, [$settings['defaultGroup']]);
        }

        Craft::$app->elements->saveElement($newUser);

        return $newUser;
    }

    /**
     * Login user from login account.
     *
     * @param User         $craftUser
     * @param LoginAccount $account
     * @param Token        $token
     * @param bool         $registrationMode
     *
     * @return Response
     * @throws Exception
     */
    private function login(User $craftUser, LoginAccount $account, Token $token, $registrationMode = false): Response
    {
        $this->_cleanSession();

        if (!$this->redirect) {
            $this->redirect = $this->originUrl;
        }

        if (!$account->authenticate($token)) {
            return $this->_handleLoginFailure();
        }

        if (!Craft::$app->getUser()->login($craftUser)) {
            return $this->_handleLoginFailure();
        }

        return $this->_handleSuccessfulLogin($registrationMode);
    }

    /**
     * Handles a failed login attempt.
     *
     * @return Response
     */
    private function _handleLoginFailure(): Response
    {
        Craft::$app->getSession()->setError(Craft::t('social', 'Couldn’t authenticate.'));

        return $this->redirect($this->originUrl);
    }

    /**
     * Redirects the user after a successful login attempt.
     *
     * @param bool $registrationMode
     *
     * @return Response
     */
    private function _handleSuccessfulLogin(bool $registrationMode): Response
    {
        if ($registrationMode) {
            Craft::$app->getSession()->setNotice(Craft::t('social', 'Account created.'));
        } else {
            Craft::$app->getSession()->setNotice(Craft::t('social', 'Logged in.'));
        }

        return $this->redirect($this->redirect);
    }

    /**
     * Clean session variables.
     */
    private function _cleanSession()
    {
        Craft::$app->getSession()->remove('social.originUrl');
    }
}
