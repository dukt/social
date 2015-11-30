<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

use Dukt\Social\Etc\Users\TokenIdentity;

class Social_UserSessionService extends UserSessionService
{
    // Properties
    // =========================================================================

    public $allowAutoLogin = true;
    private $_identity;

    // Public Methods
    // =========================================================================

    public function init()
    {
        $this->setStateKeyPrefix(md5('Yii.Craft\UserSessionService.'.craft()->getId()));

        parent::init();
    }

    public function login($accountId)
    {
        $rememberMe = true;

        $this->_identity = new TokenIdentity($accountId);
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
                $this->changeIdentity($id, $this->_identity->getName(), $states);

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

                            $data = [
                                $this->getName(),
                                $sessionToken,
                                $uid,
                                ($rememberMe ? 1 : 0),
                                craft()->request->getUserAgent(),
                                $this->saveIdentityStates(),
                            ];

                            $this->saveCookie('', $data, $this->authTimeout);
                        }
                        else
                        {
                            throw new Exception(Craft::t('Could not find a user with Id of {userId}.', ['{userId}' => $this->getId()]));
                        }
                    }
                    else
                    {
                        throw new Exception(Craft::t('{class}.allowAutoLogin must be set true in order to use cookie-based authentication.', ['{class}' => get_class($this)]));
                    }
                }

                // Run any after login logic.
                $this->afterLogin(false);
            }

            return !$this->getIsGuest();
        }

        SocialPlugin::log('Tried to log in unsuccessfully.', LogLevel::Warning);

        return false;
    }
}