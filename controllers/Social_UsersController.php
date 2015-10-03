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

class Social_UsersController extends BaseController
{
    // Public Methods
    // =========================================================================

    /**
     * List Users
     *
     * @return null
     */
    public function actionIndex()
    {
        $socialUsers = craft()->social_users->getUsers();

        $this->renderTemplate('social/users', [
            'socialUsers' => $socialUsers
        ]);
    }

    /**
     * Change Photo
     *
     * @return null
     */
    public function actionChangePhoto()
    {
        $userId = craft()->request->getParam('userId');
        $photoUrl = craft()->request->getParam('photoUrl');

        $user = craft()->users->getUserById($userId);

        craft()->social_users->saveRemotePhoto($photoUrl, $user);

        $this->redirect($_SERVER['HTTP_REFERER']);
    }

    /**
     * User Profile
     *
     * @return null
     */
    public function actionUserProfile()
    {
        craft()->social->checkRequirements();

        // order

        $routeParams = craft()->urlManager->getRouteParams();

        $socialUserId = $routeParams['variables']['id'];

        $socialUser = craft()->social_users->getSocialUserById($socialUserId);

        $variables = [
            'socialUser' => $socialUser
        ];

        $this->renderTemplate('social/users/_profile', $variables);
    }
}
