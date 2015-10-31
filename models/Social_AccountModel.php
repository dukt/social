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
    // Public Methods
    // =========================================================================

    /**
     * Define Attributes
     */
    public function defineAttributes()
    {
        return array(
            'id' => AttributeType::Number,
            'userId' => AttributeType::Number,
            'tokenId' => AttributeType::Number,

            'providerHandle' => array(AttributeType::String, 'required' => true),
            'socialUid' => array(AttributeType::String, 'required' => true),

            'photo' => AttributeType::String,
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

    /**
     * Get Token
     */
    public function getToken()
    {
        $token = craft()->oauth->getTokenById($this->tokenId);

        return $token;
    }
}
