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

    public function logout()
    {
        return UrlHelper::getActionUrl('social/public/logout');
    }

    // --------------------------------------------------------------------

    public function connect($providerClass)
    {
        return craft()->oauth->connect('social.user', $providerClass, true);
    }

    // --------------------------------------------------------------------

    public function disconnect($providerClass)
    {
        return craft()->oauth->disconnect('social.user', $providerClass);
    }

    // --------------------------------------------------------------------

    public function getAccount($providerClass)
    {
        try {
            $account = craft()->oauth->getAccount('social.user', $providerClass, true);
        } catch(\Exception $e) {
            return false;
        }

        return $account;
    }

    // --------------------------------------------------------------------
}

