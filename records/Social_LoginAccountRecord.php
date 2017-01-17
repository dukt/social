<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class Social_LoginAccountRecord extends BaseRecord
{
	// Public Methods
	// =========================================================================

	/**
	 * Returns the name of the associated database table.
	 *
	 * @return string
	 */
	public function getTableName()
	{
		return 'social_login_accounts';
	}

	/**
	 * Defines this model's attributes.
	 *
	 * @return array
	 */
	public function defineAttributes()
	{
		return array(
			'providerHandle' => array(AttributeType::String, 'required' => true),
			'socialUid' => array(AttributeType::String, 'required' => true),
		);
	}

	/**
	 * Defines this model's relations to other models.
	 *
	 * @return array
	 */
	public function defineRelations()
	{
		return array(
			'element' => array(static::BELONGS_TO, 'ElementRecord', 'id', 'onDelete' => static::CASCADE, 'required' => true),
			'user' => array(static::BELONGS_TO, 'UserRecord', 'onDelete' => static::CASCADE, 'required' => true),
		);
	}

	/**
	 * Defines this model's database table indexes.
	 *
	 * @return array
	 */
	public function defineIndexes()
	{
		return array(
			array('columns' => array('providerHandle', 'socialUid'), 'unique' => true)
		);
	}
}
