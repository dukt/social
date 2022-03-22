<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2021, Dukt
 * @license   https://github.com/dukt/social/blob/v2/LICENSE.md
 */

namespace dukt\social\services;

use Craft;
use dukt\social\base\LoginProvider;
use dukt\social\events\RegisterLoginProviderTypesEvent;
use dukt\social\Plugin;
use yii\base\Component;
use dukt\social\base\LoginProviderInterface;
use dukt\social\loginproviders\Facebook;
use dukt\social\loginproviders\Twitter;
use dukt\social\loginproviders\Google;

/**
 * The LoginProviders service provides APIs for managing login providers in Craft.
 *
 * An instance of the LoginProviders service is globally accessible in Craft via [[Plugin::loginProviders `Plugin::getInstance()->getLoginProviders()`]].
 *
 * @author  Dukt <support@dukt.net>
 * @since   1.0
 */
class LoginProviders extends Component
{
    // Constants
    // =========================================================================

    /**
     * @event RegisterLoginProviderTypesEvent The event that is triggered when registering login providers.
     */
    public const EVENT_REGISTER_LOGIN_PROVIDER_TYPES = 'registerLoginProviderTypes';

    // Public Methods
    // =========================================================================
    /**
     * Disable a login provider by handle.
     *
     *
     * @return bool
     */
    public function disableLoginProvider(string $handle): bool
    {
        $settings = Plugin::getInstance()->getSettings();

        $enabledLoginProviders = $settings->enabledLoginProviders;

        if (($key = array_search($handle, $enabledLoginProviders, true)) !== false) {
            unset($enabledLoginProviders[$key]);
        }

        $settings->enabledLoginProviders = $enabledLoginProviders;

        return Plugin::getInstance()->savePluginSettings($settings->getAttributes());
    }

    /**
     * Enable a login provider by handle.
     *
     *
     * @return bool
     */
    public function enableLoginProvider(string $handle): bool
    {
        $plugin = Craft::$app->getPlugins()->getPlugin('social');
        $settings = $plugin->getSettings();

        $enabledLoginProviders = $settings->enabledLoginProviders;

        if (!in_array($handle, $enabledLoginProviders, true)) {
            $enabledLoginProviders[] = $handle;
        }

        $settings->enabledLoginProviders = $enabledLoginProviders;

        return Plugin::getInstance()->savePluginSettings($settings->getAttributes());
    }

    /**
     * Get a login provider by handle.
     *
     * @param bool|true $enabledOnly
     * @return LoginProvider|LoginProviderInterface|null
     */
    public function getLoginProvider(string $handle, $enabledOnly = true)
    {
        $loginProviders = $this->getLoginProviders($enabledOnly);

        foreach ($loginProviders as $loginProvider) {
            if ($loginProvider->getHandle() == $handle) {
                return $loginProvider;
            }
        }
    }

    /**
     * Get login providers.
     *
     * @param bool|true $enabledOnly
     *
     * @return array
     */
    public function getLoginProviders($enabledOnly = true): array
    {
        return $this->_getLoginProviders($enabledOnly);
    }

    // Private Methods
    // =========================================================================
    /**
     * Get login providers and instantiate them.
     *
     *
     * @return array
     */
    private function _getLoginProviders(bool $enabledOnly): array
    {
        $loginProviderTypes = $this->_getLoginProviderTypes();

        $loginProviders = [];

        foreach ($loginProviderTypes as $loginProviderType) {
            $loginProvider = $this->_createLoginProvider($loginProviderType);

            if (!$enabledOnly || ($enabledOnly && $loginProvider->getIsEnabled())) {
                $key = substr($loginProviderType, strrpos($loginProviderType, "\\") + 1);
                $loginProviders[$key] = $loginProvider;
            }
        }

        ksort($loginProviders);

        return $loginProviders;
    }

    /**
     * Returns login provider types.
     *
     * @return array
     */
    private function _getLoginProviderTypes(): array
    {
        $loginProviderTypes = [
            Facebook::class,
            Google::class,
            Twitter::class,
        ];

        $eventName = self::EVENT_REGISTER_LOGIN_PROVIDER_TYPES;

        $event = new RegisterLoginProviderTypesEvent([
            'loginProviderTypes' => $loginProviderTypes
        ]);

        $this->trigger($eventName, $event);

        return $event->loginProviderTypes;
    }

    /**
     * Instantiates an OAuth provider.
     *
     * @param $socialLoginProviderType
     *
     * @return LoginProviderInterface
     */
    private function _createLoginProvider($socialLoginProviderType): LoginProviderInterface
    {
        return new $socialLoginProviderType;
    }
}
