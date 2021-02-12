<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2021, Dukt
 * @license   https://github.com/dukt/social/blob/v2/LICENSE.md
 */

namespace dukt\social\models;

use craft\base\Model;

/**
 * Token model class.
 *
 * @author  Dukt <support@dukt.net>
 * @since   2.0
 */
class Token extends Model
{
    /**
     * @var string|null Provider handle
     */
    public $providerHandle;

    /**
     * @var mixed|null Token
     */
    public $token;
}
