<?php

namespace Craft;

class Social_PluginController extends BaseController
{
    // --------------------------------------------------------------------

    private $pluginService;
    private $referer;

    // --------------------------------------------------------------------

    public function __construct()
    {
        $this->pluginService = craft()->social_plugin;
    }

    // --------------------------------------------------------------------

    public function actionDownload()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $pluginHandle = craft()->request->getParam('plugin');


        // download plugin (includes download, unzip)

        $download = $this->pluginService->download($pluginHandle, $pluginHandle);

        if($download['success'] == true) {

            // install plugin

            if($this->pluginService->install($pluginHandle)) {

                Craft::log(__METHOD__.' : '.$pluginHandle.' plugin installed.', LogLevel::Info, true);

                craft()->userSession->setNotice(Craft::t('Plugin installed.'));

            } else {

                // plugin couldn't be installed

                Craft::log(__METHOD__.' : '.$pluginHandle.' plugin not installed.', LogLevel::Info, true);

                $this->redirect($_SERVER['HTTP_REFERER']);
            }

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