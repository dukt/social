<?php

namespace Dukt\Social\Provider;

abstract class AbstractProvider {

    protected $token;

    public function setToken($token)
    {
        $this->token = $token;
    }

}