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
     * @var array Login providers
     */
    public $loginProviders = [];

    /**
     * @var bool Show CP section
     */
    public $showCpSection = true;

    /**
     * @var array User mapping
     */
    public $userMapping = [];
}
