<?php

/**
 * Social Login for Craft
 *
 * @package   Social Login
 * @author    Benjamin David
 * @copyright Copyright (c) 2014, Dukt
 * @link      https://dukt.net/craft/social/
 * @license   https://dukt.net/craft/social/docs/license
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
        return '0.9.23';
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
        return 'https://dukt.net/';
    }

    /**
     * Define Settings
     */
    protected function defineSettings()
    {
        return array(
            'allowSocialRegistration' => array(AttributeType::Bool, 'default' => true),
            'allowSocialLogin' => array(AttributeType::Bool, 'default' => true),
            'defaultGroup' => array(AttributeType::Number, 'default' => null),
            'requireEmailAddress' => array(AttributeType::Bool, 'default' => true),
        );
    }

    /**
     * Get Settings HTML
     */
    public function getSettingsHtml()
    {
        if(craft()->request->getPath() == 'settings/plugins')
        {
            return true;
        }

        $variables = array(
            'settings' => $this->getSettings()
        );

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
    public function registerCpRoutes()
    {
        return array(
            'social\/settings\/(?P<serviceProviderClass>.*)' => 'social/settings/_provider',
        );
    }

    /**
     * On Before Uninstall
     */
    public function onBeforeUninstall()
    {
        craft()->oauth->deleteTokensByPlugin('social');
    }
}