<?php

namespace floriskoch\craftpatlib\resources;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class PatlibBundle extends AssetBundle
{
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@floriskoch/craftpatlib/resources';

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'index.js',
        ];

//        $this->css = [
//            'styles.css',
//        ];

        parent::init();
    }
}
