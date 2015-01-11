<?php

/**
 * Craft Social Login by Dukt
 *
 * @package   Craft Social Login
 * @author    Benjamin David
 * @copyright Copyright (c) 2014, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 * @link      https://dukt.net/craft/social/
 */

namespace Craft;

class Social_CompleteRegistrationModel extends BaseModel
{
    /**
     * Define Attributes
     */
    public function defineAttributes()
    {
        return array(
            'email'    => array(AttributeType::Email, 'required' => true),
            'password'    => array(AttributeType::String, 'required' => false),
        );
    }
}
