<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\services;

use Craft;
use yii\base\Component;

class Oauth extends Component
{
    public function getProviderInfos($handle)
    {
        $loginProvidersConfig = Craft::$app->config->get('loginProviders', 'social');

        if(isset($loginProvidersConfig[$handle]))
        {
            return $loginProvidersConfig[$handle];
        }
    }

    public function getProvider($handle, $configuredOnly)
    {

    }
}
