<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/social/docs/license
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
