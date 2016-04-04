<?php
/**
 * @link      https://dukt.net/craft/oauth/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/oauth/docs/license
 */

namespace Dukt\Social\LoginProviders;

use Craft\Craft;

class Twitter extends BaseProvider
{
    /**
     * Get the provider name.
     *
     * @return string
     */
    public function getName()
    {
        return 'Twitter';
    }

    /**
     * Get the provider handle.
     *
     * @return string
     */
    public function getOauthProviderHandle()
    {
        return 'twitter';
    }
    
    public function getProfile($token)
    {
        $remoteAccount = $this->getRemoteAccount($token);
        
        $photoUrl = $remoteAccount->imageUrl;
        $photoUrl = str_replace("_normal.", ".", $photoUrl);
        
        return [
            'id' => $remoteAccount->uid,
            'email' => $remoteAccount->email,
            'photoUrl' => $photoUrl,
            
            'nickname' => $remoteAccount->nickname,
            'name' => $remoteAccount->name,
            'location' => $remoteAccount->location,
            'description' => $remoteAccount->description,
        ];
    }
}
