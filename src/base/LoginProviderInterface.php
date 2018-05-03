<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2018, Dukt
 * @license   https://dukt.net/social/docs/license
 */

namespace dukt\social\base;

use dukt\social\models\Token;

/**
 * LoginProviderInterface defines the common interface to be implemented by login provider classes.
 *
 * @author  Dukt <support@dukt.net>
 * @since   1.0
 */
interface LoginProviderInterface
{
    // Public Methods
    // =========================================================================

    /**
     * Returns the name of the login provider.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns a profile from an OAuth token.
     *
     * @param Token $token
     *
     * @return array|null
     */
    public function getProfile(Token $token);
}
