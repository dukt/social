<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
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
		if (craft()->config->get('useEmailAsUsername'))
		{
			return $this->email;
		}
		else
		{
			return $this->username;
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
			'providerHandle' => array(AttributeType::String, 'required' => true),
			'socialUid' => array(AttributeType::String, 'required' => true),

			'username' => AttributeType::String,
			'email' => AttributeType::String,
			'firstName' => AttributeType::String,
			'lastName' => AttributeType::String,
			'lastLoginDate' => AttributeType::DateTime,
		));
	}

	/**
	 * Get the OAuth provider for the social account.
	 */
	public function getOauthProvider()
	{
		if ($this->providerHandle)
		{
            Craft::app()->social->checkPluginRequirements();
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
	 * Gets the user's full name.
	 *
	 * @return string|null
	 */
	public function getFullName()
	{
		$firstName = trim($this->firstName);
		$lastName = trim($this->lastName);

		return $firstName.($firstName && $lastName ? ' ' : '').$lastName;
	}
}
