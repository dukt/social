<?php

/**
 * Social plugin for Craft
 *
 * @package   Craft Social
 * @author    Benjamin David
 * @copyright Copyright (c) 2015, Dukt
 * @link      https://dukt.net/craft/social/
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class SocialVariable
{
    // Properties
    // =========================================================================

    private $_error = false;
    private $_notice = false;

    // Public Methods
    // =========================================================================

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
        return craft()->social_accounts->getAccountByUserId($id);
    }

    public function getUserByProvider($handle)
    {
        return craft()->social_users->getUserByProvider($handle);
    }

    public function getProvider($handle, $configuredOnly = true)
    {
        return craft()->social_providers->getProvider($handle, $configuredOnly);
    }

    public function getProviders($configuredOnly = true)
    {
        try
        {
            return craft()->social_providers->getProviders($configuredOnly);
        }
        catch(\Exception $e)
        {
            return array();
        }
    }

    public function getLoginUrl($providerClass, $params = array())
    {
        return craft()->social->getLoginUrl($providerClass, $params);
    }

    public function getLogoutUrl($redirect = null)
    {
        return craft()->social->getLogoutUrl($redirect);
    }

    public function getLinkAccountUrl($handle)
    {
        return craft()->social->getLinkAccountUrl($handle);
    }

    public function getUnlinkAccountUrl($handle)
    {
        return craft()->social->getUnlinkAccountUrl($handle);
    }
}
