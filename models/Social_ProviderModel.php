<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2016, Dukt
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

    public function getAuthorizationOptions()
    {
        $authorizationOptions = craft()->config->get('authorizationOptions', 'social');

        if(isset($authorizationOptions[$this->oauthProviderHandle]))
        {
            return $authorizationOptions[$this->oauthProviderHandle];
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
        return craft()->oauth->getProvider($this->oauthProviderHandle, false);
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
