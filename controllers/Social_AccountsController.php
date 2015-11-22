<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class Social_AccountsController extends BaseController
{
    // Public Methods
    // =========================================================================

    /**
     * List Accounts
     *
     * @return null
     */
    public function actionIndex()
    {
        $accounts = craft()->social_accounts->getAccounts();

        $this->renderTemplate('social/accounts', [
            'accounts' => $accounts
        ]);
    }
}
