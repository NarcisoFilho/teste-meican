<?php
namespace meican\modules\bpm\assets;

use yii\web\AssetBundle;

class IndexAsset extends AssetBundle
{
	public $basePath = '@webroot';
	public $baseUrl = '@web';
	public $js = [
			'js/bpm/workflow/index.js',
			'js/bpm/workflow/bpm-i18n.js',
	];
	public $depends = [
			'yii\web\JqueryAsset',
	];
	public $jsOptions = ['position' => \yii\web\View::POS_END];
}