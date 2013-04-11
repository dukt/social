<?php
namespace Craft;

class Connect_UserSessionService extends UserSessionService {
    function login($email)
    {
        

        $userObj = craft()->users->getUserByUsernameOrEmail($email);

        // Get how long this session is supposed to last.
        $seconds = 3600;
        $this->authTimeout = $seconds;

        $id = $userObj->id;


        $user = craft()->users->getUserById($id);

        if ($user)
        {
            // Save the necessary info to the identity cookie.
            $sessionToken = StringHelper::UUID();
            $hashedToken = craft()->security->hashString($sessionToken);
            $uid = craft()->users->handleSuccessfulLogin($user, $hashedToken['hash']);
            $userAgent = craft()->request->userAgent;

            $data = array(
                $userObj->getName(),
                $sessionToken,
                $uid,
                $seconds,
                $userAgent,
                //$this->saveIdentityStates(),
            );

            $this->saveCookie('', $data, $seconds);
        }
        else
        {
            throw new Exception(Craft::t('Could not find a user with Id of {userId}.', array('{userId}' => $this->getId())));
        }


        return $email;
    }
}