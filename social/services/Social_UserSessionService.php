<?php

/**
 * Social Login for Craft
 *
 * @package   Social Login
 * @author    Benjamin David
 * @copyright Copyright (c) 2014, Dukt
 * @link      https://dukt.net/craft/social/
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class Social_UserSessionService extends UserSessionService {

    private $_identity;
    public $allowAutoLogin = true;

    public function init()
    {
        $this->setStateKeyPrefix(md5('Yii.Craft\UserSessionService.'.craft()->getId()));

        parent::init();
    }

    public function login($socialUserId)
    {

        $rememberMe = true;

        Craft::log(__METHOD__, LogLevel::Info, true);

        $this->_identity = new TokenIdentity($socialUserId);
        $this->_identity->authenticate();

        // Was the login successful?
        if ($this->_identity->errorCode == TokenIdentity::ERROR_NONE)
        {
            // Get how long this session is supposed to last.

            $this->authTimeout = craft()->config->getUserSessionDuration($rememberMe);

            $id = $this->_identity->getId();

            $user = craft()->users->getUserById($id);

            $states = $this->_identity->getPersistentStates();



            // Run any before login logic.
            if ($this->beforeLogin($id, $states, false))
            {
                // Fire an 'onBeforeLogin' event
                $this->onBeforeLogin(new Event($this, array(
                    'username'      => $user->username,
                )));

                $this->changeIdentity($id, $this->_identity->getName(), $states);

                // Fire an 'onLogin' event
                $this->onLogin(new Event($this, array(
                    'username'      => $user->username,
                )));

                if ($this->authTimeout)
                {
                    if ($this->allowAutoLogin)
                    {
                        if ($user)
                        {
                            // Save the necessary info to the identity cookie.
                            $sessionToken = StringHelper::UUID();
                            $hashedToken = craft()->security->hashData(base64_encode(serialize($sessionToken)));
                            $uid = craft()->users->handleSuccessfulLogin($user, $hashedToken);

                            $data = array(
                                $this->getName(),
                                $sessionToken,
                                $uid,
                                ($rememberMe ? 1 : 0),
                                craft()->request->getUserAgent(),
                                $this->saveIdentityStates(),
                            );

                            $this->saveCookie('', $data, $this->authTimeout);
                        }
                        else
                        {
                            throw new Exception(Craft::t('Could not find a user with Id of {userId}.', array('{userId}' => $this->getId())));
                        }
                    }
                    else
                    {
                        throw new Exception(Craft::t('{class}.allowAutoLogin must be set true in order to use cookie-based authentication.', array('{class}' => get_class($this))));
                    }
                }

                // $this->_sessionRestoredFromCookie = false;
                // $this->_userRow = null;
                // Run any after login logic.
                $this->afterLogin(false);
            }

            return !$this->getIsGuest();
        }

        Craft::log('Tried to log in unsuccessfully.', LogLevel::Warning);
        return false;
    }
}