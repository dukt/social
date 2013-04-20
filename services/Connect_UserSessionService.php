<?php
namespace Craft;

require_once(CRAFT_PLUGINS_PATH."connect/etc/users/TokenIdentity.php");

class Connect_UserSessionService extends UserSessionService {
    private $_identity;
    public $allowAutoLogin = true;
    private $_sessionRestoredFromCookie;
    private $_userRow;

    function test()
    {
        echo "rock you";
        die();
    }

    function loginToken()
    {
        // Authenticate the credentials.
        $this->_identity = new TokenIdentity();
        $this->_identity->authenticate();

        // Was the login successful?
        if ($this->_identity->errorCode == TokenIdentity::ERROR_NONE)
        {
            // See if the 'rememberUsernameDuration' config item is set. If so, save the name to a cookie.
            // $rememberUsernameDuration = craft()->config->get('rememberUsernameDuration');
            // if ($rememberUsernameDuration)
            // {
            //     $interval = new DateInterval($rememberUsernameDuration);
            //     $expire = new DateTime();
            //     $expire->add($interval);

            //     // Save the username cookie.
            //     $this->saveCookie('username', $username, $expire->getTimestamp());
            // }

            $rememberMe = false;

            // Get how long this session is supposed to last.
            $seconds = $this->_getSessionDuration($rememberMe);
            $this->authTimeout = $seconds;

            $id = $this->_identity->getId();
            $states = $this->_identity->getPersistentStates();

            // Run any before login logic.
            if ($this->beforeLogin($id, $states, false))
            {

                $this->changeIdentity($id, $this->_identity->getName(), $states);

                if ($seconds > 0)
                {
                    if ($this->allowAutoLogin)
                    {
                        $user = craft()->users->getUserById($id);

                        if ($user)
                        {

                            // Save the necessary info to the identity cookie.
                            $sessionToken = StringHelper::UUID();
                            $hashedToken = craft()->security->hashString($sessionToken);
                            $uid = craft()->users->handleSuccessfulLogin($user, $hashedToken['hash']);
                            $userAgent = craft()->request->userAgent;

                            $data = array(
                                $this->getName(),
                                $sessionToken,
                                $uid,
                                $seconds,
                                $userAgent,
                                $this->saveIdentityStates(),
                            );

                            $this->saveCookie('', $data, $seconds);
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

                $this->_sessionRestoredFromCookie = false;
                $this->_userRow = null;

                // Run any after login logic.
                $this->afterLogin(false);
            }

            return !$this->getIsGuest();
        }
    }

    private function _getUserRow($id)
    {
        if (!isset($this->_userRow))
        {
            if ($id)
            {
                $userRow = craft()->db->createCommand()
                    ->select('*')
                    ->from('{{users}}')
                    ->where('id=:id', array(':id' => $id))
                    ->queryRow();

                if ($userRow)
                {
                    $this->_userRow = $userRow;
                }
                else
                {
                    $this->_userRow = false;
                }
            }
            else
            {
                $this->_userRow = false;
            }
        }

        return $this->_userRow;
    }


    /**
     * @param $rememberMe
     * @return int
     */
    private function _getSessionDuration($rememberMe)
    {
        if ($rememberMe)
        {
            $duration = craft()->config->get('rememberedUserSessionDuration');
        }
        else
        {
            $duration = craft()->config->get('userSessionDuration');
        }

        // Calculate how long the session should last.
        if ($duration)
        {
            $interval = new DateInterval($duration);
            $expire = DateTimeHelper::currentUTCDateTime();
            $currentTimeStamp = $expire->getTimestamp();
            $futureTimeStamp = $expire->add($interval)->getTimestamp();
            $seconds = $futureTimeStamp - $currentTimeStamp;
        }
        else
        {
            $seconds = 0;
        }

        return $seconds;
    }
}