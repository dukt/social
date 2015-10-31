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

    public function getProviders()
    {
        return craft()->social_providers->getProviders();
    }

    /**
     * Get login URL
     *
     * @param $providerHandle
     * @param array  $params
     *
     * @return string
     */
    public function getLoginUrl($providerHandle, $params = array())
    {
        return craft()->social->getLoginUrl($providerHandle, $params);
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
     * Get account by provider handle
     *
     * @param string $providerHandle
     *
     * @return Social_AccountModel|null
     */
    public function getAccountByProvider($providerHandle)
    {
        return craft()->social_accounts->getAccountByProvider($providerHandle);
    }

    /**
     * Get link account URL
     *
     * @param $handle
     *
     * @return string
     */
    public function getLinkAccountUrl($providerHandle)
    {
        return craft()->social->getLinkAccountUrl($providerHandle);
    }

    /**
     * Get unlink account URL
     *
     * @param $handle
     *
     * @return string
     */
    public function getUnlinkAccountUrl($providerHandle)
    {
        return craft()->social->getUnlinkAccountUrl($providerHandle);
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
