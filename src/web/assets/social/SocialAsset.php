<?php
namespace dukt\social\web\assets\social;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class SocialAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = __DIR__.'/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->css = [
            'social.css',
        ];

        parent::init();
    }
}
