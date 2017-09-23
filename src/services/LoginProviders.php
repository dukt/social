<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/social/docs/license
 */

namespace dukt\social\services;

use Craft;
use dukt\social\base\LoginProvider;
use dukt\social\Plugin;
use yii\base\Component;
use Dukt\Social\Base\LoginProviderInterface;

/**
 * The LoginProviders service provides APIs for managing login providers in Craft.
 *
 * An instance of the LoginProviders service is globally accessible in Craft via [[Plugin::loginProviders `Plugin::getInstance()->getLoginProviders()`]].
 *
 * @author Dukt <support@dukt.net>
 * @since   1.0
 */
class LoginProviders extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Disable a login provider by handle.
     *
     * @param string $handle
     *
     * @return bool
     */
    public function disableLoginProvider($handle)
    {
        $plugin = Craft::$app->getPlugins()->getPlugin('social');
        $settings = $plugin->getSettings();

        $enabledLoginProviders = $settings->enabledLoginProviders;

        if (($key = array_search($handle, $enabledLoginProviders)) !== false) {
            unset($enabledLoginProviders[$key]);
        }

        $settings->enabledLoginProviders = $enabledLoginProviders;

        return Craft::$app->getPlugins()->savePluginSettings($plugin, $settings->getAttributes());
    }

    /**
     * Enable a login provider by handle.
     *
     * @param string $handle
     *
     * @return bool
     */
    public function enableLoginProvider($handle)
    {
        $plugin = Craft::$app->getPlugins()->getPlugin('social');
        $settings = $plugin->getSettings();

        $enabledLoginProviders = $settings->enabledLoginProviders;

        if (!in_array($handle, $enabledLoginProviders)) {
            $enabledLoginProviders[] = $handle;
        }

        $settings->enabledLoginProviders = $enabledLoginProviders;

        return Craft::$app->getPlugins()->savePluginSettings($plugin, $settings->getAttributes());
    }

    /**
     * Get a login provider by handle.
     *
     * @param string    $handle
     * @param bool|true $enabledOnly
     *
     * @return LoginProvider|LoginProviderInterface|null
     */
    public function getLoginProvider($handle, $enabledOnly = true)
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
    public function getLoginProviders($enabledOnly = true)
    {
        return $this->_getLoginProviders($enabledOnly);
    }

    // Private Methods
    // =========================================================================

    /**
     * Get login providers and instantiate them.
     *
     * @param bool $enabledOnly
     *
     * @return array
     */
    private function _getLoginProviders($enabledOnly)
    {
        // Fetch Social's login providers

        $loginProviderTypes = Plugin::$plugin->getSocialLoginProviders();


        // Fetch login providers from other plugins

        foreach (Craft::$app->getPlugins()->getAllPlugins() as $plugin) {
            if (method_exists($plugin, 'getSocialLoginProviders')) {
                $pluginLoginProviders = $plugin->getSocialLoginProviders();

                foreach ($pluginLoginProviders as $pluginLoginProvider) {
                    $alreadyExists = false;
                    foreach ($loginProviderTypes as $loginProviderType) {
                        if ($loginProviderType === $pluginLoginProvider) {
                            $alreadyExists = true;
                        }
                    }

                    if (!$alreadyExists) {
                        array_push($loginProviderTypes, $pluginLoginProvider);
                    }
                }
            }
        }

        // Instantiate providers

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
     * Instantiates an OAuth provider.
     *
     * @param $socialLoginProviderType
     *
     * @return LoginProviderInterface
     */
    private function _createLoginProvider($socialLoginProviderType)
    {
        return new $socialLoginProviderType;
    }
}
