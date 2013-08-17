<?php

namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'social/vendor/autoload.php');

use Guzzle\Http\Client;

class Social_PublicController extends BaseController
{
	public $allowAnonymous = true;

	public function actionLogout()
	{
		craft()->userSession->logout(false);

		$redirect = '';

		if(isset($_SERVER['HTTP_REFERER'])) {
			$redirect = $_SERVER['HTTP_REFERER'];
		}

		$this->redirect($redirect);
	}

	public function actionPublish()
	{
		$criteria = craft()->elements->getCriteria(ElementType::Entry);
		$criteria->id = 110;
		$criteria->status = '*';


		$entry = $criteria->first();

		craft()->social->publish($entry);
	}

	public function actionPublish2()
	{

		$providerLib = craft()->oauth->getProvider('Facebook');

		$clientId = $providerLib->clientId;
		$clientSecret = $providerLib->clientSecret;

		$provider = craft()->oauth->getProviderLibrary('Facebook', 'social.user', true);


		// get user infos

		$client = new Client('https://graph.facebook.com/');
		$request = $client->get('me?access_token='.$provider->token->access_token);
		$response = $client->send($request);
		$response = json_decode($response->getBody(), true);
		// var_dump($response);

		$userInfos = $response;


		// publish to user stream

		$client = new Client('https://graph.facebook.com/');
		$request = $client->post('me/feed', array(), array(
				'access_token' => $provider->token->access_token,
				'message' => 'test2'
			));
		$response = $client->send($request);
		$response = json_decode($response->getBody(), true);
		var_dump($response);


		
		// $request = $client->get('/user');
		// $request->setAuth('user', 'pass');
		// echo $request->getUrl();


		// $me = $provider->api('me');
		// $me['id'];
		// var_dump($me);

		// $client = new Client('https://api.github.com');

		// $request = $client->get('/user');
		// $request->setAuth('user', 'pass');
		// echo $request->getUrl();

		die();
	}
}
