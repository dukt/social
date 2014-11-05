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

class Social_AccountRecord extends BaseRecord
{
    /**
     * Get Table Name
     */
    public function getTableName()
    {
        return 'social_accounts';
    }

    /**
     * Define Attributes
     */
    public function defineAttributes()
    {
        return array(
            'hasEmail' => array(AttributeType::Bool, 'required' => true),
            'hasPassword' => array(AttributeType::Bool, 'required' => true),
            'temporaryEmail' => array(AttributeType::String, 'required' => false),
            'temporaryPassword' => array(AttributeType::String, 'required' => true),
        );
    }

    public function defineRelations()
    {
        return array(
            'user' => array(static::BELONGS_TO, 'UserRecord', 'onDelete' => static::CASCADE, 'required' => true),
        );
    }

    /**
     * @return array
     */
    public function defineIndexes()
    {
        return array(
            array('columns' => array('userId'), 'unique' => true)
        );
    }
}
