<?php

/**
 * Social Login for Craft
 *
 * @package   Social Login
 * @author    Benjamin David
 * @copyright Copyright (c) 2014, Dukt
 * @link      https://dukt.net/craft/social/
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class SocialVariable
{
    private $_error = false;

    public function getNotice()
    {
        return craft()->userSession->getFlash('notice');
        // craft()->userSession->setNotice(Craft::t('User saved.'));

        // if(!$this->_error) {
        //     $this->_error = craft()->httpSession->get('notice');
        //     craft()->httpSession->remove('notice');
        // }

        // return $this->_error;
    }

    public function getError()
    {
        if(!$this->_error) {
            $this->_error = craft()->httpSession->get('error');
            craft()->httpSession->remove('error');
        }

        return $this->_error;
    }

    public function getAccountByUserId($id)
    {
        return craft()->social->getAccountByUserId($id);
    }

    public function getUsers()
    {
        return craft()->social->getUsers();
    }

    public function getUserByProvider($handle)
    {
        return craft()->social->getUserByProvider($handle);
    }

    public function getProvider($handle, $configuredOnly = true)
    {
        return craft()->social->getProvider($handle, $configuredOnly);
    }

    public function getProviders($configuredOnly = true)
    {
        return craft()->social->getProviders($configuredOnly);
    }

    public function getConnectUrl($handle)
    {
        return craft()->social->getConnectUrl($handle);
    }

    public function getDisconnectUrl($handle)
    {
        return craft()->social->getDisconnectUrl($handle);
    }

    public function getLoginUrl($providerClass, $params)
    {
        return craft()->social->getLoginUrl($providerClass, $params);
    }

    public function getLogoutUrl($redirect = null)
    {
        return craft()->social->getLogoutUrl($redirect);
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
