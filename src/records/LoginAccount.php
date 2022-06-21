<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/social/blob/v2/LICENSE.md
 */

namespace dukt\social\records;

use craft\db\ActiveRecord;

/**
 * Class LoginAccount record.
 *
 * @property int $id              ID
 * @property int $userId          User ID
 * @property string $providerHandle  Provider handle
 * @property string $socialUid       Social UID
 *
 * @author  Dukt <support@dukt.net>
 * @since   1.0
 */
class LoginAccount extends ActiveRecord
{
    // Public Methods
    // =========================================================================

    /**
     * Returns the name of the associated database table.
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%social_login_accounts}}';
    }
}
