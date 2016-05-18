<?php
/**
 * @link      https://dukt.net/craft/oauth/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/oauth/docs/license
 */

namespace Dukt\Social\LoginProviders;

use Craft\Craft;
use Guzzle\Http\Client;
use Craft\Oauth_TokenModel;

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
            'email',
            'user_location',
        ];
    }

    public function getRemoteProfile($token)
    {
        $oauthProvider = $this->getOauthProvider();

        $client = new Client('https://graph.facebook.com/v2.6');
        $client->addSubscriber($oauthProvider->getSubscriber($token));

        $fields = implode(',', [
            'id', 'name', 'first_name', 'last_name',
            'email', 'hometown', 'bio', 'picture.type(large){url,is_silhouette}',
            'cover{source}', 'gender', 'locale', 'link',
            'location',
        ]);

        $request = $client->get('/me?fields='.$fields);

        $response = $request->send();
        $json = $response->json();

        return $json;
    }

    public function getProfile(Oauth_TokenModel $token)
    {
        $remoteProfile = $this->getRemoteProfile($token);

        return [
            'id' => (isset($remoteProfile['id']) ? $remoteProfile['id'] : null ),
            'email' => (isset($remoteProfile['email']) ? $remoteProfile['email'] : null ),
            'photoUrl' => (isset($remoteProfile['picture']['data']['url']) ? $remoteProfile['picture']['data']['url'] : null ),

            'remoteProfile' => $remoteProfile,
        ];
    }
}
