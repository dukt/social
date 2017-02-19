<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social;

use Craft;
use yii\base\Event;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;
use dukt\social\models\Settings;
use dukt\social\variables\SocialVariable;
use dukt\social\web\assets\social\SocialAsset;

class Plugin extends \craft\base\Plugin
{
    public $hasSettings = true;
    public $hasCpSection = false;

    public static $plugin;

	// Public Methods
	// =========================================================================

	/**
	 * Initialization
     *
     * @return null
	 */
	public function init()
	{
        parent::init();
        self::$plugin = $this;

        $this->hasCpSection = $this->hasCpSection();

        /*
        require_once(CRAFT_PLUGINS_PATH.'social/base/LoginProviderInterface.php');
        require_once(CRAFT_PLUGINS_PATH.'social/providers/login/BaseProvider.php');
        */

        $this->setComponents([
            'social' => \dukt\social\services\Social::class,
            'social_loginAccounts' => \dukt\social\services\LoginAccounts::class,
            'social_loginProviders' => \dukt\social\services\LoginProviders::class,
            'social_userSession' => \dukt\social\services\UserSession::class,
        ]);

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, [$this, 'registerCpUrlRules']);

        // $this->initEventListeners();
        $this->initTemplateHooks();
    }

    public function defineTemplateComponent()
    {
        return SocialVariable::class;
    }

    public function registerCpUrlRules(RegisterUrlRulesEvent $event)
    {
        $rules = [
            'social' => 'social/login-accounts/index',

            'social/settings' => 'social/settings/index',
            'social/install' => 'social/install/index',

            'social/loginaccounts' => 'social/loginAccounts/index',
            'social/loginaccounts/<userId:\d+>' => 'social/login-accounts/edit',

            'settings/plugins/social/settings/loginproviders' => 'social/login-providers/index',
            'settings/plugins/social/settings/loginproviders/<handle:{handle}>' => 'social/loginProviders/edit',
            
            'settings/plugins/social/settings/settings' => 'social/settings/index',
        ];

        $event->rules = array_merge($event->rules, $rules);
    }

    /**
     * Control Panel routes.
     *
     * @return mixed
     */
    public function registerCpRoutes()
    {
        return [
            "social" => ['action' => "social/loginAccounts/index"],

            'social/install' => ['action' => "social/install/index"],
            'social/settings' => ['action' => "social/settings/index"],

            "social/loginaccounts" => ['action' => "social/loginAccounts/index"],
            "social/loginaccounts/(?P<userId>\d+)" => ['action' => "social/login-Accounts/edit"],

            'settings/plugins/social/settings/loginproviders' => ['action' => "social/loginProviders/index"],
            'settings/plugins/social/settings/loginproviders/(?P<handle>.*)' => ['action' => "social/loginProviders/edit"],

            'settings/plugins/social/settings/settings' => ['action' => "social/settings/index"],
        ];
    }

	/**
	 * Get Social Login Providers
     *
     * @return array
	 */
	public function getSocialLoginProviders()
	{
/*		require_once(CRAFT_PLUGINS_PATH.'social/providers/login/Facebook.php');
		require_once(CRAFT_PLUGINS_PATH.'social/providers/login/Google.php');
		require_once(CRAFT_PLUGINS_PATH.'social/providers/login/Twitter.php');*/

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

    public function getSettingsResponse()
    {
        $url = \craft\helpers\UrlHelper::cpUrl('settings/plugins/social/settings/loginproviders');

        \Craft::$app->controller->redirect($url);

        return '';
    }

	/**
	 * Get Required Dependencies
     *
     * @return array
	 */
	public function getRequiredPlugins()
	{
		return [
			[
				'name'    => "OAuth",
				'handle'  => 'oauth',
				'url'     => 'https://dukt.net/craft/oauth',
				'version' => '2.0.2'
			]
		];
	}

	/**
	 * Get Name
     *
     * @return string
	 */
	public function getName()
	{
		return Craft::t('app', 'Social Login');
	}

	/**
	 * Get Description
     *
     * @return string
	 */
	public function getDescription()
	{
		return Craft::t('app', 'Let your visitors log into Craft with web services like Facebook, Google, Twitter…');
	}

	/**
	 * Get Version
     *
     * @return string
	 */
	public function getVersion()
	{
		return '1.2.4';
	}


	/**
	 * Schema Version
     *
     * @return string
	 */
	public function getSchemaVersion()
	{
		return '1.0.2';
	}

	/**
	 * Get Developer
     *
     * @return string
	 */
	public function getDeveloper()
	{
		return 'Dukt';
	}

	/**
	 * Get Developer URL
     *
     * @return string
	 */
	public function getDeveloperUrl()
	{
		return 'https://dukt.net/';
	}

	/**
	 * Get Documentation URL
     *
     * @return string
	 */
	public function getDocumentationUrl()
	{
		return 'https://dukt.net/craft/social/docs/';
	}

	/**
	 * Get Release Feed URL
     *
     * @return string
	 */
	public function getReleaseFeedUrl()
	{
		return 'https://dukt.net/craft/social/updates.json';
	}

    /**
     * Get Settings URL
     *
     * @return string
     */
    public function getSettingsUrl()
	{
		return 'settings/plugins/social/settings/loginproviders';
	}

    /**
     * Has CP Section
     *
     * @return bool
     */
    public function hasCpSection()
	{
		$settings = $this->getSettings();

		if ($settings['showCpSection'])
		{
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
            'loginAccounts' => Craft::t('app', 'Login Accounts')
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
        if ($attribute == 'loginAccounts')
        {
            $loginAccounts = $this->social_loginAccounts->getLoginAccountsByUserId($user->id);

            if (!$loginAccounts)
            {
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

        if($this->settings->enableCpLogin)
        {
            if (Craft::$app->request->isCpRequest() && Craft::$app->request->getSegment(1) == 'login')
            {
                $loginProviders = $this->social_loginProviders->getLoginProviders();
                $jsLoginProviders = [];

                foreach($loginProviders as $loginProvider)
                {
                    $jsLoginProvider = [
                        'name' => $loginProvider->getName(),
                        'handle' => $loginProvider->getHandle(),
                        'url' => $this->social->getLoginUrl($loginProvider->getHandle()),
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

		Craft::$app->on('users.onBeforeDeleteUser', function (Event $event)
		{
			$user = $event->params['user'];

            $this->social_loginAccounts->deleteLoginAccountByUserId($user->id);
        });
    }

    /**
     * Initialize template hooks
     *
     * @return null
     */
    private function initTemplateHooks()
    {
        Craft::$app->getView()->hook('cp.users.edit.right-pane', function(&$context)
        {
            if ($context['account'])
            {
	            $context['user'] = $context['account'];
	            $context['loginAccounts'] = $this->social_loginAccounts->getLoginAccountsByUserId($context['account']->id);

                $loginProviders = $this->social_loginProviders->getLoginProviders();
                $context['loginProviders'] = [];

                foreach($loginProviders as $loginProvider)
                {
                    $providerAvailable = true;

                    foreach($context['loginAccounts'] as $loginAccount)
                    {
                        $handle = $loginProvider->getHandle();

                        if($loginProvider->getHandle() == $loginAccount->providerHandle)
                        {
                            $providerAvailable = false;
                        }
                    }

                    if($providerAvailable)
                    {
                        $context['loginProviders'][] = $loginProvider;
                    }
                }

                Craft::$app->getView()->registerAssetBundle(SocialAsset::class);

                return Craft::$app->getView()->renderTemplate('social/users/_edit-pane', $context);
            }
        });
    }
}
