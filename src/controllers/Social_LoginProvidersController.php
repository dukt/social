<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class Social_LoginProvidersController extends BaseController
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

        craft()->social->requireDependencies();
	}

	/**
	 * Login Providers Index
	 *
	 * @return null
	 */
	public function actionIndex()
	{
		$variables['loginProviders'] = craft()->social_loginProviders->getLoginProviders(false);

		$this->renderTemplate('social/loginproviders/_index', $variables);
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
			$loginProvider = craft()->social_loginProviders->getLoginProvider($variables['handle'], false, true);

			if ($loginProvider)
			{
				$variables['infos'] = craft()->oauth->getProviderInfos($variables['handle']);;
				$variables['loginProvider'] = $loginProvider;

				$configInfos = craft()->config->get('providerInfos', 'oauth');

				if (!empty($configInfos[$variables['handle']]))
				{
					$variables['configInfos'] = $configInfos[$variables['handle']];
				}

				$this->renderTemplate('social/loginproviders/_edit', $variables);
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
		$loginProvider = craft()->request->getRequiredPost('loginProvider');

		if (craft()->social_loginProviders->enableLoginProvider($loginProvider))
		{
			craft()->userSession->setNotice(Craft::t('Login provider enabled.'));
		}
		else
		{
			craft()->userSession->setError(Craft::t('Couldn’t enable login provider.'));
		}

		$this->redirectToPostedUrl();
	}

	/**
	 * Disable Login Provider
	 *
	 * @return null
	 */
	public function actionDisableLoginProvider()
	{
		$this->requirePostRequest();
		$loginProvider = craft()->request->getRequiredPost('loginProvider');

		if (craft()->social_loginProviders->disableLoginProvider($loginProvider))
		{
			craft()->userSession->setNotice(Craft::t('Login provider disabled.'));
		}
		else
		{
			craft()->userSession->setError(Craft::t('Couldn’t disable login provider.'));
		}

		$this->redirectToPostedUrl();
	}
}
