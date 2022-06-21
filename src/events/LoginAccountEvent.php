<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/social/blob/v2/LICENSE.md
 */

namespace dukt\social\events;

use craft\web\User;
use dukt\social\base\LoginProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use yii\base\Event;

/**
 * LoginAccountEvent class.
 *
 * @author Dukt <support@dukt.net>
 * @since  2.0
 */
class LoginAccountEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var LoginProvider|null The login provider associated with the event.
     */
    public $loginProvider;

    /**
     * @var ResourceOwnerInterface|array|null The profile associated with the event.
     */
    public $profile;

    /**
     * @var User|null The user model associated with the event.
     */
    public $user;
}
