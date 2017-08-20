<?php
namespace dukt\social\web\assets\login;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class LoginAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = __DIR__.'/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->css = [
            'login.css',
        ];

        $this->js = [
            'login.js',
        ];

        parent::init();
    }
}