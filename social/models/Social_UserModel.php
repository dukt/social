<?php

/**
 * Craft OAuth by Dukt
 *
 * @package   Craft OAuth
 * @author    Benjamin David
 * @copyright Copyright (c) 2014, Dukt
 * @license   https://dukt.net/craft/oauth/docs/license
 * @link      https://dukt.net/craft/oauth/
 */

namespace Craft;

class Social_UserModel extends BaseModel
{
    /**
     * Define Attributes
     */
    public function defineAttributes()
    {
        return array(
            'id' => AttributeType::Number,
            'userId' => AttributeType::Number,
            'tokenId' => AttributeType::Number,

            'provider' => array(AttributeType::String, 'required' => true),
            'socialUid' => array(AttributeType::String, 'required' => true),

            'photo' => AttributeType::String,
        );
    }

    public function getUser()
    {
        if ($this->userId) {
            return craft()->users->getUserById($this->userId);
        }
    }

    public function getToken()
    {
        $token = craft()->oauth->getTokenById($this->tokenId);

        return $token;
    }
}
