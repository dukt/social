<?php

namespace Craft;

class SocialService extends BaseApplicationComponent
{
    // --------------------------------------------------------------------

    public function login($providerClass)
    {
        return craft()->oauth->connect('social.user', $providerClass, true);
    }

    // --------------------------------------------------------------------

    public function logout($providerClass)
    {
        return craft()->oauth->disconnect('social.user', $providerClass);
    }

    // --------------------------------------------------------------------

    public function getAccount($providerClass)
    {
        $account = craft()->oauth->getAccount('social.user', $providerClass);

        return $account;
    }

    // --------------------------------------------------------------------
}

