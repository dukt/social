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

class SocialPlugin extends BasePlugin
{
    /**
     * Get Name
     */
    function getName()
    {
        return Craft::t('Social Login');
    }

    /**
     * Get Version
     */
    function getVersion()
    {
        return '0.9.18';
    }

    /**
     * Get Developer
     */
    function getDeveloper()
    {
        return 'Dukt';
    }

    /**
     * Get Developer URL
     */
    function getDeveloperUrl()
    {
        return 'http://dukt.net/';
    }

    /**
     * Define Settings
     */
    protected function defineSettings()
    {
        return array(
            'allowSocialRegistration' => AttributeType::Bool,
            'defaultGroup' => AttributeType::Number,
            'allowFakeEmail' => AttributeType::Bool,
        );
    }

    /**
     * Get Settings HTML
     */
    public function getSettingsHtml()
    {
        if(craft()->request->getPath() == 'settings/plugins') {
            return true;
        }

        $variables = array(
            'settings' => $this->getSettings()
        );

        $oauthPlugin = craft()->plugins->getPlugin('OAuth');

        if($oauthPlugin) {
            if($oauthPlugin->isInstalled && $oauthPlugin->isEnabled) {

            }
        }

        return craft()->templates->render('social/settings', $variables);
    }

    /**
     * Has CP Section
     */
    public function hasCpSection()
    {
        return false;
    }

    /**
     * Hook Register CP Routes
     */
    public function hookRegisterCpRoutes()
    {
        return array(
            'social\/settings\/(?P<serviceProviderClass>.*)' => 'social/settings/_provider',
        );
    }
}