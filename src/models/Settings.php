<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\models;

use craft\base\Model;

class Settings extends Model
{
    // Public Properties
    // =========================================================================

    public $enableSocialRegistration;
    public $enableSocialLogin;
    public $loginProviders;
    public $defaultGroup;
    public $autoFillProfile;
    public $showCpSection;
    public $enableCpLogin;

    // Public Methods
    // =========================================================================

    public function rules()
    {
        return [
        ];
    }
}
