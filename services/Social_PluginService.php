<?php

/**
 * Social plugin for Craft
 *
 * @package   Craft Social
 * @author    Benjamin David
 * @copyright Copyright (c) 2015, Dukt
 * @link      https://dukt.net/craft/social/
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class Social_PluginService extends BaseApplicationComponent
{
    // Properties
    // =========================================================================

    public $plugin;

    // Public Methods
    // =========================================================================

    /**
     * Init
     */
    public function init()
    {
        $this->plugin = craft()->plugins->getPlugin('social');

        parent::init();
    }

    /**
     * Check Requirements
     */
    public function checkRequirements()
    {
        $pluginDependencies = craft()->social_plugin->getPluginDependencies();

        if (count($pluginDependencies) > 0)
        {
            throw new \Exception("Social is not configured properly. Check Social settings for more informations.");
        }
    }

    /**
     * Get Plugin Dependencies
     */
    public function getPluginDependencies($missingOnly = true)
    {
        $dependencies = [];

        $plugins = $this->plugin->getRequiredPlugins();

        foreach ($plugins as $key => $plugin)
        {
            $dependency = $this->getPluginDependency($plugin);

            if ($missingOnly)
            {
                if ($dependency['isMissing'])
                {
                    $dependencies[] = $dependency;
                }
            }
            else
            {
                $dependencies[] = $dependency;
            }
        }

        return $dependencies;
    }

    // Private Methods
    // =========================================================================

    /**
     * Get Plugin Dependency
     */
    private function getPluginDependency($dependency)
    {
        $isMissing = true;
        $isInstalled = true;

        $plugin = craft()->plugins->getPlugin($dependency['handle'], false);

        if ($plugin)
        {
            $currentVersion = $plugin->version;


            // requires update ?

            if (version_compare($currentVersion, $dependency['version']) >= 0)
            {
                // no (requirements OK)

                if ($plugin->isInstalled && $plugin->isEnabled)
                {
                    $isMissing = false;
                }
            }
            else
            {
                // yes (requirement not OK)
            }
        }
        else
        {
            // not installed
        }

        $dependency['isMissing'] = $isMissing;
        $dependency['plugin'] = $plugin;

        return $dependency;
    }
}
