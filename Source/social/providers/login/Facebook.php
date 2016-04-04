<?php
/**
 * @link      https://dukt.net/craft/oauth/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/oauth/docs/license
 */

namespace Dukt\Social\LoginProviders;

use Craft\Craft;

class Facebook extends BaseProvider
{
    /**
     * Get the provider name.
     *
     * @return string
     */
    public function getName()
    {
        return 'Facebook';
    }

    /**
     * Get the provider handle.
     *
     * @return string
     */
    public function getOauthProviderHandle()
    {
        return 'facebook';
    }

    /**
     * @inheritDoc
     */
    public function getDefaultScope()
    {
        return [
            'email'
        ];
    }

    public function getProfile($token)
    {
        $remoteAccount = $this->getRemoteAccount($token);
        
        return [
            'id' => $remoteAccount->getId(),
            'email' => $remoteAccount->getEmail(),
            'firstName' => $remoteAccount->getFirstName(),
            'lastName' => $remoteAccount->getLastName(),
            'photoUrl' => $remoteAccount->getPictureUrl(),
        
            'name' => $remoteAccount->getName(),
            'hometown' => $remoteAccount->getHometown(),
            'bio' => $remoteAccount->getBio(),
            'isDefaultPicture' => $remoteAccount->isDefaultPicture(),
            'coverPhotoUrl' => $remoteAccount->getCoverPhotoUrl(),
            'gender' => $remoteAccount->getGender(),
            'locale' => $remoteAccount->getLocale(),
            'link' => $remoteAccount->getLink(),
        ];
    }
}
