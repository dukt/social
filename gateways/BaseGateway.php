<?php

namespace Dukt\Social\Gateway;

abstract class BaseGateway
{
    // Properties
    // =========================================================================

    protected $token;

    // Public Methods
    // =========================================================================

    public function getHandle()
    {
        // from : \Dukt\Share\Service\Twitter
        // to : twitter

        $handle = get_class($this);

        $start = strrpos($handle, "\\") + 1;

        $handle = substr($handle, $start);
        $handle = strtolower($handle);

        return $handle;
    }

    public function isConfigured()
    {
        $oauthProvider = \Craft\craft()->oauth->getProvider($this->getHandle());

        if($oauthProvider)
        {
            return $oauthProvider->isConfigured();
        }
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getScopes()
    {
        return array();
    }

    public function getParams()
    {
        return array();
    }

    public function onBeforeSaveToken()
    {
    }
}