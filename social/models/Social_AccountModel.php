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

    public function getUser()
    {
        if ($this->userId)
        {
            return craft()->users->getUserById($this->userId);
        }
    }
}
