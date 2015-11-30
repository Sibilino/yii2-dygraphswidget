<?php
namespace sibilino\y2dygraphs;

use yii\base\Widget;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\web\View;
use yii\helpers\Html;
use yii\base\Model;
use yii\data\DataProviderInterface;

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
     * To be used when $data contains a data provider.
     * @var array The attributes of each of the provided models that will form the data rows, in order. If empty, all model attributes will be in each row.
     * @since 1.1.0
     */
    public $attributes = [];
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
    /**
     * CSS selector that matches checkboxes in your DOM. If this property is set, the matched checkboxes will control the visibility
     * of series in the Dygraphs chart. To associate a series with a checkbox, specify the id of the series in the {@link checkBoxReferenceAttr}
     * attribute of the checkbox.
     * @var string Optional. If this property is not set, the visibility checkbox feature will be disabled.
     * @since 1.0.0
     */
    public $checkBoxSelector;
    /**
     * The attribute of each checkbox matched by {@link $checkBoxSelector} that indicats which of the series is controlled by that checkbox.
     * @var string Optional. By default, the attribute is "id".
     * @since 1.0.0
     */
    public $checkBoxReferenceAttr = 'id';
    
    public function init() {
        if ($this->hasModel()) {
            $attr = $this->attribute;
            $this->data = $this->model->$attr;
        }
        if ($this->data instanceof DataProviderInterface) {
            $this->data = $this->getProviderData();
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

    /**
     * Extracts the data from the configured data provider in $this->data.
     * @return array
     * @since 1.1.0
     */
    protected function getProviderData()
    {
        $models = $this->data->getModels();
        $data = [];
        foreach ($models as $model) {
            $row = $model instanceof Model ? $model->attributes : $model;
            if ($this->attributes) {
                $values = [];
                foreach ($this->attributes as $attr) {
                    $values[$attr] = $row[$attr];
                }
                $row = $values;
            }
            $data [] = array_values($row);
        }
        return $data;
    }
    
    public function run() {
        
        $this->view->registerJs($this->getGraphScript(), $this->scriptPosition);
        if (isset($this->checkBoxSelector)) {
            $position = $this->scriptPosition;
            if ($position !== View::POS_LOAD) {
                $position = View::POS_READY; // Checkbox script requires JQuery; allow only POS_LOAD or POS_READY
            }
            $this->view->registerJs($this->getCheckBoxScript(), $position);
        }
        return Html::tag('div', '', $this->htmlOptions);
    }
    
    /**
     * Generates the JavaScript code that will initialize the Dygraphs object.
     * @return string
     * @since 1.0.0
     */
    protected function getGraphScript() {
        $id = $this->htmlOptions['id'];
        $options = Json::encode($this->options);
        $data = $this->processDates();
        return "
            var $this->jsVarName = new Dygraph(
                document.getElementById('$id'),
                $data,
                $options
            );
        ";
    }
    
    /**
     * Generates the JavaScript code that will to enable the visibility checkbox feature.
     * @return string
     * @since 1.0.0
     */
    protected function getCheckBoxScript() {
        return "
            // Check the checkboxes that correspond to visible series
            $.each($this->jsVarName.getOption('visibility'), function (i, val) {
                $('$this->checkBoxSelector[$this->checkBoxReferenceAttr=' + i + ']').prop('checked', val);
            });
            // On checkbox click, modify the visibility of the corresponding series
            $('$this->checkBoxSelector').click(function () {
                $this->jsVarName.setVisibility($(this).attr('$this->checkBoxReferenceAttr'), $(this).prop('checked'));
            });
        ";
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
    protected function processDates() {
        if (is_array($this->data)) {
            foreach ($this->data as &$row) {
                if ($row[0] instanceof \DateTimeInterface)
                    $row[0] = new JsExpression("new Date(".($row[0]->getTimestamp()*1000).")");
                elseif ($this->xIsDate)
                    $row[0] = new JsExpression("new Date('$row[0]')");
            }
            return Json::encode($this->data, JSON_NUMERIC_CHECK);
        }
        return Json::encode($this->data);
    }
}