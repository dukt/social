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

    public function logout()
    {
        return craft()->social->logout();
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

}
