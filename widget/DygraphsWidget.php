<?php
namespace sibilino\y2dygraphs;

use yii\base\Widget;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\web\View;
use yii\helpers\Html;
use yii\base\Model;

/**
 * @link https://github.com/Sibilino/yii2-dygraphswidget
 * @copyright Copyright (c) 2015 Luis Hernández Hernández
 * @license http://opensource.org/licenses/MIT MIT
 */
class DygraphsWidget extends Widget 
{
	
	/**
	 * URL to the dygraphs library to be used. If not specified, the widget will publish its own distribution of the Dygraphs library.
	 * @var string
	 */
	public $scriptUrl;
	/**
	 * The position in which the Dygraphs object init script will be registerd. Note that the Dygraphs library will always be registered in POS_HEAD.
	 * @var integer Optional, default is POS_READY.
	 */
	public $scriptPosition = View::POS_READY;
	/**
	 * Can be used together with $attribute instead of setting the $data property.
	 * @var CModel
	 */
	public $model;
	/**
	 * Attribute of $model from which $data will be taken.
	 * @var string
	 */
	public $attribute;
	/**
	 * The data array to be passed to the graph.
	 * The standard format is to use a matrix of array rows, for which $row[0] is the X axis, and $row[N] is the Y axis for the data series N.
	 * Alternatively, a string representing a JavaScript function may be passed instead. This code does not need to include the "function () {}".
	 * @link http://dygraphs.com/data.html#array
	 * @var mixed Array or string
	 */
	public $data = [];
	/**
	 * HTML options for the div containing the graph.
	 * @var array
	 */
	public $htmlOptions = [];
	/**
	 * Additional Dygraphs options that will be passed to the Dygraphs object upon initialization.
	 * @link http://dygraphs.com/options.html
	 * @var array
	 */
	public $options = [];
	/**
	 * The name of the JS variable that will receive the Dygraphs object. Optional.
	 * @var string
	 */
	public $jsVarName;
	/**
	 * If set to true and this graph's data is an array, the first column of each data row will be converted to JS Date object.
	 * @var boolean
	 */
	public $xIsDate;
	
	public function init() {
		if ($this->hasModel()) {
			$attr = $this->attribute;
			$this->data = $this->model->$attr;
		}
		if (!isset($this->htmlOptions['id'])) {
			$this->htmlOptions['id'] = $this->getId();
		}
		if (!isset($this->jsVarName)) {
			$this->jsVarName = 'dygraphs_'.$this->getId();
		}
		if (is_string($this->scriptUrl)) {
			$this->view->registerJsFile($this->scriptUrl);
		} else {
			DygraphsAsset::register($this->view);
		}
	}
	
	public function run() {
		
		$id = $this->htmlOptions['id'];
		$options = Json::encode($this->options);
		$data = $this->preprocessData();
		$js = "var $this->jsVarName = new Dygraph(
			 document.getElementById('$id'),
			 $data,
			 $options
		);";
		$this->view->registerJs($js, $this->scriptPosition);
		
		return Html::tag('div', '', $this->htmlOptions);
	}
	
	/**
	 * @return boolean whether this widget is associated with a data model.
	 */
	protected function hasModel() {
		return $this->model instanceof Model && $this->attribute !== null;
	}
	
	/**
	 * Encodes the current data into the proper JS variable.
	 * @return Ambigous <string, mixed>
	 */
	protected function preprocessData() {
		if (is_array($this->data)&& $this->xIsDate) {
			foreach ($this->data as &$row) {
				$row[0] = new JsExpression("new Date('$row[0]')");
			}
		}
		return Json::encode($this->data);
	}
}