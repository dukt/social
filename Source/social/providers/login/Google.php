<?php
/**
 * @link      https://dukt.net/craft/oauth/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/oauth/docs/license
 */

namespace Dukt\Social\LoginProviders;

use Craft\Craft;

class Google extends BaseProvider
{
    /**
     * Get the provider name.
     *
     * @return string
     */
    public function getName()
    {
        return 'Google';
    }

    /**
     * Get the provider handle.
     *
     * @return string
     */
    public function getOauthProviderHandle()
    {
        return 'google';
    }

    /**
     * @inheritDoc
     */
    public function getDefaultScope()
    {
        return [
            'https://www.googleapis.com/auth/userinfo.profile',
            'https://www.googleapis.com/auth/userinfo.email'
        ];
    }
    
    public function getProfile($token)
    {
        $remoteAccount = $this->getRemoteAccount($token);
        
        $photoUrl = $remoteAccount->getAvatar();
        $photoUrl = substr($photoUrl, 0, strpos($photoUrl, "?"));
        
        return [
            'id' => $remoteAccount->getId(),
            'email' => $remoteAccount->getEmail(),
            'firstName' => $remoteAccount->getFirstName(),
            'lastName' => $remoteAccount->getLastName(),
            'photoUrl' => $photoUrl,
            
            'name' => $remoteAccount->getName(),
        ];
    }
}
