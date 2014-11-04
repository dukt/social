<?php

namespace Dukt\Social\Provider;

abstract class AbstractProvider {

    protected $token;

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
}