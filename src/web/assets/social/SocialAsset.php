<?php
namespace dukt\social\web\assets\social;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class SocialAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@dukt/social/resources';

        $this->depends = [
            CpAsset::class,
        ];

        $this->css = [
            'css/social.css',
        ];

        parent::init();
    }
}