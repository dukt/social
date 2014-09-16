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

class Social_AskEmailModel extends BaseModel
{
    /**
     * Define Attributes
     */
    public function defineAttributes()
    {
        return array(
            'email'    => array(AttributeType::Email, 'required' => true),
        );
    }
}
