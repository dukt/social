<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class SocialPlugin extends BasePlugin
{
    // Public Methods
    // =========================================================================

    /**
     * Initialization
     */
    public function init()
    {
        require_once(CRAFT_PLUGINS_PATH.'social/etc/providers/ISocial_Provider.php');
        require_once(CRAFT_PLUGINS_PATH.'social/providers/login/BaseProvider.php');

        $this->initEventListeners();
        $this->initTemplateHooks();
    }

    /**
     * Get Social Login Providers
     */
    public function getSocialLoginProviders()
    {
        require_once(CRAFT_PLUGINS_PATH.'social/providers/login/Facebook.php');
        require_once(CRAFT_PLUGINS_PATH.'social/providers/login/Google.php');
        require_once(CRAFT_PLUGINS_PATH.'social/providers/login/Twitter.php');

        return [
            'Dukt\Social\LoginProviders\Facebook',
            'Dukt\Social\LoginProviders\Google',
            'Dukt\Social\LoginProviders\Twitter',
        ];
    }


    /**
     * Get Required Dependencies
     */
    public function getRequiredPlugins()
    {
        return [
            [
                'name'    => "OAuth",
                'handle'  => 'oauth',
                'url'     => 'https://dukt.net/craft/oauth',
                'version' => '1.0.0'
            ]
        ];
    }

    /**
     * Get Name
     */
    public function getName()
    {
        return Craft::t('Social Login');
    }

    /**
     * Get Description
     */
    public function getDescription()
    {
        return Craft::t('Let your visitors log into Craft with web services like Facebook, Google, Twitter…');
    }

    /**
     * Get Version
     */
    public function getVersion()
    {
        $path = CRAFT_PLUGINS_PATH.'social/Info.php';

        if(IOHelper::fileExists($path))
        {
            require_once($path);

            return SOCIAL_VERSION;
        }

        return '1.0.0';
    }


    /**
     * Get SchemaVersion
     */
    public function getSchemaVersion()
    {
        return '1.0.2';
    }

    /**
     * Get Developer
     */
    public function getDeveloper()
    {
        return 'Dukt';
    }

    /**
     * Get Developer URL
     */
    public function getDeveloperUrl()
    {
        return 'https://dukt.net/';
    }

    /**
     * Get Documentation URL
     */
    public function getDocumentationUrl()
    {
        return 'https://dukt.net/craft/social/docs/';
    }

    /**
     * Get Release Feed URL
     */
    public function getReleaseFeedUrl()
    {
        return 'https://dukt.net/craft/social/updates.json';
    }

    /**
     * Get Settings URL
     */
    public function getSettingsUrl()
    {
        return 'settings/plugins/social/settings/loginproviders';
    }

    /**
     * Has CP Section
     */
    public function hasCpSection()
    {
        $socialPlugin = craft()->plugins->getPlugin('social');
        $settings = $socialPlugin->getSettings();

        if ($settings['showCpSection'])
        {
            return true;
        }

        return false;
    }

    /**
     * Hook Register CP Routes
     */
    public function registerCpRoutes()
    {
        return [
            "social" => ['action' => "social/loginAccounts/index"],

            'social/install' => ['action' => "social/plugin/install"],

            "social/loginaccounts" => ['action' => "social/loginAccounts/index"],
            "social/loginaccounts/(?P<userId>\d+)" => ['action' => "social/loginAccounts/edit"],

            'settings/plugins/social/settings/loginproviders' => ['action' => "social/loginProviders/index"],
            'settings/plugins/social/settings/loginproviders/(?P<handle>.*)' => ['action' => "social/loginProviders/edit"],

            'settings/plugins/social/settings/settings' => ['action' => "social/settings/index"],
        ];
    }

    public function defineAdditionalUserTableAttributes()
    {
        return [
            'loginAccounts' => Craft::t('Login Accounts')
        ];
    }

    public function getUserTableAttributeHtml(UserModel $user, $attribute)
    {
        if ($attribute == 'loginAccounts')
        {
            $providerHandles = $this->_getProviderHandlesByUserId($user->id);

            if (!$providerHandles)
            {
                return '';
            }

            $html = implode(', ', $providerHandles);

            return $html;
        }
    }

    /**
     * On Before Uninstall
     */
    public function onBeforeUninstall()
    {
        if (isset(craft()->oauth))
        {
            craft()->oauth->deleteTokensByPlugin('social');
        }
    }

    // Protected Methods
    // =========================================================================

    /**
     * Define Settings
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
        ];
    }

    // Private Methods
    // =========================================================================

    /**
     * Initialize event listeners
     */
    private function initEventListeners()
    {
        // delete social user when craft user is deleted

        craft()->on('users.onBeforeDeleteUser', function (Event $event)
        {
            $user = $event->params['user'];

            craft()->social_loginAccounts->deleteLoginAccountByUserId($user->id);
        });
    }

    /**
     * Initialize template hooks
     */
    private function initTemplateHooks()
    {
        craft()->templates->hook('cp.users.edit.right-pane', function(&$context)
        {
            $account = $context['account'];

            if ($account)
            {
                $variables = [
                    'user' => $account,
                    'providerHandles' => $this->_getProviderHandlesByUserId($account->id),
                ];

                $html = craft()->templates->render('social/users/_edit-pane', $variables, true);

                return $html;
            }
        });
    }

    /**
     * Returns the provider handles for a given user id
     *
     * @param int $userId
     *
     * @return array|null
     */
    private function _getProviderHandlesByUserId($userId)
    {
        BusinessLogicPlugin::log($userId);

        $loginAccounts = craft()->social_loginAccounts->getLoginAccountsByUserId($userId);

        BusinessLogicPlugin::log(print_r($loginAccounts, true));

        if (!is_array($loginAccounts))
        {
            return null;
        }

        $providerHandles = [];

        foreach ($loginAccounts as $loginAccount)
        {
            $providerHandles[] = $loginAccount->providerHandle;
        }

        return $providerHandles;
    }
}
