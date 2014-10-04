<?php

namespace Craft;

abstract class BaseSocialProvider {

    protected $token;

    public function setToken($token)
    {
        $this->token = $token;
    }

}