<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2018, Dukt
 * @license   https://github.com/dukt/social/blob/v2/LICENSE.md
 */

namespace dukt\social;

use Craft;
use craft\elements\User;
use craft\events\ModelEvent;
use craft\events\RegisterElementTableAttributesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\SetElementTableAttributeHtmlEvent;
use craft\helpers\UrlHelper;
use craft\services\Plugins;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use dukt\social\base\PluginTrait;
use dukt\social\models\Settings;
use dukt\social\web\assets\login\LoginAsset;
use dukt\social\web\twig\variables\SocialVariable;
use dukt\social\web\assets\social\SocialAsset;
use yii\base\Event;

/**
 * Social plugin class.
 *
 * @author  Dukt <support@dukt.net>
 * @since   1.0
 */
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
    public $hasCpSettings = true;

    /**
     * @inheritdoc
     */
    public $minVersionRequired = '1.1.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->_setPluginComponents();
        $this->_registerCpRoutes();
        $this->_registerVariable();
        $this->_registerEventHandlers();
        $this->_registerTableAttributes();
        $this->_initLoginAccountsUserPane();
    }

    /**
     * @inheritdoc
     */
    public function getSettingsResponse()
    {
        $url = UrlHelper::cpUrl('settings/social/loginproviders');

        Craft::$app->controller->redirect($url);

        return '';
    }

    /**
     * Get OAuth provider config.
     *
     * @param $handle
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getOauthProviderConfig($handle): array
    {
        $config = [
            'options' => $this->getOauthConfigItem($handle, 'options'),
            'scope' => $this->getOauthConfigItem($handle, 'scope'),
            'authorizationOptions' => $this->getOauthConfigItem($handle, 'authorizationOptions'),
        ];

        $provider = $this->getLoginProviders()->getLoginProvider($handle);

        if ($provider && !isset($config['options']['redirectUri'])) {
            $config['options']['redirectUri'] = $provider->getRedirectUri();
        }

        return $config;
    }

    /**
     * Get login provider config.
     *
     * @param $handle
     *
     * @return array
     */
    public function getLoginProviderConfig($handle)
    {
        $configSettings = Craft::$app->config->getConfigFromFile($this->id);

        if (isset($configSettings['loginProviders'][$handle])) {
            return $configSettings['loginProviders'][$handle];
        }

        return [];
    }

    /**
     * Save plugin settings.
     *
     * @param array $settings
     *
     * @return bool
     */
    public function savePluginSettings(array $settings, Plugin $plugin = null)
    {
        if (!$plugin) {
            $plugin = Craft::$app->getPlugins()->getPlugin('social');

            if ($plugin === null) {
                throw new NotFoundHttpException('Plugin not found');
            }
        }

        $storedSettings = Craft::$app->plugins->getStoredPluginInfo('social')['settings'];

        $settings['loginProviders'] = [];

        if (isset($storedSettings['loginProviders'])) {
            $settings['loginProviders'] = $storedSettings['loginProviders'];
        }

        return Craft::$app->getPlugins()->savePluginSettings($plugin, $settings);
    }

    /**
     * Save login provider settings.
     *
     * @param $handle
     * @param $providerSettings
     *
     * @return bool
     */
    public function saveLoginProviderSettings($handle, $providerSettings)
    {
        $settings = (array)Plugin::getInstance()->getSettings();
        $storedSettings = Craft::$app->plugins->getStoredPluginInfo('social')['settings'];

        $settings['loginProviders'] = [];

        if (isset($storedSettings['loginProviders'])) {
            $settings['loginProviders'] = $storedSettings['loginProviders'];
        }

        $settings['loginProviders'][$handle] = $providerSettings;

        $plugin = Craft::$app->getPlugins()->getPlugin('social');

        return Craft::$app->getPlugins()->savePluginSettings($plugin, $settings);
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    // Private Methods
    // =========================================================================

    /**
     * Social login for the control panel.
     *
     * @return null
     * @throws \yii\base\InvalidConfigException
     */
    private function initCpSocialLogin()
    {
        if (!Craft::$app->getRequest()->getIsConsoleRequest() && $this->settings->enableCpLogin) {
            if (Craft::$app->getRequest()->getIsCpRequest() && Craft::$app->getRequest()->getSegment(1) === 'login') {

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

                Craft::$app->getView()->registerAssetBundle(LoginAsset::class);

                Craft::$app->getView()->registerJs('var socialLoginForm = new Craft.SocialLoginForm(' . json_encode($jsLoginProviders) . ', ' . json_encode($error) . ');');
            }
        }
    }

    /**
     * Initialize login accounts user pane.
     *
     * @return null
     */
    private function _initLoginAccountsUserPane()
    {
        Craft::$app->getView()->hook('cp.users.edit.details', function(&$context) {
            if ($context['user']) {
                $context['loginAccounts'] = $this->loginAccounts->getLoginAccountsByUserId($context['user']->id);
                $context['loginProviders'] = $this->loginProviders->getLoginProviders();

                Craft::$app->getView()->registerAssetBundle(SocialAsset::class);

                return Craft::$app->getView()->renderTemplate('social/_components/users/login-accounts-pane', $context);
            }
        });
    }

    /**
     * Get OAuth config item
     *
     * @param string $providerHandle
     * @param string $key
     *
     * @return array
     */
    private function getOauthConfigItem(string $providerHandle, string $key): array
    {
        $configSettings = Craft::$app->config->getConfigFromFile($this->id);

        if (isset($configSettings['loginProviders'][$providerHandle]['oauth'][$key])) {
            return $configSettings['loginProviders'][$providerHandle]['oauth'][$key];
        }

        $storedSettings = Craft::$app->plugins->getStoredPluginInfo($this->id)['settings'];

        if (isset($storedSettings['loginProviders'][$providerHandle]['oauth'][$key])) {
            return $storedSettings['loginProviders'][$providerHandle]['oauth'][$key];
        }

        return [];
    }

    /**
     * Set plugin components.
     */
    private function _setPluginComponents()
    {
        $this->setComponents([
            'loginAccounts' => \dukt\social\services\LoginAccounts::class,
            'loginProviders' => \dukt\social\services\LoginProviders::class,
        ]);
    }

    /**
     * Register CP routes.
     */
    private function _registerCpRoutes()
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $rules = [
                'social' => 'social/login-accounts/index',

                'social/loginaccounts' => 'social/loginAccounts/index',
                'social/loginaccounts/<userId:\d+>' => 'social/login-accounts/edit',

                'settings/social' => 'social/login-providers/index',
                'settings/social/loginproviders' => 'social/login-providers/index',
                'settings/social/loginproviders/<handle:{handle}>' => 'social/login-providers/oauth',
                'settings/social/loginproviders/<handle:{handle}>/user-field-mapping' => 'social/login-providers/user-field-mapping',
                'settings/social/settings' => 'social/settings/settings',
            ];

            $event->rules = array_merge($event->rules, $rules);
        });
    }

    /**
     * Register Social template variable.
     */
    private function _registerVariable()
    {
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            /** @var CraftVariable $variable */
            $variable = $event->sender;
            $variable->set('social', SocialVariable::class);
        });
    }

    /**
     * Register Social user table attributes.
     */
    private function _registerTableAttributes()
    {
        Event::on(User::class, User::EVENT_REGISTER_TABLE_ATTRIBUTES, function(RegisterElementTableAttributesEvent $event) {
            $event->tableAttributes['loginAccounts'] = Craft::t('social', 'Login Accounts');
        });

        Event::on(User::class, User::EVENT_SET_TABLE_ATTRIBUTE_HTML, function(SetElementTableAttributeHtmlEvent $event) {
            if ($event->attribute === 'loginAccounts') {
                Craft::$app->getView()->registerAssetBundle(SocialAsset::class);
                $user = $event->sender;

                $loginAccounts = $this->getLoginAccounts()->getLoginAccountsByUserId($user->Id);

                if ($loginAccounts) {
                    $event->html = Craft::$app->getView()->renderTemplate('social/_components/users/login-accounts-table-attribute', [
                        'loginAccounts' => $loginAccounts,
                    ]);
                } else {
                    $event->html = '';
                }
            }
        });
    }

    /**
     * Register event handlers.
     */
    private function _registerEventHandlers()
    {
        Event::on(User::class, User::EVENT_AFTER_SAVE, function(ModelEvent $event) {
            $user = $event->sender;
            $loginAccounts = Plugin::getInstance()->getLoginAccounts()->getLoginAccountsByUserId($user->id);

            foreach ($loginAccounts as $loginAccount) {
                Plugin::getInstance()->getLoginAccounts()->saveLoginAccount($loginAccount);
            }
        });

        Event::on(Plugins::class, Plugins::EVENT_AFTER_LOAD_PLUGINS, function() {
            $this->initCpSocialLogin();
        });
    }
}
