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

class Social_ProviderModel extends BaseModel
{
    // Public Methods
    // =========================================================================

    /**
     * Define Attributes
     */
    public function defineAttributes()
    {
        return array(
            'oauthProviderHandle' => AttributeType::Number,
            'customScope' => AttributeType::Mixed,
        );
    }

    public function getOptions()
    {
        $oauthConfig = craft()->config->get('oauthConfig', 'social');

        if(isset($oauthConfig[$this->oauthProviderHandle]['options']))
        {
            return $oauthConfig[$this->oauthProviderHandle]['options'];
        }
    }

    public function getScope()
    {
        $scope = [];
        $defaultScope = $this->getDefaultScope();

        if(is_array($defaultScope))
        {
            $scope = array_merge($scope, $defaultScope);
        }

        if(is_array($this->customScope))
        {
            $scope = array_merge($scope, $this->customScope);
        }

        return $scope;
    }

    public function getDefaultScope()
    {
        return $this->getOauthProvider()->getDefaultScope();
    }

    public function getName()
    {
        return $this->getOauthProvider()->getName();
    }

    public function getHandle()
    {
        return $this->getOauthProvider()->getHandle();
    }

    public function getOauthProvider()
    {
        return craft()->oauth->getProvider($this->oauthProviderHandle);
    }

    public function getIconUrl()
    {
        return $this->getOauthProvider()->getIconUrl();
    }

    public function getScopeDocsUrl()
    {
        return $this->getOauthProvider()->getScopeDocsUrl();
    }
}
