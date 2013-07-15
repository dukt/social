<?php

namespace Craft;

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
}