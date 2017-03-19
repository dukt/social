<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/social/docs/license
 */

namespace dukt\social\models;

use craft\base\Model;

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
