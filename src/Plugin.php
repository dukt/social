<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2018, Dukt
 * @license   https://dukt.net/social/docs/license
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
            'loginAccounts' => \dukt\social\services\LoginAccounts::class,
            'loginProviders' => \dukt\social\services\LoginProviders::class,
        ]);

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $rules = [
                'social' => 'social/login-accounts/index',

                'social/loginaccounts' => 'social/loginAccounts/index',
                'social/loginaccounts/<userId:\d+>' => 'social/login-accounts/edit',

                'settings/social' => 'social/login-providers/index',
                'settings/social/loginproviders' => 'social/login-providers/index',
                'settings/social/loginproviders/<handle:{handle}>' => 'social/login-providers/oauth',
                'settings/social/loginproviders/<handle:{handle}>/usermapping' => 'social/login-providers/user-mapping',
                'settings/social/settings' => 'social/settings/settings',
            ];

            $event->rules = array_merge($event->rules, $rules);
        });

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

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            /** @var CraftVariable $variable */
            $variable = $event->sender;
            $variable->set('social', SocialVariable::class);
        });

        Event::on(User::class, User::EVENT_AFTER_SAVE, function(ModelEvent $event) {
            $user = $event->sender;
            $loginAccounts = Plugin::$plugin->getLoginAccounts()->getLoginAccountsByUserId($user->id);

            foreach ($loginAccounts as $loginAccount) {
                Plugin::$plugin->getLoginAccounts()->saveLoginAccount($loginAccount);
            }
        });

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_LOAD_PLUGINS,
            function() {
                $this->initCpSocialLogin();
            });
        $this->initLoginAccountsUserPane();
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
    public function getOauthProviderConfig($handle)
    {
        if(!isset($this->getSettings()->oauthProviders[$handle])) {
            return [];
        }

        $config = $this->getSettings()->oauthProviders[$handle];

        $provider = $this->getLoginProviders()->getLoginProvider($handle);

        if ($provider) {
            $config['redirectUri'] = $provider->getRedirectUri();
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
        if(!isset($this->getSettings()->loginProviders[$handle])) {
            return [];
        }

        return $this->getSettings()->loginProviders[$handle];
    }

    /**
     * Has CP Section.
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
     * @inheritdoc
     */
    public function beforeUpdate(string $fromVersion): bool
    {
        if (version_compare($fromVersion, '1.1.0', '<')) {
            Craft::error('Social Login 2 requires you to be running at least v1.1.0 before updating');

            return false;
        }

        return true;
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

                Craft::$app->getView()->registerJs('var socialLoginForm = new Craft.SocialLoginForm('.json_encode($jsLoginProviders).', '.json_encode($error).');');
            }
        }
    }

    /**
     * Initialize login accounts user pane.
     *
     * @return null
     */
    private function initLoginAccountsUserPane()
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
}
