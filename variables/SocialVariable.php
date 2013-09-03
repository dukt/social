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
}
