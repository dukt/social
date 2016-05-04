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
        $remoteProfile = $this->getRemoteProfile($token);

        return [
            'id' => $remoteProfile->getId(),
            'email' => $remoteProfile->getEmail(),
            'firstName' => $remoteProfile->getFirstName(),
            'lastName' => $remoteProfile->getLastName(),
            'photoUrl' => $remoteProfile->getPictureUrl(),

            'name' => $remoteProfile->getName(),
            'hometown' => $remoteProfile->getHometown(),
            'bio' => $remoteProfile->getBio(),
            'isDefaultPicture' => $remoteProfile->isDefaultPicture(),
            'coverPhotoUrl' => $remoteProfile->getCoverPhotoUrl(),
            'gender' => $remoteProfile->getGender(),
            'locale' => $remoteProfile->getLocale(),
            'link' => $remoteProfile->getLink(),
        ];
    }
}
