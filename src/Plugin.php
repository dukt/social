<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social;

use Craft;
use dukt\social\base\PluginTrait;
use yii\base\Event;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;
use dukt\social\models\Settings;
use dukt\social\variables\SocialVariable;
use dukt\social\web\assets\social\SocialAsset;

class Plugin extends \craft\base\Plugin
{
    // Traits
    // =========================================================================

    use PluginTrait;

    // Properties
    // =========================================================================

    /**
     * @var bool
     */
    public $hasSettings = true;

    /**
     * @var bool
     */
    public $hasCpSection = false;

    /**
     * @var \dukt\social\Plugin The plugin instance.
     */
    public static $plugin;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->hasCpSection = $this->hasCpSection();

        $this->setComponents([
            'social' => \dukt\social\services\Social::class,
            'loginAccounts' => \dukt\social\services\LoginAccounts::class,
            'loginProviders' => \dukt\social\services\LoginProviders::class,
            'userSession' => \dukt\social\services\UserSession::class,
            'oauth' => \dukt\social\services\Oauth::class,
        ]);

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, [$this, 'registerCpUrlRules']);

        // $this->initEventListeners();
        $this->initTemplateHooks();
    }

    /**
     * @return mixed
     */
    public function defineTemplateComponent()
    {
        return SocialVariable::class;
    }

    /**
     * @param RegisterUrlRulesEvent $event
     */
    public function registerCpUrlRules(RegisterUrlRulesEvent $event)
    {
        $rules = [
            'social' => 'social/login-accounts/index',

            'social/loginaccounts' => 'social/loginAccounts/index',
            'social/loginaccounts/<userId:\d+>' => 'social/login-accounts/edit',

            'social/settings/general' => 'social/settings/general',
            'social/settings/loginproviders' => 'social/login-providers/index',
            'social/settings/loginproviders/<handle:{handle}>' => 'social/login-providers/edit',

            // 'social/settings' => 'social/settings/index',
        ];

        $event->rules = array_merge($event->rules, $rules);
    }

    /**
     * Get Social Login Providers
     *
     * @return array
     */
    public function getSocialLoginProviders()
    {
        return [
            'dukt\social\loginproviders\Facebook',
            'dukt\social\loginproviders\Google',
            'dukt\social\loginproviders\Twitter',
        ];
    }

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @return string
     */
    public function getSettingsResponse()
    {
        $url = \craft\helpers\UrlHelper::cpUrl('social/settings');

        \Craft::$app->controller->redirect($url);

        return '';
    }

    /**
     * Has CP Section
     *
     * @return bool
     */
    public function hasCpSection()
    {
        $settings = $this->getSettings();

        if ($settings['showCpSection']) {
            return true;
        }

        return false;
    }

    /**
     * Defines additional user table attributes.
     *
     * @return array
     */
    public function defineAdditionalUserTableAttributes()
    {
        return [
            'loginAccounts' => Craft::t('social', 'Login Accounts')
        ];
    }

    /**
     * Returns the HTML of the user table attribute.
     *
     * @param UserModel $user
     * @param           $attribute
     *
     * @return string
     */
    public function getUserTableAttributeHtml(UserModel $user, $attribute)
    {
        if ($attribute == 'loginAccounts') {
            $loginAccounts = $this->loginAccounts->getLoginAccountsByUserId($user->id);

            if (!$loginAccounts) {
                return '';
            }

            $variables = [
                'loginAccounts' => $loginAccounts,
            ];

            Craft::$app->getView()->registerCssFile('social/css/social.css');

            $html = Craft::$app->getView()->renderTemplate('social/users/_login-accounts-column', $variables, true);

            return $html;
        }
    }
    // Protected Methods
    // =========================================================================

    /**
     * Define Social Settings.
     *
     * @return array
     */
    protected function defineSettings()
    {
        return [
            'enableSocialRegistration' => [AttributeType::Bool, 'default' => true],
            'enableSocialLogin' => [AttributeType::Bool, 'default' => true],
            'loginProviders' => [AttributeType::Mixed],
            'defaultGroup' => [AttributeType::Number, 'default' => null],
            'autoFillProfile' => [AttributeType::Bool, 'default' => true],
            'showCpSection' => [AttributeType::Bool, 'default' => true],
            'enableCpLogin' => [AttributeType::Bool, 'default' => false],
        ];
    }

    // Private Methods
    // =========================================================================

    /**
     * Initialize event listeners
     *
     * @return null
     */
    private function initEventListeners()
    {
        // social login for CP

        if ($this->settings->enableCpLogin) {
            if (Craft::$app->getRequest()->isCpRequest() && Craft::$app->getRequest()->getSegment(1) == 'login') {
                $loginProviders = $this->loginProviders->getLoginProviders();
                $jsLoginProviders = [];

                foreach ($loginProviders as $loginProvider) {
                    $jsLoginProvider = [
                        'name' => $loginProvider->getName(),
                        'handle' => $loginProvider->getHandle(),
                        'url' => $this->getLoginAccounts()->getLoginUrl($loginProvider->getHandle()),
                        'iconUrl' => $loginProvider->getIconUrl(),
                    ];

                    array_push($jsLoginProviders, $jsLoginProvider);
                }

                $error = Craft::$app->getSession()->getFlash('error');

                Craft::$app->getView()->registerCssFile("social/css/login.css", true);
                Craft::$app->getView()->includeJsResource("social/js/login.js", true);

                Craft::$app->getView()->includeJs("var socialLoginForm = new Craft.SocialLoginForm(".json_encode($jsLoginProviders).", ".json_encode($error).");");
            }
        }


        // Delete social user when craft user is deleted

        Craft::$app->on('users.onBeforeDeleteUser', function(Event $event) {
            $user = $event->params['user'];

            $this->loginAccounts->deleteLoginAccountByUserId($user->id);
        });
    }

    /**
     * Initialize template hooks
     *
     * @return null
     */
    private function initTemplateHooks()
    {
        Craft::$app->getView()->hook('cp.users.edit.right-pane', function(&$context) {
            if ($context['account']) {
                $context['user'] = $context['account'];
                $context['loginAccounts'] = $this->loginAccounts->getLoginAccountsByUserId($context['account']->id);

                $loginProviders = $this->loginProviders->getLoginProviders();
                $context['loginProviders'] = [];

                foreach ($loginProviders as $loginProvider) {
                    $providerAvailable = true;

                    foreach ($context['loginAccounts'] as $loginAccount) {
                        if ($loginProvider->getHandle() == $loginAccount->providerHandle) {
                            $providerAvailable = false;
                        }
                    }

                    if ($providerAvailable) {
                        $context['loginProviders'][] = $loginProvider;
                    }
                }

                Craft::$app->getView()->registerAssetBundle(SocialAsset::class);

                return Craft::$app->getView()->renderTemplate('social/users/_edit-pane', $context);
            }
        });
    }
}
