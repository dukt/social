<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

trait SocialTrait
{
    // Public Methods
    // =========================================================================

    /**
     * Check requirements
     *
     * @throws Exception
     */
    public function checkPluginRequirements()
    {
        if($this->areDependenciesMissing())
        {
            throw new Exception("Social is not configured properly. Check Social settings for more informations.");
        }
    }

    /**
     * Checks dependencies and redirects to install if one or more are missing
     *
     * @return bool
     */
    public function requireDependencies()
    {
        if ($this->areDependenciesMissing())
        {
            $url = UrlHelper::getUrl('social/install');
            craft()->request->redirect($url);
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * Get Missing Dependencies
     *
     * @return array
     */
    public function getMissingDependencies()
    {
        return $this->getDependencies(true);
    }

    // Private Methods
    // =========================================================================

    /**
     * Returns `true` if dependencies are missing, and `false` otherwise
     *
     * @return bool
     */
    private function areDependenciesMissing()
    {
        $missingDependencies = $this->getMissingDependencies();

        if(count($missingDependencies) > 0)
        {
            return true;
        }

        return false;
    }

    /**
     * Get dependencies
     *
     * @return array
     */
    private function getDependencies($missingOnly = false)
    {
        $dependencies = array();

        $plugin = craft()->plugins->getPlugin('social');
        $plugins = $plugin->getRequiredPlugins();

        foreach($plugins as $key => $plugin)
        {
            $dependency = $this->getPluginDependency($plugin);

            if($missingOnly)
            {
                if($dependency['isMissing'])
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

    /**
     * Get dependency
     *
     * @return array
     */
    private function getPluginDependency($dependency)
    {
        $isMissing = true;
        $isInstalled = true;

        $plugin = craft()->plugins->getPlugin($dependency['handle'], false);

        if($plugin)
        {
            $currentVersion = $plugin->version;


            // requires update ?

            if(version_compare($currentVersion, $dependency['version']) >= 0)
            {
                if($plugin->isInstalled && $plugin->isEnabled)
                {
                    $isMissing = false;
                }
            }
        }

        $dependency['isMissing'] = $isMissing;
        $dependency['plugin'] = $plugin;
        $dependency['pluginLink'] = 'https://dukt.net/craft/'.$dependency['handle'];

        return $dependency;
    }
}