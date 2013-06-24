<?php

/**
 * Craft Analytics
 *
 * @package     Craft Analytics
 * @version     Version 1.0
 * @author      Benjamin David
 * @copyright   Copyright (c) 2013 - DUKT
 * @link        http://dukt.net/add-ons/craft/analytics/
 *
 */

namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'social/vendor/autoload.php');

use VIPSoft\Unzip\Unzip;
use Symfony\Component\Filesystem\Filesystem;

class Social_OauthService extends BaseApplicationComponent
{
    // --------------------------------------------------------------------

    private $oauthPluginZip = 'http://cl.ly/2F1O0w1P1Q1F/download/oauth-craft-0.9.zip';

    // --------------------------------------------------------------------

    private $oauthPluginClass = 'Oauth';
    private $oauthPluginHandle = 'oauth';

    // --------------------------------------------------------------------

    public function download()
    {
        $r = array('success' => false);

        $filesystem = new Filesystem();
        $unzipper  = new Unzip();

        $pluginComponent = craft()->plugins->getPlugin($this->oauthPluginClass, false);


        // plugin path

        $pluginZipDir = CRAFT_PLUGINS_PATH."_".$this->oauthPluginHandle."/";
        $pluginZipPath = CRAFT_PLUGINS_PATH."_".$this->oauthPluginHandle.".zip";

        try {

            // download

            $current = file_get_contents($this->oauthPluginZip);

            file_put_contents($pluginZipPath, $current);


            // unzip

            $content = $unzipper->extract($pluginZipPath, $pluginZipDir);


            // make a backup here ?

            $filesystem->remove(CRAFT_PLUGINS_PATH.$this->oauthPluginHandle);
            $filesystem->rename($pluginZipDir.$content[0].'/', CRAFT_PLUGINS_PATH.$this->oauthPluginHandle);

        } catch (\Exception $e) {
            $r['msg'] = $e->getMessage();
            return $r;
        }

        try {
            // remove download files

            $filesystem->remove($pluginZipDir);
            $filesystem->remove($pluginZipPath);
        } catch(\Exception $e) {
            $r['msg'] = $e->getMessage();

            return $r;
        }

        $r['success'] = true;

        return $r;
    }

    // --------------------------------------------------------------------

    public function install()
    {
        $pluginComponent = craft()->plugins->getPlugin($this->oauthPluginClass, false);

        try {
            if(!$pluginComponent->isInstalled) {
                if (craft()->plugins->installPlugin($this->oauthPluginClass)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } catch(\Exception $e) {
            return false;
        }
    }

    // --------------------------------------------------------------------

    public function enable()
    {
        $pluginComponent = craft()->plugins->getPlugin($this->oauthPluginClass, false);

        try {
            if(!$pluginComponent->isEnabled) {
                if (craft()->plugins->enablePlugin($this->oauthPluginClass)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } catch(\Exception $e) {
            return false;
        }
    }

    // --------------------------------------------------------------------
}

