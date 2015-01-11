<?php

/**
 * Social plugin for Craft
 *
 * @package   Craft Social
 * @author    Benjamin David
 * @copyright Copyright (c) 2015, Dukt
 * @link      https://dukt.net/craft/social/
 * @license   https://dukt.net/craft/social/docs/license
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
