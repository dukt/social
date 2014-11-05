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
    private $_notice = false;

    public function getNotice()
    {
        if(!$this->_notice)
        {
            $this->_notice = craft()->userSession->getFlash('notice');
        }

        return $this->_notice;
    }

    public function getError()
    {
        if(!$this->_error)
        {
            $this->_error = craft()->userSession->getFlash('error');
        }

        return $this->_error;
    }

    public function getAccountByUserId($id)
    {
        return craft()->social->getAccountByUserId($id);
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

    public function getLoginUrl($providerClass, $params = array())
    {
        return craft()->social->getLoginUrl($providerClass, $params);
    }

    public function getLogoutUrl($redirect = null)
    {
        return craft()->social->getLogoutUrl($redirect);
    }
}
