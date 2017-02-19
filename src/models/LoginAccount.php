<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\models;

use craft\base\Model;

class LoginAccount extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
	protected $elementType = 'Social_LoginAccount';

    /**
     * @var UserModel|null
     */
	private $_user;

	// Public Methods
	// =========================================================================

	/**
	 * Use the login account's email or username as its string representation.
	 *
	 * @return string
	 */
	public function __toString()
	{
		if (craft()->config->get('useEmailAsUsername'))
		{
			return (string) $this->email;
		}
		else
		{
			return (string) $this->username;
		}
	}

	/**
	 * Returns whether the current user can edit the element.
	 *
	 * @return bool
	 */
	public function isEditable()
	{
		return true;
	}

	/**
	 * Returns the element's CP edit URL.
	 *
	 * @return string|false
	 */
	public function getCpEditUrl()
	{
		return UrlHelper::getCpUrl('social/loginaccounts/'.$this->userId);
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
	    $asset = $this->getUser()->getPhoto();

	    if($asset)
        {
            $url = $asset->getThumbUrl($size);

            if (!$url)
            {
                $url = UrlHelper::getResourceUrl('defaultuserphoto');
            }

            return $url;
        }
	}

	/**
	 * Returns the OAuth provider for this login account.
     *
     * @return IOauth_Provider|null
	 */
	public function getOauthProvider()
	{
		if ($this->providerHandle)
		{
			return Social::$plugin->oauth->getProvider($this->providerHandle);
		}
	}

	/**
	 * Returns the associated Craft user for this login account.
     *
     * @return UserModel
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
	 * Returns the user's full name.
	 *
	 * @return string|null
	 */
	public function getFullName()
	{
		$firstName = trim($this->firstName);
		$lastName = trim($this->lastName);

		return $firstName.($firstName && $lastName ? ' ' : '').$lastName;
	}

    // Protected Methods
    // =========================================================================

    /**
     * @return array
     */
    protected function defineAttributes()
    {
        return array_merge(parent::defineAttributes(), array(
            'id' => AttributeType::Number,
            'userId' => AttributeType::Number,
            'providerHandle' => array(AttributeType::String, 'required' => true),
            'socialUid' => array(AttributeType::String, 'required' => true),

            'username' => AttributeType::String,
            'email' => AttributeType::String,
            'firstName' => AttributeType::String,
            'lastName' => AttributeType::String,
            'lastLoginDate' => AttributeType::DateTime,
        ));
    }
}
