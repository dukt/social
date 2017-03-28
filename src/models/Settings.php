<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/social/docs/license
 */

namespace dukt\social\models;

use craft\base\Model;

class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var bool Enable social registration
     */
    public $enableSocialRegistration = true;

    /**
     * @var bool Enable social login
     */
    public $enableSocialLogin = true;

    /**
     * @var array Login providers
     */
    public $loginProviders = [];

    /**
     * @var int|null Default group
     */
    public $defaultGroup;

    /**
     * @var bool Auto fill profile
     */
    public $autoFillProfile = true;

    /**
     * @var bool Show CP section
     */
    public $showCpSection = true;

    /**
     * @var bool Enable social login for the CP
     */
    public $enableCpLogin = false;

    /**
     * @var bool Allow email matching
     */
    public $allowEmailMatch = false;

    /**
     * @var array Lock social registration to specific domains
     */
    public $lockDomains = [];

    /**
     * @var array User mapping
     */
    public $userMapping = [];
}
