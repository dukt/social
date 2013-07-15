<?php

namespace Craft;

class Social_TokenController extends BaseController
{
	public function actionRemove()
	{
		$tokenId = craft()->request->getParam('tokenId');

		$token = Oauth_TokenRecord::model()->findByPk($tokenId);

		if($token) {
			$token->delete();
		}

		$redirect = '';

		if(isset($_SERVER['HTTP_REFERER'])) {
			$redirect = $_SERVER['HTTP_REFERER'];
		}

		$this->redirect($redirect);
	}
}