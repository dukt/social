<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class Social_LoginAccountRecord extends BaseRecord
{
    // Public Methods
    // =========================================================================

    /**
     * Get Table Name
     */
    public function getTableName()
    {
        return 'social_login_accounts';
    }

    /**
     * Define Attributes
     */
    public function defineAttributes()
    {
        return array(
            'providerHandle' => array(AttributeType::String, 'required' => true),
            'socialUid' => array(AttributeType::String, 'required' => true),
            'tokenId' => array(AttributeType::Number, 'required' => false),
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
            array('columns' => array('providerHandle', 'socialUid'), 'unique' => true)
        );
    }
}
