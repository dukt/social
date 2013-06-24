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

    public function logout($providerClass)
    {
        return craft()->social->logout($providerClass);
    }

    // --------------------------------------------------------------------

    public function getAccount($providerClass)
    {
        return craft()->social->getAccount($providerClass);
    }

    // --------------------------------------------------------------------

}
