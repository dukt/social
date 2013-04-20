<?php

namespace Craft;

require(CRAFT_PLUGINS_PATH.'connect/vendor/autoload.php');
require(CRAFT_PLUGINS_PATH."connect/etc/users/TokenIdentity.php");

class Connect_PublicController extends BaseController
{
    protected $allowAnonymous = true;


    public function actionLogin()
    {
        craft()->connect_userSession->loginToken();


        // $providerClass = craft()->request->getParam('provider');

        // // get the token

        // $token = craft()->httpSession->get('connectToken.'.$providerClass);
        // $token = base64_decode($token);
        // $token = unserialize($token);



        // // create an identity and login

        // //$identity = new TokenIdentity($token);

        // $identity = new TokenIdentity();

        // if ($identity->authenticate()) {
        //     craft()->user->allowAutoLogin = true;
        //     $ret = craft()->user->login($identity);


        //     var_dump(craft()->user);

        // } else {
        //     echo $identity->errorMessage;
        // }

        // $user = craft()->users->getUserById(1);

        // $seconds = 3600;
        // $sessionToken = StringHelper::UUID();
        // $hashedToken = craft()->security->hashString($sessionToken);
        // $uid = craft()->users->handleSuccessfulLogin($user, $hashedToken['hash']);
        // $userAgent = craft()->request->userAgent;

        // $data = array(
        //     craft()->user->getName(),
        //     $sessionToken,
        //     $uid,
        //     $seconds,
        //     $userAgent,
        //     craft()->user->saveIdentityStates(),
        // );

        // $this->saveCookie('', $data, $seconds);


        // die();

        $this->redirect('connect');
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

        craft()->httpSession->add('connectToken.'.$className, $token);

        // $service->token = $token;

        // $service->save();

        $finalRedirect = 'connect';

        $this->redirect($finalRedirect);
    }

}