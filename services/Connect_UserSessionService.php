<?php
namespace Craft;

// require_once(CRAFT_PLUGINS_PATH."connect/etc/users/TokenIdentity.php");

class Connect_UserSessionService extends UserSessionService {


    public $allowAutoLogin = true;


    public function login2()
    {

        // $this->class = 'Craft\UserSessionService';
        $this->allowAutoLogin  = true;
        $this->loginUrl        = 'login';
        $this->autoRenewCookie = true;

        $username = 'ben';
        $password = 'password';
        $rememberMe = false;



        return parent::login($username, $password, $rememberMe);

        // Validate the username/password first.
        $usernameModel = new UsernameModel();
        $passwordModel = new PasswordModel();

        $usernameModel->username = $username;
        $passwordModel->password = $password;

        // Require a userAgent string and an IP address to help prevent direct socket connections from trying to login.
        if (!craft()->request->userAgent || !craft()->request->getIpAddress())
        {
            Craft::log('Someone tried to login with loginName: '.$username.', without presenting an IP address or userAgent string.', LogLevel::Warning);
            $this->logout();
            $this->requireLogin();
        }

        // Validate the model.
        if ($usernameModel->validate() && $passwordModel->validate())
        {
            // Authenticate the credentials.
            $this->_identity = new UserIdentity($username, $password);
            $this->_identity->authenticate();

            // Was the login successful?
            if ($this->_identity->errorCode == UserIdentity::ERROR_NONE)
            {
                // See if the 'rememberUsernameDuration' config item is set. If so, save the name to a cookie.
                $rememberUsernameDuration = craft()->config->get('rememberUsernameDuration');
                if ($rememberUsernameDuration)
                {
                    $interval = new DateInterval($rememberUsernameDuration);
                    $expire = new DateTime();
                    $expire->add($interval);

                    // Save the username cookie.
                    $this->saveCookie('username', $username, $expire->getTimestamp());
                }

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

                $r = !$this->getIsGuest();

                return $r;
            }
        }

        Craft::log($username.' tried to log in unsuccessfully.', LogLevel::Warning);
        return false;
    }

    public function quelconque() {
        $identity = new TokenIdentity;

        if($identity->authenticate()) {


            // See if the 'rememberUsernameDuration' config item is set. If so, save the name to a cookie.
            // $rememberUsernameDuration = craft()->config->get('rememberUsernameDuration');

            $seconds =  1000;
            // Get how long this session is supposed to last.
            $seconds = $this->_getSessionDuration($seconds);
            //$this->authTimeout = $seconds;

            $id = $identity->getId();
            $states = $identity->getPersistentStates();

            $user = craft()->users->getUserByUsernameOrEmail('ben');

            var_dump($user instanceof CWebUser);
            die();

            $sessionToken = StringHelper::UUID();
            $hashedToken = craft()->security->hashString($sessionToken);
            $uid = craft()->users->handleSuccessfulLogin($user, $hashedToken['hash']);
            $userAgent = craft()->request->userAgent;



            $data = array(
                $user->getName(),
                $sessionToken,
                $uid,
                $seconds,
                $userAgent,
                //$this->saveIdentityStates(),
                array()
            );

            $this->saveCookie('', $data, $seconds);

            // // Run any before login logic.
            // // if ($this->beforeLogin($id, $states, false))
            // if (1==1)
            // {
            //     $this->changeIdentity($id, $identity->getName(), $states);

            //     if ($seconds > 0)
            //     {
            //         if ($this->allowAutoLogin)
            //         {
            //             $user = craft()->users->getUserById($id);

            //             if ($user)
            //             {
            //                 // Save the necessary info to the identity cookie.
            //                 $sessionToken = StringHelper::UUID();
            //                 $hashedToken = craft()->security->hashString($sessionToken);
            //                 $uid = craft()->users->handleSuccessfulLogin($user, $hashedToken['hash']);
            //                 $userAgent = craft()->request->userAgent;

            //                 $data = array(
            //                     $this->getName(),
            //                     $sessionToken,
            //                     $uid,
            //                     $seconds,
            //                     $userAgent,
            //                     $this->saveIdentityStates(),
            //                 );

            //                 $this->saveCookie('', $data, $seconds);
            //             }
            //             else
            //             {
            //                 throw new Exception(Craft::t('Could not find a user with Id of {userId}.', array('{userId}' => $this->getId())));
            //             }
            //         }
            //         else
            //         {
            //             throw new Exception(Craft::t('{class}.allowAutoLogin must be set true in order to use cookie-based authentication.', array('{class}' => get_class($this))));
            //         }
            //     }

            //     $this->_sessionRestoredFromCookie = false;
            //     $this->_userRow = null;

            //     // Run any after login logic.
            //     $this->afterLogin(false);
            // }

            // return !$this->getIsGuest();







            //echo "rock you";
        }

        //var_dump($identity);
        die();
    }








}