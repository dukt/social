<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2018, Dukt
 * @license   https://github.com/dukt/social/blob/v2/LICENSE.md
 */

namespace dukt\social\events;

use yii\base\Event;

/**
 * RegisterLoginProviderTypesEvent class.
 *
 * @author Dukt <support@dukt.net>
 * @since  2.0
 */
class RegisterLoginProviderTypesEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var array The registered login providers.
     */
    public $loginProviderTypes = [];
}
