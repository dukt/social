<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/social/docs/license
 */

namespace dukt\social;

use Craft;
use craft\elements\User;
use craft\events\DefineComponentsEvent;
use craft\events\ModelEvent;
use craft\events\RegisterElementTableAttributesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\SetElementTableAttributeHtmlEvent;
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
 * @author Dukt <support@dukt.net>
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

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, [$this, 'registerCpUrlRules']);

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

        Event::on(CraftVariable::class, CraftVariable::EVENT_DEFINE_COMPONENTS, function(DefineComponentsEvent $event) {
            $event->components['social'] = SocialVariable::class;
        });

        Event::on(User::class, User::EVENT_AFTER_SAVE, function(ModelEvent $event) {
            $user = $event->sender;
            $loginAccounts = Plugin::$plugin->getLoginAccounts()->getLoginAccountsByUserId($user->id);

            foreach ($loginAccounts as $loginAccount) {
                Plugin::$plugin->getLoginAccounts()->saveLoginAccount($loginAccount);
            }
        });

        $this->initCpSocialLogin();
        $this->initLoginAccountsUserPane();
    }

    /**
     * @inheritdoc
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

            'settings/social' => ['template' => 'social/settings/index'],
            'settings/social/general' => 'social/settings/general',
            'settings/social/loginproviders' => 'social/login-providers/index',
            'settings/social/loginproviders/<handle:{handle}>' => 'social/login-providers/edit',

            // 'social/settings' => 'social/settings/index',
        ];

        $event->rules = array_merge($event->rules, $rules);
    }

    /**
     * Get Social login providers.
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
        $url = \craft\helpers\UrlHelper::cpUrl('settings/social/loginproviders');

        \Craft::$app->controller->redirect($url);

        return '';
    }

    /**
     * Returns login provider config.
     *
     * @param $handle
     *
     * @return mixed
     */
    public function getLoginProviderConfig($handle)
    {
        $config = Craft::$app->getConfig()->getConfigFromFile('social');

        if (isset($config['loginProviders'][$handle])) {
            return $config['loginProviders'][$handle];
        }
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

    // Private Methods
    // =========================================================================

    /**
     * Social login for the control panel.
     *
     * @return null
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

                Craft::$app->getView()->registerJs("var socialLoginForm = new Craft.SocialLoginForm(".json_encode($jsLoginProviders).", ".json_encode($error).");");
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
        Craft::$app->getView()->hook('cp.users.edit.right-pane', function(&$context) {
            if ($context['account']) {
                $context['user'] = $context['account'];
                $context['loginAccounts'] = $this->loginAccounts->getLoginAccountsByUserId($context['account']->id);
                $context['loginProviders'] = $this->loginProviders->getLoginProviders();

                Craft::$app->getView()->registerAssetBundle(SocialAsset::class);

                return Craft::$app->getView()->renderTemplate('social/_components/users/login-accounts-pane', $context);
            }
        });
    }
}
