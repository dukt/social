<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\base;

use dukt\social\Plugin as Social;

/**
 * PluginTrait implements the common methods and properties for plugin classes.
 *
 * @property \dukt\social\services\Social           $social             The social service
 * @property \dukt\social\services\LoginAccounts    $loginAccounts      The loginAccounts service
 * @property \dukt\social\services\LoginProviders   $loginProviders     The loginProviders service
 * @property \dukt\social\services\Oauth            $oauth              The oauth service
 * @property \dukt\social\services\UserSession      $userSession        The userSession service
 */
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
