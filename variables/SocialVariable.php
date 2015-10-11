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

	/**
     * Get gateway
     *
     * @param string $gatewayHandle
     * @param bool|true $configuredOnly
     *
     * @return object
     */
    public function getGateway($gatewayHandle, $configuredOnly = true)
    {
        return craft()->social_gateways->getGateway($gatewayHandle, $configuredOnly);
    }

    /**
     * Get gateways
     *
     * @return array
     */
    public function getGateways($configuredOnly = true)
    {
        return craft()->social_gateways->getGateways($configuredOnly);
    }

    /**
     * Get login URL
     *
     * @param $gatewayHandle
     * @param array  $params
     *
     * @return string
     */
    public function getLoginUrl($gatewayHandle, $params = array())
    {
        return craft()->social->getLoginUrl($gatewayHandle, $params);
    }

    /**
     * Get logout URL
     *
     * @param string|null $redirect
     *
     * @return string
     */
    public function getLogoutUrl($redirect = null)
    {
        return craft()->social->getLogoutUrl($redirect);
    }

    /**
     * Get account by gateway handle
     *
     * @param string $gatewayHandle
     *
     * @return Social_AccountModel|null
     */
    public function getAccountByGateway($gatewayHandle)
    {
        return craft()->social_accounts->getAccountByGateway($gatewayHandle);
    }

    /**
     * Get link account URL
     *
     * @param $handle
     *
     * @return string
     */
    public function getLinkAccountUrl($gatewayHandle)
    {
        return craft()->social->getLinkAccountUrl($gatewayHandle);
    }

    /**
     * Get unlink account URL
     *
     * @param $handle
     *
     * @return string
     */
    public function getUnlinkAccountUrl($gatewayHandle)
    {
        return craft()->social->getUnlinkAccountUrl($gatewayHandle);
    }

    /**
     * Get social user from a Craft user ID
     *
     * @param $userId
     *
     * @return Social_UserModel
     */
    public function getSocialUserByUserId($id)
    {
        return craft()->social_users->getSocialUserByUserId($id);
    }

	/**
     * Retrieve a notice stored in the user’s flash data
     *
     * @return string
     */
    public function getNotice()
    {
        if(!$this->_notice)
        {
            $this->_notice = craft()->userSession->getFlash('notice');
        }

        return $this->_notice;
    }

    /**
     * Retrieve an error stored in the user’s flash data
     *
     * @return string
     */
    public function getError()
    {
        if(!$this->_error)
        {
            $this->_error = craft()->userSession->getFlash('error');
        }

        return $this->_error;
    }

}
