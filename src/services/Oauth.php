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

class Oauth extends Component
{
    public function getProviderInfos($handle)
    {
        $loginProvidersConfig = Craft::$app->getConfig()->get('loginProviders', 'social');

        if(isset($loginProvidersConfig[$handle]))
        {
            return $loginProvidersConfig[$handle];
        }
    }

    public function isProviderConfigured($handle)
    {
        if($this->getProviderInfos($handle))
        {
            return true;
        }

        return false;
    }

    public function connect($options)
    {
        $handle = $options['provider'];

        $loginProvider = Social::$plugin->getLoginProviders()->getLoginProvider($handle);

        Craft::$app->getSession()->set('social.loginProvider', $handle);
        if(Craft::$app->getSession()->get('social.callback') === true)
        {
            Craft::$app->getSession()->remove('social.callback');
            return $loginProvider->oauthCallback();
        }
        else
        {
            return $loginProvider->oauthConnect($options);
        }
    }
}
