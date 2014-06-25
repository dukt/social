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

class Social_UserRecord extends BaseRecord
{
    /**
     * Get Table Name
     */
    public function getTableName()
    {
        return 'social_users';
    }

    /**
     * Define Attributes
     */
    public function defineAttributes()
    {
        return array(
            'provider' => array(AttributeType::String, 'required' => true),
            'socialUid' => array(AttributeType::String, 'required' => true),
        );
    }

    public function defineRelations()
    {
        return array(
            'user' => array(static::BELONGS_TO, 'UserRecord', 'onDelete' => static::CASCADE, 'required' => true),
            'token' => array(static::BELONGS_TO, 'Oauth_TokenRecord', 'onDelete' => static::CASCADE, 'required' => false),
        );
    }

    /**
     * @return array
     */
    public function defineIndexes()
    {
        return array(
            array('columns' => array('provider', 'socialUid'), 'unique' => true)
        );
    }
}
