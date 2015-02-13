<?php
namespace sibilino\y2dygraphs;

use yii\base\Model;
use PHPUnit_Framework_TestCase;

class TestModel extends Model 
{
	public $chart = [
		[1, 25, 100],
		[2, 50, 90],
		[3, 100, 80],
	];
}

class DygraphsWidgetTest extends PHPUnit_Framework_TestCase {
	/* @var $widget DygraphsWidget */
	public function testInit() {
		
		$model = new TestModel();
		$widget = DygraphsWidget::begin([
			'model' => $model,
			'attribute' => 'chart',
		]);
		
		$this->assertInstanceOf('sibilino\y2dygraphs\DygraphsWidget', $widget);
		$this->assertTrue(isset($widget->htmlOptions['id']));
		$this->assertTrue(isset($widget->jsVarName));
		$this->assertEquals($model->chart, $widget->data);
		$this->assertArrayHasKey('sibilino\y2dygraphs\DygraphsAsset', $widget->view->assetBundles);
	}
	
	/* public function testRun() {
		$this->expectOutputString('<div id="test"></div>');
		$widget = $this->controller->widget('DygraphsWidget', array(
				'htmlOptions' => array('id'=>'test'),
		));
	}
	
	private function getLastScript() {
		$scripts = array_values(end(Yii::app()->clientScript->scripts));
		return end($scripts);
	}
	
	private function dataTester($data, $expected) {
		$widget = $this->controller->widget('DygraphsWidget', array(
				'data'=>$data,
		));
		$this->assertContains($expected, $this->getLastScript());
	}
	
	public function testDataUrl() {
		$this->dataTester('http://localhost/testdata.csv', "'http://localhost/testdata.csv',");
	}
	
	public function testDataFunction() {
		$this->dataTester('function () {return [[1, 3, 4],[2, 7, 20]];}', "function () {return [[1, 3, 4],[2, 7, 20]];},");
	}
	
	public function testDataArray() {
		$this->dataTester(array(
				array(1, 25, 100),
				array(2, 50, 90),
				array(3, 100, 80),
			), "[[1,25,100],[2,50,90],[3,100,80]]");
	}
	
	public function testDataWithDates() {
		$data = array(
				array("2014/01/10 00:06:50", 25, 100),
				array("2014/12/23 10:16:40", 50, 90),
				array("2015/07/01 03:09:19", 100, 80),
			);
		
		$widget = $this->controller->widget('DygraphsWidget', array(
				'data'=>$data,
				'xIsDate'=>true,
		));
		$this->assertContains(
				"[[new Date('2014/01/10 00:06:50'),25,100],[new Date('2014/12/23 10:16:40'),50,90],[new Date('2015/07/01 03:09:19'),100,80]]",
				$this->getLastScript()
				);
	}
	
	public function testVarName() {
		$widget = $this->controller->widget('DygraphsWidget', array(
				'jsVarName'=>'testvar',
		));
		$this->assertContains(
				"var testvar = new Dygraph(",
				$this->getLastScript()
		);
	}
	
	public function testOptions() {
		$widget = $this->controller->widget('DygraphsWidget', array(
				'options'=>array(
		                'strokeWidth' => 2,
		                'parabola' => array(
		                  'strokeWidth' => 0.0,
		                  'drawPoints' => true,
		                  'pointSize' => 4,
		                  'highlightCircleSize' => 6
		                ),
		                'line' => array(
		                  'strokeWidth' => 1.0,
		                  'drawPoints' => true,
		                  'pointSize' => 1.5
		                ),
		                'sine wave' => array(
		                  'strokeWidth' => 3,
		                  'highlightCircleSize' => 10
		                ),
				),
		));
		$this->assertContains(
				"{'strokeWidth':2,'parabola':{'strokeWidth':0,'drawPoints':true,'pointSize':4,'highlightCircleSize':6},'line':{'strokeWidth':1,'drawPoints':true,'pointSize':1.5},'sine wave':{'strokeWidth':3,'highlightCircleSize':10}}",
				$this->getLastScript()
		);
	}
	
	public function testHtmlOptions() {
		$this->expectOutputString('<div id="test-id" class="test-class centered" data-toggle="dropdown" onChange="alert(&#039;hello&#039;)"></div>');
		$widget = $this->controller->widget('DygraphsWidget', array(
				'htmlOptions'=>array(
					'id' =>  'test-id',
					'class' => 'test-class centered',
					'data-toggle' => 'dropdown',
					'onChange' => "alert('hello')"
				),
		));
	} */
}