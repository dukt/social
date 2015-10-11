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

class Social_GatewaysService extends BaseApplicationComponent
{
    private $gateways;

    // Public Methods
    // =========================================================================

    /**
     * Get gateway
     */
	public function getGateway($handle, $configuredOnly = true)
	{
		foreach($this->getGateways() as $gateway)
		{
			if($handle == $gateway->getHandle())
			{
				return $gateway;
			}
		}
	}

    /**
     * Get gateways
     */
    public function getGateways()
    {
    	if(!$this->gateways)
    	{
	        // fetch all Social gateway types

	        $gatewayTypes = array();

	        foreach(craft()->plugins->call('getSocialGateways', [], true) as $pluginGatewayTypes)
	        {
	            $gatewayTypes = array_merge($gatewayTypes, $pluginGatewayTypes);
	        }


	        // Instantiate gateways

	        $gateways = [];

	        foreach($gatewayTypes as $gatewayType)
	        {
                $gateway = $this->_createGateway($gatewayType);
	            $gateways[$gateway->getHandle()] = $gateway;
	        }

	        ksort($gateways);

	        $this->gateways = $gateways;
    	}

    	return $this->gateways;
    }

	public function getGatewayScopes($handle)
	{
		$scopes = craft()->config->get($handle.'Scopes', 'social');

		if ($scopes)
		{
			return $scopes;
		}
		else
		{
			return [];
		}
	}

	public function getGatewayParams($handle)
	{
		$gateway = $this->getGateway($handle, false);

		if ($gateway)
		{
			return $gateway->getParams();
		}
		else
		{
			return [];
		}
	}

    // Private Methods
    // =========================================================================

    /**
     * Create gateway from gateway type
     */
    private function _createGateway($gatewayType)
    {
        $gateway = new $gatewayType;

        return $gateway;
    }
}
