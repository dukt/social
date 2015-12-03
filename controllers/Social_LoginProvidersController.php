<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class Social_LoginProvidersController extends BaseController
{
    // Public Methods
    // =========================================================================

    /**
     * Settings
     *
     * @return null
     */
    public function actionIndex()
    {
        $variables['loginProviders'] = craft()->social_loginProviders->getLoginProviders(false);

        $this->renderTemplate('social/loginproviders/_index', $variables);
    }

    /**
     * Edit Provider
     *
     * @return null
     */
    public function actionEdit(array $variables = array())
    {
        if(!empty($variables['handle']))
        {
            $loginProvider = craft()->social_loginProviders->getLoginProvider($variables['handle'], false, true);

            if($loginProvider)
            {
                $variables['infos'] = craft()->oauth->getProviderInfos($variables['handle']);;
                $variables['loginProvider'] = $loginProvider;

                $configInfos = craft()->config->get('providerInfos', 'oauth');

                if(!empty($configInfos[$variables['handle']]))
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
}
