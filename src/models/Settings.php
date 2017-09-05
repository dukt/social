<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/social/docs/license
 */

namespace dukt\social\models;

use craft\base\Model;

/**
 * Settings model class.
 *
 * @author Dukt <support@dukt.net>
 * @since   2.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var bool Allow email matching
     */
    public $allowEmailMatch = false;

    /**
     * @var bool Auto fill profile
     */
    public $autoFillProfile = true;

    /**
     * @var int|null Default group
     */
    public $defaultGroup;

    /**
     * @var bool Enable social login for the CP
     */
    public $enableCpLogin = false;

    /**
     * @var bool Enable social login
     */
    public $enableSocialLogin = true;

    /**
     * @var bool Enable social registration
     */
    public $enableSocialRegistration = true;

    /**
     * @var array Lock social registration to specific domains
     */
    public $lockDomains = [];

    /**
     * @var array Enabled login providers
     */
    public $enabledLoginProviders = [];

    /**
     * @var bool Show CP section
     */
    public $showCpSection = false;
}
