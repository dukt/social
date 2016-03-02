<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class Social_LoginAccountModel extends BaseModel
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
        );
    }

    /**
     * Get the OAuth provider for the social account.
     */
    public function getOauthProvider()
    {
        if ($this->providerHandle)
        {
            return craft()->oauth->getProvider($this->providerHandle);
        }
    }

    /**
     * Get the associated Craft user for this social account.
     */
    public function getUser()
    {
        if ($this->userId)
        {
            return craft()->users->getUserById($this->userId);
        }
    }

    /**
     * Get the OAuth token for the social account.
     */
    public function getToken()
    {
        $token = craft()->oauth->getTokenById($this->tokenId);

        return $token;
    }
}
