<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/social/docs/license
 */

namespace dukt\social\base;

use dukt\social\models\Token;

interface LoginProviderInterface
{
    // Public Methods
    // =========================================================================

    /**
     * Returns the name of the login provider
     *
     * @return string
     */
    public function getName();

    /**
     * Returns a profile from an OAuth token
     *
     * @param Token $token
     *
     * @return array|null
     */
    public function getProfile(Token $token);
}