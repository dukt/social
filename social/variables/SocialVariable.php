<?php

/**
 * Social Login for Craft
 *
 * @package   Social Login
 * @author    Benjamin David
 * @copyright Copyright (c) 2013, Dukt
 * @link      http://dukt.net/craft/social/
 * @license   http://dukt.net/craft/social/docs/license
 */

namespace Craft;

class SocialVariable
{
    private $_error = false;

    public function getError()
    {
        if(!$this->_error) {
            $this->_error = craft()->httpSession->get('error');
            craft()->httpSession->remove('error');
        }

        return $this->_error;
    }

    public function getProvider($handle, $configuredOnly = true)
    {
        return craft()->social->getProvider($handle, $configuredOnly);
    }

    public function getProviders($configuredOnly = true)
    {
        return craft()->social->getProviders($configuredOnly);
    }

    public function login($providerClass, $redirect = null, $scope = null, $errorRedirect = null)
    {
        return craft()->social->login($providerClass, $redirect, $scope, $errorRedirect);
    }

    public function logout($redirect = null)
    {
        return craft()->social->logout($redirect);
    }

    public function isTemporaryEmail($email)
    {
        return craft()->social->isTemporaryEmail($email);
    }

    public function getTemporaryPassword($userId)
    {
        return craft()->social->getTemporaryPassword($userId);
    }

    public function userHasTemporaryUsername($userId)
    {
        return craft()->social->userHasTemporaryUsername($userId);
    }
}
