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

    public function getGateway($gatewayHandle, $configuredOnly = true)
    {
        return craft()->social_gateways->getGateway($gatewayHandle, $configuredOnly);
    }

    public function getGateways($configuredOnly = true)
    {
        return craft()->social_gateways->getGateways($configuredOnly);
    }

    public function getLoginUrl($gatewayHandle, $params = array())
    {
        return craft()->social->getLoginUrl($gatewayHandle, $params);
    }

    public function getLogoutUrl($redirect = null)
    {
        return craft()->social->getLogoutUrl($redirect);
    }

    public function getAccountByGateway($gatewayHandle)
    {
        return craft()->social_accounts->getAccountByGateway($gatewayHandle);
    }

    public function getLinkAccountUrl($gatewayHandle)
    {
        return craft()->social->getLinkAccountUrl($gatewayHandle);
    }

    public function getUnlinkAccountUrl($gatewayHandle)
    {
        return craft()->social->getUnlinkAccountUrl($gatewayHandle);
    }

    public function getSocialUserByUserId($id)
    {
        return craft()->social_users->getSocialUserByUserId($id);
    }

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

}
