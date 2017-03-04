<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\services;

use Craft;
use dukt\social\Plugin as Social;
use yii\base\Component;

/**
 * Class Oauth
 *
 * @package dukt\social\services
 */
class Oauth extends Component
{
    /**
     * OAuth connect.
     * 
     * @param $loginProviderHandle
     *
     * @return mixed
     */
    public function connect($loginProviderHandle)
    {
        $loginProvider = Social::$plugin->getLoginProviders()->getLoginProvider($loginProviderHandle);

        Craft::$app->getSession()->set('social.loginProvider', $loginProviderHandle);

        if (Craft::$app->getSession()->get('social.callback') === true) {
            Craft::$app->getSession()->remove('social.callback');

            return $loginProvider->oauthCallback();
        } else {
            return $loginProvider->oauthConnect();
        }
    }

    /**
     * Is OAuth provider configured.
     *
     * @param $loginProviderHandle
     *
     * @return bool
     */
    public function isProviderConfigured($loginProviderHandle)
    {
        if ($this->getProviderInfos($loginProviderHandle)) {
            return true;
        }

        return false;
    }

    /**
     * Get provider infos.
     *
     * @param $loginProviderHandle
     *
     * @return mixed
     */
    public function getProviderInfos($loginProviderHandle)
    {
        $loginProvidersConfig = Craft::$app->getConfig()->get('loginProviders', 'social');

        if (isset($loginProvidersConfig[$loginProviderHandle])) {
            return $loginProvidersConfig[$loginProviderHandle];
        }
    }
}