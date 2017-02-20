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
     * @return \dukt\social\services\Social The config service
     */
    public function getSocial()
    {
        /** @var Social $this */
        return $this->get('social');
    }

    /**
     * Returns the api service.
     *
     * @return \dukt\social\services\Api The config service
     */
    public function getApi()
    {
        /** @var Social $this */
        return $this->get('api');
    }

    /**
     * Returns the cache service.
     *
     * @return \dukt\social\services\Cache The config service
     */
    public function getCache()
    {
        /** @var Social $this */
        return $this->get('cache');
    }

    /**
     * Returns the oauth service.
     *
     * @return \dukt\social\services\Oauth The config service
     */
    public function getOauth()
    {
        /** @var Social $this */
        return $this->get('oauth');
    }

    /**
     * Returns the reports service.
     *
     * @return \dukt\social\services\Reports The config service
     */
    public function getReports()
    {
        /** @var Social $this */
        return $this->get('reports');
    }
}
