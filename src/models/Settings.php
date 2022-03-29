<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/social/blob/v2/LICENSE.md
 */

namespace dukt\social\models;

use craft\base\Model;

/**
 * Settings model class.
 *
 * @author  Dukt <support@dukt.net>
 * @since   2.0
 */
class Settings extends Model
{
    // Properties
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
     * @var array Enabled login providers
     */
    public $enabledLoginProviders = [];

    /**
     * @var bool Enable social login
     */
    public $enableSocialLogin = true;

    /**
     * @var bool Enable social registration
     */
    public $enableSocialRegistration = true;

    /**
     * @var array Defines the login providers.
     */
    public $loginProviders = [];

    /**
     * @var array Lock social registration to specific domains
     */
    public $lockDomains = [];

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['allowEmailMatch', 'autoFillProfile', 'enableCpLogin', 'enableSocialLogin', 'enableSocialRegistration'], 'boolean'],
            [['defaultGroup'], 'number', 'integerOnly' => true],
        ];
    }
}
