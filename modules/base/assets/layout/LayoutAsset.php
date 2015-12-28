<?php

namespace meican\base\assets\layout;

use yii\web\AssetBundle;

class LayoutAsset extends AssetBundle
{
    public $sourcePath = '@meican/base/assets/layout/public';

    public $css = [
        'layout.css',
        'theme.css'
    ];
    
    public $js = [
        'layout.js'
    ];
    
    public $depends = [
        'meican\base\assets\BootstrapAsset',
        'meican\base\assets\SlimScrollAsset',
        'meican\base\assets\FontAwesomeAsset',
        'meican\base\assets\IoniconsAsset',
        'meican\base\assets\IcheckAsset'
    ];
}
