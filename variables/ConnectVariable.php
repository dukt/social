<?php

namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'connect/vendor/autoload.php');

use ReflectionClass;
use Symfony\Component\Finder\Finder;

class ConnectVariable
{
    public function getProviders()
    {
        return craft()->connect->getProviders();
    }

    public function getServiceByProviderClass($providerClass)
    {
        return craft()->connect->getServiceByProviderClass($providerClass);
    }

    public function outputToken($providerClass)
    {
        return craft()->connect->outputToken($providerClass);
    }
}
