<?php

/**
 * Social Login for Craft
 *
 * @package   Social Login
 * @author    Benjamin David
 * @copyright Copyright (c) 2013, Dukt
 * @link      http://dukt.net/craft/social/
 * @license   http://dukt.net/craft/social/docs/license
 */

namespace Craft;

class Social_TokenController extends BaseController
{
	public function actionRemove()
	{
        Craft::log(__METHOD__, LogLevel::Info, true);

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