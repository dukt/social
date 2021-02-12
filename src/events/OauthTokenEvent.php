<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2021, Dukt
 * @license   https://github.com/dukt/social/blob/v2/LICENSE.md
 */

namespace dukt\social\events;

use dukt\social\models\Token;
use yii\base\Event;

/**
 * OauthTokenEvent class.
 *
 * @author Dukt <support@dukt.net>
 * @since  2.0
 */
class OauthTokenEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var Token|null The OAuth token.
     */
    public $token;
}
