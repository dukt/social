<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\controllers;

use Craft;
use craft\web\Controller;
use dukt\social\web\assets\social\SocialAsset;

class LoginProvidersController extends Controller
{

	// Public Methods
	// =========================================================================

	/**
	 * Init
	 *
	 * @return null
	 */
	public function init()
	{
		parent::init();

        \dukt\social\Plugin::$plugin->social->requireDependencies();
	}

	/**
	 * Login Providers Index
	 *
	 * @return null
	 */
	public function actionIndex()
	{
        Craft::$app->getView()->registerAssetBundle(SocialAsset::class);

		$variables['loginProviders'] = \dukt\social\Plugin::$plugin->social_loginProviders->getLoginProviders(false);

		return $this->renderTemplate('social/loginproviders/_index', $variables);
	}

	/**
	 * Edit Login Provider
	 *
	 * @param array $variable Route variables
	 *
	 * @throws HttpException
	 * @return null
	 */
	public function actionEdit(array $variables = array())
	{
		if (!empty($variables['handle']))
		{
			$loginProvider = \dukt\social\Plugin::$plugin->social_loginProviders->getLoginProvider($variables['handle'], false, true);

			if ($loginProvider)
			{
				$variables['infos'] = \dukt\oauth\Plugin::getInstance()->oauth->getProviderInfos($variables['handle']);;
				$variables['loginProvider'] = $loginProvider;

				$configInfos = Craft::$app->config->get('providerInfos', 'oauth');

				if (!empty($configInfos[$variables['handle']]))
				{
					$variables['configInfos'] = $configInfos[$variables['handle']];
				}

				return $this->renderTemplate('social/loginproviders/_edit', $variables);
			}
			else
			{
				throw new HttpException(404);
			}
		}
		else
		{
			throw new HttpException(404);
		}
	}

	/**
	 * Enable Login Provider
	 *
	 * @return null
	 */
	public function actionEnableLoginProvider()
	{
		$this->requirePostRequest();
		$loginProvider = Craft::$app->request->getRequiredBodyParam('loginProvider');

		if (\dukt\social\Plugin::$plugin->social_loginProviders->enableLoginProvider($loginProvider))
		{
			Craft::$app->getSession()->setNotice(Craft::t('app', 'Login provider enabled.'));
		}
		else
		{
			Craft::$app->getSession()->setError(Craft::t('app', 'Couldn’t enable login provider.'));
		}

		return $this->redirectToPostedUrl();
	}

	/**
	 * Disable Login Provider
	 *
	 * @return null
	 */
	public function actionDisableLoginProvider()
	{
		$this->requirePostRequest();
		$loginProvider = Craft::$app->request->getRequiredBodyParam('loginProvider');

		if (\dukt\social\Plugin::$plugin->social_loginProviders->disableLoginProvider($loginProvider))
		{
			Craft::$app->getSession()->setNotice(Craft::t('app', 'Login provider disabled.'));
		}
		else
		{
			Craft::$app->getSession()->setError(Craft::t('app', 'Couldn’t disable login provider.'));
		}

		return $this->redirectToPostedUrl();
	}
}
