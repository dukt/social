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

        $pluginClass = craft()->request->getParam('pluginClass');
        $pluginHandle = craft()->request->getParam('pluginHandle');


        // download plugin (includes download, unzip)

        $download = $this->pluginService->download($pluginClass, $pluginHandle);

        if($download['success'] == true) {

            // install plugin

            if($this->pluginService->install($pluginClass)) {

                Craft::log(__METHOD__.' : '.$pluginClass.' plugin installed.', LogLevel::Info, true);

                craft()->userSession->setNotice(Craft::t($pluginClass.' plugin installed.'));

            } else {

                // plugin couldn't be installed

                Craft::log(__METHOD__.' : '.$pluginClass.' plugin not installed.', LogLevel::Info, true);

                $this->redirect($_SERVER['HTTP_REFERER']);
            }

        } else {

            // download failure

            $msg = 'Couldn’t install '.$pluginClass.' plugin.';

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

        $pluginClass = craft()->request->getParam('pluginClass');

        $this->pluginService->enable($pluginClass);

        $this->redirect($_SERVER['HTTP_REFERER']);
    }

    // --------------------------------------------------------------------

    public function actionInstall()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        // pluginClass

        $pluginClass = craft()->request->getParam('pluginClass');


        // install plugin

        if($this->pluginService->install($pluginClass)) {

            // install success

            Craft::log(__METHOD__." : ".$pluginClass.' plugin installed.', LogLevel::Info, true);

            craft()->userSession->setNotice(Craft::t($pluginClass.' plugin installed.'));
        } else {

            // install failure

            Craft::log(__METHOD__." : Couldn't install ".$pluginClass." plugin.", LogLevel::Info, true);

            craft()->userSession->setError(Craft::t("Couldn't install ".$pluginClass." plugin."));
        }


        // redirect

        $this->redirect($_SERVER['HTTP_REFERER']);
    }

    // --------------------------------------------------------------------
}