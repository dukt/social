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

    /**
     * View Account Details
     *
     * @return null
     */
    public function actionView()
    {
        craft()->social->checkRequirements();

        $routeParams = craft()->urlManager->getRouteParams();

        $accountId = $routeParams['variables']['id'];

        $account = craft()->social_accounts->getAccountById($accountId);

        $variables = [
            'account' => $account
        ];

        $this->renderTemplate('social/accounts/_view', $variables);
    }
}
