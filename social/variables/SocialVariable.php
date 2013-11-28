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
    public function getError()
    {
        return craft()->userSession->getFlash('error');
    }

    public function login($providerClass, $redirect = null, $scope = null)
    {
        return craft()->social->login($providerClass, $redirect, $scope);
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
