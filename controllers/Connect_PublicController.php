<?php

namespace Craft;

require(CRAFT_PLUGINS_PATH.'connect/vendor/autoload.php');

class Connect_PublicController extends BaseController
{
    protected $allowAnonymous = true;

    public function actionNow()
    {
        die('now');
    }

    public function actionLogin()
    {
        $token = craft()->httpSession->get('connectToken');

        $providerClass = craft()->request->getParam('provider');

        $user = false; // would be set with a user object

        // Save the necessary info to the identity cookie.
        
        $seconds = 3600;
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

    public function actionAuthenticate()
    {
        $className = craft()->request->getParam('provider');

        $service = Connect_ServiceRecord::model()->find('providerClass=:providerClass', array(':providerClass' => $className));


        $className = $service->providerClass;

        $provider = \OAuth\OAuth::provider($className, array(
            'id' => $service->clientId,
            'secret' => $service->clientSecret,
            'redirect_url' => \Craft\UrlHelper::getActionUrl('connect/public/authenticate/', array('provider' => $className))
        ));

        $provider = $provider->process(function($url, $token = null) {

            if ($token) {
                $_SESSION['token'] = base64_encode(serialize($token));
            }

            header("Location: {$url}");

            exit;

        }, function() {
            return unserialize(base64_decode($_SESSION['token']));
        });


        $token = $provider->token();

        $token = base64_encode(serialize($token));

        craft()->httpSession->add('connectToken', $token);
        
        $service->token = $token;

        $service->save();

        $finalRedirect = 'connect';

        $this->redirect($finalRedirect);
    }

}