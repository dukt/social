<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/social/blob/v2/LICENSE.md
 */

namespace dukt\social\web\assets\login;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Asset bundle for CP social login
 */
class LoginAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = __DIR__ . '/dist';

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
