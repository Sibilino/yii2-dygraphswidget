<?php
namespace sibilino\y2dygraphs;

use yii\web\AssetBundle;

class DygraphsAsset extends AssetBundle 
{
	public $sourcePath = '@vendor/sibilino/yii2-dygraphswidget';
	public $js = [
		'js/dygraph-combined.js',
	];
}