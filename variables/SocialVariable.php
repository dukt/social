<?php

namespace Craft;

class SocialVariable
{
    // --------------------------------------------------------------------

    public function login($providerClass)
    {
        return craft()->social->login($providerClass);
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
