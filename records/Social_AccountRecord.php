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

class Social_AccountRecord extends BaseRecord
{
    // Public Methods
    // =========================================================================

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

    /**
     * Define Relations
     */
    public function defineRelations()
    {
        return array(
            'user' => array(static::BELONGS_TO, 'UserRecord', 'onDelete' => static::CASCADE, 'required' => true),
        );
    }

    /**
     * Define Indexes
     *
     * @return array
     */
    public function defineIndexes()
    {
        return array(
            array('columns' => array('userId'), 'unique' => true)
        );
    }
}
