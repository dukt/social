<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) Dukt
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
use dukt\social\elements\LoginAccount;
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
    public bool $hasCpSettings = true;

    /**
     * @inheritdoc
     */
    public string $minVersionRequired = '1.1.0';

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
    public function getSettingsResponse(): mixed
    {
        $url = UrlHelper::cpUrl('settings/social/loginproviders');

        Craft::$app->controller->redirect($url);

        return '';
    }

    /**
     * Get OAuth provider config.
     *
     * @param $handle
     * @param bool $parse
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getOauthProviderConfig(string $handle, bool $parse = true): array
    {
        $config = [
            'options' => $this->getOauthConfigItem($handle, 'options', $parse),
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
     * @param Plugin|null $plugin
     * @return bool
     */
    public function savePluginSettings(array $settings, Plugin $plugin = null)
    {
        if ($plugin === null) {
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
        $settings = (array)self::getInstance()->getSettings();
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
    protected function createSettingsModel(): ?\craft\base\Model
    {
        return new Settings();
    }

    // Private Methods
    // =========================================================================

    /**
     * Social login for the control panel.
     *
     * @return null
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    private function initCpSocialLogin()
    {
        if (!Craft::$app->getRequest()->getIsConsoleRequest() && $this->getSettings()->enableCpLogin && Craft::$app->getRequest()->getIsCpRequest() && Craft::$app->getRequest()->getSegment(1) === 'login') {

            $loginProviders = $this->loginProviders->getLoginProviders();
            $jsLoginProviders = [];

            foreach ($loginProviders as $loginProvider) {
                $jsLoginProvider = [
                    'name' => $loginProvider->getName(),
                    'handle' => $loginProvider->getHandle(),
                    'url' => $this->getLoginAccounts()->getLoginUrl($loginProvider->getHandle()),
                    'iconUrl' => $loginProvider->getIconUrl(),
                ];

                $jsLoginProviders[] = $jsLoginProvider;
            }

            $error = Craft::$app->getSession()->getFlash('error');

            Craft::$app->getView()->registerAssetBundle(LoginAsset::class);

            Craft::$app->getView()->registerJs('var socialLoginForm = new Craft.SocialLoginForm(' . json_encode($jsLoginProviders) . ', ' . json_encode($error) . ');');
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
            if ($context['user'] && $context['user']->id) {
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
    private function getOauthConfigItem(string $providerHandle, string $key, bool $parse = true): array
    {
        $configSettings = Craft::$app->config->getConfigFromFile($this->id);

        if (isset($configSettings['loginProviders'][$providerHandle]['oauth'][$key])) {
            return $this->parseOauthConfigItemEnv($key, $configSettings['loginProviders'][$providerHandle]['oauth'][$key], $parse);
        }

        $storedSettings = Craft::$app->plugins->getStoredPluginInfo($this->id)['settings'];

        if (isset($storedSettings['loginProviders'][$providerHandle]['oauth'][$key])) {
            return $this->parseOauthConfigItemEnv($key, $storedSettings['loginProviders'][$providerHandle]['oauth'][$key], $parse);
        }

        return [];
    }

    /**
     * Parse OAuth config item environment variables.
     *
     * @param string $key
     * @param array $configItem
     * @param bool $parse
     * @return array
     */
    private function parseOauthConfigItemEnv(string $key, array $configItem, bool $parse = true): array
    {
        // Parse config item options environment variables
        if ($parse && $key === 'options') {
            return array_map('Craft::parseEnv', $configItem);
        }

        return $configItem;
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
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event): void {
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
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event): void {
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
        Event::on(User::class, User::EVENT_REGISTER_TABLE_ATTRIBUTES, function(RegisterElementTableAttributesEvent $event): void {
            $event->tableAttributes['loginAccounts'] = Craft::t('social', 'Login Accounts');
        });

        Event::on(User::class, User::EVENT_SET_TABLE_ATTRIBUTE_HTML, function(SetElementTableAttributeHtmlEvent $event): void {
            if ($event->attribute === 'loginAccounts') {
                Craft::$app->getView()->registerAssetBundle(SocialAsset::class);

                $user = $event->sender;

                $loginAccounts = LoginAccount::find()
                    ->userId($user->id)
                    ->trashed($user->trashed)
                    ->all();

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
        Event::on(User::class, User::EVENT_AFTER_SAVE, function(ModelEvent $event): void {
            $user = $event->sender;
            $loginAccounts = Plugin::getInstance()->getLoginAccounts()->getLoginAccountsByUserId($user->id);

            foreach ($loginAccounts as $loginAccount) {
                Craft::$app->elements->saveElement($loginAccount);
            }
        });

        // Soft delete the related login accounts after deleting a user
        Event::on(User::class, User::EVENT_AFTER_DELETE, function(Event $event): void {
            $user = $event->sender;

            $loginAccounts = LoginAccount::find()
                ->userId($user->id)
                ->all();

            foreach($loginAccounts as $loginAccount) {
                Craft::$app->getElements()->deleteElement($loginAccount);
            }
        });

        // Make sure there’s no duplicate login account before restoring the user
        Event::on(User::class, User::EVENT_BEFORE_RESTORE, function(ModelEvent $event) {
            $user = $event->sender;

            // Get the login accounts of the user that’s being restored
            $loginAccounts = LoginAccount::find()
                ->userId($user->id)
                ->trashed(true)
                ->all();

            $conflicts = false;

            // Check that those login accounts don’t conflict with existing login accounts from other users
            foreach ($loginAccounts as $loginAccount) {
                // Check if there is another user with a login account using the same providerHandle/socialUid combo
                $existingAccount = LoginAccount::find()->one();

                if ($existingAccount) {
                    $conflicts = true;
                }
            }

            // Mark the event as invalid is there are conflicts
            if ($conflicts) {
                $event->isValid = false;
                return false;
            }

            // Restore login account elements
            foreach($loginAccounts as $loginAccount) {
                Craft::$app->getElements()->restoreElement($loginAccount);
            }
        });

        // Initialize Social Login for CP after loading the plugins
        Event::on(Plugins::class, Plugins::EVENT_AFTER_LOAD_PLUGINS, function(): void {
            $this->initCpSocialLogin();
        });
    }
}
