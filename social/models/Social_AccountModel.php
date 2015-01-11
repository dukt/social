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

class Social_AccountModel extends BaseModel
{
    /**
     * Define Attributes
     */
    public function defineAttributes()
    {
        return array(
            'id' => AttributeType::Number,
            'userId' => AttributeType::Number,
            'hasEmail' => AttributeType::Bool,
            'hasPassword' => AttributeType::Bool,
            'temporaryEmail' => AttributeType::String,
            'temporaryPassword' => AttributeType::String,
        );
    }

    /**
     * Get User
     */
    public function getUser()
    {
        if ($this->userId)
        {
            return craft()->users->getUserById($this->userId);
        }
    }
}
