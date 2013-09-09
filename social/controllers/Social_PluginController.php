<?php

namespace Craft;

class Social_PluginController extends BaseController
{
    // --------------------------------------------------------------------

    private $pluginHandle = 'social';
    private $pluginService;

    // --------------------------------------------------------------------

    public function __construct()
    {
        $this->pluginService = craft()->{$this->pluginHandle.'_plugin'};
    }

    // --------------------------------------------------------------------

    public function actionDownload()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $pluginHandle = craft()->request->getParam('plugin');


        // download plugin (includes download, unzip)

        $download = $this->pluginService->download($pluginHandle);

        if($download['success'] == true) {

            $this->redirect(
                UrlHelper::getActionUrl(
                    $this->pluginHandle.'/plugin/install',
                    array('plugin' => $pluginHandle, 'redirect' => $_SERVER['HTTP_REFERER'])
                )
            );

        } else {

            // download failure

            $msg = 'Couldn’t install plugin.';

            if(isset($download['msg'])) {
                $msg = $download['msg'];
            }

            Craft::log(__METHOD__.' : '.$msg, LogLevel::Info, true);

            craft()->userSession->setError(Craft::t($msg));
        }


        // redirect

        $this->redirect($_SERVER['HTTP_REFERER']);
    }

    // --------------------------------------------------------------------

    public function actionEnable()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $pluginHandle = craft()->request->getParam('plugin');

        $this->pluginService->enable($pluginHandle);

        $this->redirect($_SERVER['HTTP_REFERER']);
    }

    // --------------------------------------------------------------------

    public function actionInstall()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        // pluginHandle

        $pluginHandle = craft()->request->getParam('plugin');
        $redirect = craft()->request->getParam('redirect');

        if (!$redirect) {
            $redirect = $_SERVER['HTTP_REFERER'];
        }


        // install plugin

        if($this->pluginService->install($pluginHandle)) {

            // install success

            Craft::log(__METHOD__." : ".$pluginHandle.' plugin installed.', LogLevel::Info, true);

            craft()->userSession->setNotice(Craft::t('Plugin installed.'));
        } else {

            // install failure

            Craft::log(__METHOD__." : Couldn't install ".$pluginHandle." plugin.", LogLevel::Info, true);

            craft()->userSession->setError(Craft::t("Couldn't install plugin."));
        }


        // redirect

        $this->redirect($_SERVER['HTTP_REFERER']);
    }

    // --------------------------------------------------------------------
}