<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\base;

use dukt\social\Plugin as Social;

trait PluginTrait
{
    /**
     * Returns the social service.
     *
     * @return \dukt\social\services\Social The social service
     */
    public function getSocial()
    {
        /** @var Social $this */
        return $this->get('social');
    }

    /**
     * Returns the loginAccounts service.
     *
     * @return \dukt\social\services\LoginAccounts The loginAccounts service
     */
    public function getLoginAccounts()
    {
        /** @var Social $this */
        return $this->get('loginAccounts');
    }

    /**
     * Returns the loginProviders service.
     *
     * @return \dukt\social\services\LoginProviders The loginProviders service
     */
    public function getLoginProviders()
    {
        /** @var Social $this */
        return $this->get('loginProviders');
    }

    /**
     * Returns the oauth service.
     *
     * @return \dukt\social\services\Oauth The oauth service
     */
    public function getOauth()
    {
        /** @var Social $this */
        return $this->get('oauth');
    }

    /**
     * Returns the userSession service.
     *
     * @return \dukt\social\services\UserSession The userSession service
     */
    public function getUserSession()
    {
        /** @var Social $this */
        return $this->get('userSession');
    }
}
