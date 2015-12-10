<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class Social_LoginAccountsController extends BaseController
{
    // Public Methods
    // =========================================================================

    /**
     * List Login Accounts
     *
     * @return null
     */
    public function actionIndex()
    {
        $loginAccounts = craft()->social_loginAccounts->getLoginAccounts();

        $this->renderTemplate('social/loginaccounts', [
            'loginAccounts' => $loginAccounts
        ]);
    }
}
