<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2019, Dukt
 * @license   https://github.com/dukt/social/blob/v2/LICENSE.md
 */

namespace dukt\social\base;

use dukt\social\Plugin;

/**
 * PluginTrait implements the common methods and properties for plugin classes.
 *
 * @property \dukt\social\services\LoginAccounts $loginAccounts      The loginAccounts service
 * @property \dukt\social\services\LoginProviders $loginProviders     The loginProviders service
 */
trait PluginTrait
{
    /**
     * Returns the loginAccounts service.
     *
     * @return \dukt\social\services\LoginAccounts The loginAccounts service
     * @throws \yii\base\InvalidConfigException
     */
    public function getLoginAccounts()
    {
        /** @var Plugin $this */
        return $this->get('loginAccounts');
    }

    /**
     * Returns the loginProviders service.
     *
     * @return \dukt\social\services\LoginProviders The loginProviders service
     * @throws \yii\base\InvalidConfigException
     */
    public function getLoginProviders()
    {
        /** @var Plugin $this */
        return $this->get('loginProviders');
    }
}
