<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class Social_LoginAccountModel extends BaseElementModel
{
    protected $elementType = 'Social_LoginAccount';

    private $_user;

    // Public Methods
    // =========================================================================

    /**
     * Use the login account's email as its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getUser()->username;
    }

    /**
     * Returns the URL to the element's thumbnail, if there is one.
     *
     * @param int|null $size
     *
     * @return string|null
     */
    public function getThumbUrl($size = 100)
    {
        $url = $this->getUser()->getPhotoUrl($size);

        if (!$url)
        {
            $url = UrlHelper::getResourceUrl('defaultuserphoto');
        }

        return $url;
    }

    /**
     * Define Attributes
     */
    public function defineAttributes()
    {
        return array_merge(parent::defineAttributes(), array(
            'id' => AttributeType::Number,
            'userId' => AttributeType::Number,
            'tokenId' => AttributeType::Number,

            'providerHandle' => array(AttributeType::String, 'required' => true),
            'socialUid' => array(AttributeType::String, 'required' => true),
        ));
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
        if (!isset($this->_user))
        {
            if ($this->userId)
            {
                $this->_user = craft()->users->getUserById($this->userId);
            }
        }

        return $this->_user;
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
