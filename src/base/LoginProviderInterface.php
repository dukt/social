<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2018, Dukt
 * @license   https://github.com/dukt/social/blob/v2/LICENSE.md
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
    public function getName(): string;
}
