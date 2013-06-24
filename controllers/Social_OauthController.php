<?php

/**
 * Craft Directory by Dukt
 *
 * @package   Craft Directory
 * @author    Benjamin David
 * @copyright Copyright (c) 2013, Dukt
 * @license   http://docs.dukt.net/craft/directory/license
 * @link      http://dukt.net/craft/analytics/license
 */

namespace Craft;

class Social_OauthController extends BaseController
{
    // --------------------------------------------------------------------

    private $pluginHandle = 'social';

    // --------------------------------------------------------------------

    public function actionDownload()
    {
        if(craft()->{$this->pluginHandle.'_oauth'}->download()) {
            craft()->userSession->setNotice(Craft::t('OAuth plugin has been downloaded.'));
        } else {
            craft()->userSession->setError(Craft::t('OAuth plugin couldn’t be downloaded.'));
        }

        $redirect = UrlHelper::getActionUrl($this->pluginHandle.'/oauth/install');

        $this->redirect($redirect);
    }

    // --------------------------------------------------------------------

    public function actionInstall()
    {
        if(craft()->{$this->pluginHandle.'_oauth'}->install()) {
            craft()->userSession->setNotice(Craft::t('OAuth plugin installed.'));
        } else {
            craft()->userSession->setError(Craft::t('Couldn’t install OAuth plugin.'));
        }

        $this->redirect($this->pluginHandle.'/settings');
    }

    // --------------------------------------------------------------------

    public function actionEnable()
    {
        if(craft()->{$this->pluginHandle.'_oauth'}->enable()) {
            craft()->userSession->setNotice(Craft::t('OAuth plugin enabled.'));
        } else {
            craft()->userSession->setError(Craft::t('OAuth plugin couldn’t be plugin.'));
        }

        $this->redirect($this->pluginHandle.'/settings');
    }

    // --------------------------------------------------------------------
}