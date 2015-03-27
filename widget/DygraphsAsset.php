<?php
namespace sibilino\y2dygraphs;

use yii\web\AssetBundle;
use yii\web\View;

class DygraphsAsset extends AssetBundle 
{
	public $sourcePath = '@vendor/sibilino/yii2-dygraphswidget';
	public $js = [
		'widget/js/dygraph-combined.js',
	];
	public $jsOptions = [
		'position' => View::POS_HEAD,
	];
}