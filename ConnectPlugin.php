<?php


namespace Craft;

class ConnectPlugin extends BasePlugin
{
    /**
     * Get Name
     */
    function getName()
    {
        return Craft::t('Connect');
    }
    
    // --------------------------------------------------------------------

    /**
     * Get Version
     */
    function getVersion()
    {
        return '1.0';
    }
    
    // --------------------------------------------------------------------

    /**
     * Get Developer
     */
    function getDeveloper()
    {
        return 'Dukt';
    }
    
    // --------------------------------------------------------------------

    /**
     * Get Developer URL
     */
    function getDeveloperUrl()
    {
        return 'http://dukt.net/';
    }
    
    // --------------------------------------------------------------------

    /**
     * Has CP Section
     */
    public function hasCpSection()
    {
        return true;
    }
    
    // --------------------------------------------------------------------

    /**
     * Hook Register CP Routes
     */
    public function hookRegisterCpRoutes()
    {
        return array(
            'connect\/settings\/(?P<serviceProviderClass>.*)' => 'connect/_settings',
        );
    }
}