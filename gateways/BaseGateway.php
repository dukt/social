<?php

namespace Dukt\Social\Gateway;

use Craft\Oauth_TokenModel;

abstract class BaseGateway
{
    // Properties
    // =========================================================================

    protected $token;

    // Public Methods
    // =========================================================================

	/**
     * Get color
     *
     * @return string
     */
    public function getColor()
    {
        return '#ddd';
    }

	/**
     * Get gateway handle
     *
     * @return string
     */
    public function getHandle()
    {
        // from : \Dukt\Social\Gateway\Twitter
        // to : twitter

        $handle = get_class($this);

        $start = strrpos($handle, "\\") + 1;

        $handle = substr($handle, $start);
        $handle = strtolower($handle);

        return $handle;
    }

    /**
     * Is gateway configured ?
     *
     * @return bool
     */
    public function isConfigured()
    {
        $oauthProvider = \Craft\craft()->oauth->getProvider($this->getHandle());

        if($oauthProvider)
        {
            return $oauthProvider->isConfigured();
        }
    }

	/**
     * Set OAuth token
     *
     * @param Oauth_TokenModel $token
     */
    public function setToken(Oauth_TokenModel $token)
    {
        $this->token = $token;
    }

	/**
     * Get OAuth scopes
     *
     * @return array
     */
    public function getScopes()
    {
        return array();
    }

	/**
     * Get OAuth params
     *
     * @return array
     */
    public function getParams()
    {
        return array();
    }

	/**
     * On before save token
     */
    public function onBeforeSaveToken()
    {
    }
}