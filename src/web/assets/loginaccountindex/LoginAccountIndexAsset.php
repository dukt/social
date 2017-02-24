<?php
namespace dukt\social\web\assets\loginaccountindex;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class LoginAccountIndexAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = __DIR__.'/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'LoginAccountIndex.js',
        ];

        parent::init();
    }
}