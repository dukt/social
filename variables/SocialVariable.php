<?php

namespace Craft;

class SocialVariable
{
    // --------------------------------------------------------------------

    public function login($providerClass, $redirect = null)
    {
        return craft()->social->login($providerClass, $redirect);
    }

    // --------------------------------------------------------------------

    public function logout($redirect = null)
    {
        return craft()->social->logout($redirect);
    }

    // --------------------------------------------------------------------

    public function connect($providerClass)
    {
        return craft()->social->connect($providerClass);
    }

    // --------------------------------------------------------------------

    public function disconnect($providerClass)
    {
        return craft()->social->disconnect($providerClass);
    }

    // --------------------------------------------------------------------

    public function getAccount($providerClass)
    {
        return craft()->social->getAccount($providerClass);
    }

    // --------------------------------------------------------------------

    public function getProvider($providerClass, $configuredOnly = true)
    {
        return craft()->oauth->getProvider($providerClass, $configuredOnly);
    }

    // --------------------------------------------------------------------

    public function getProviders($configuredOnly = true)
    {
        return craft()->oauth->getProviders($configuredOnly);
    }

    // --------------------------------------------------------------------

    public function getToken($providerClass)
    {
        return craft()->social->getToken($providerClass);
    }
}
