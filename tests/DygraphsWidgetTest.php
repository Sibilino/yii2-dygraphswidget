<?php
namespace sibilino\y2dygraphs;

use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\web\View;
use yii\web\JsExpression;
use yiiunit\TestCase;
use yii\data\SqlDataProvider;

class TestModel extends Model 
{
	public $chart = [
		[1, 25, 100],
		[2, 50, 90],
		[3, 100, 80],
	];
}

class ModelRow extends Model
{
	public $x;
	public $y1;
	public $y2;
	public $y3;
}

class DygraphsWidgetTest extends TestCase {
	
	protected function setUp() {
		parent::setUp();
		$this->mockWebApplication();
	}
	
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
	
	public function testRun() {
		$this->expectOutputString('<div id="test"></div>');
		$widget = DygraphsWidget::begin([
			'htmlOptions' => ['id' => 'test'],
			'scriptPosition' => View::POS_LOAD,
		]);
		$widget->end();
		$this->assertArrayHasKey(View::POS_LOAD, $widget->view->js);
		$this->assertEquals(1, count($widget->view->js));
	}
	
	/**
	 * @dataProvider dataFormatProvider
	 */
	public function testData($data, $expected) {
		$widget = DygraphsWidget::begin([
			'data' => $data,
		]);
		$widget->end();
		$this->assertContains($expected, $this->getLastScript($widget));
	}
	
	public function dataFormatProvider() {
		return [
			['http://localhost/testdata.csv', '"http://localhost/testdata.csv",'],
			[new JsExpression('function () { return [0, 7, 21]; }'), 'function () { return [0, 7, 21]; },'],
			[[[1,25,100], [2,50,90], [3,100,80]], '[[1,25,100],[2,50,90],[3,100,80]],'],
			[[[1,'25',100], [2,'50','90'], ['3',100,80]], '[[1,25,100],[2,50,90],[3,100,80]],'],
			[$this->generateProvider(), '[[0,2,10,-5],[1,4,6,-6],[2,6,2,-7]],'],
			[$this->generateProvider(false), '[[0,2,10,-5],[1,4,6,-6],[2,6,2,-7]],'],
		];
	}

    public function testProviderAttributes() {
        $widget = DygraphsWidget::begin([
            'data' => $this->generateProvider(),
            'attributes' => ['y2','x'],
        ]);
        $widget->end();
        $this->assertContains('[[10,0],[6,1],[2,2]],', $this->getLastScript($widget));
    }
	
	public function testDataWithDates() {
		$widget = DygraphsWidget::begin([
			'data' => [
				["2014/01/10 00:06:50", 25, 100],
				["2014/12/23 10:16:40", 50, 90],
				["2015/07/01 03:09:19", 100, 80]
			],
			'xIsDate' => true,
		]);
		$widget->end();
		$this->assertContains(
			"[[new Date('2014/01/10 00:06:50'),25,100],[new Date('2014/12/23 10:16:40'),50,90],[new Date('2015/07/01 03:09:19'),100,80]],",
			$this->getLastScript($widget));

		$widget = DygraphsWidget::begin([
			'data' => [
				[new \DateTime("2014/01/10 00:06:50"), 25, 100],
				[new \DateTime("2014/12/23 10:16:40"), 50, 90],
				[new \DateTime("2015/07/01 03:09:19"), 100, 80]
			],
		]);
		$widget->end();
		$this->assertContains(
			"[new Date(1389308810000),25,100],[new Date(1419326200000),50,90],[new Date(1435712959000),100,80]],",
			$this->getLastScript($widget));
	}
	
	public function testVarName() {
		$widget = DygraphsWidget::begin([
			'jsVarName'=>'testvar',
		]);
		$widget->end();
		$this->assertContains("var testvar = new Dygraph(", $this->getLastScript($widget));
	}
	
	public function testOptions() {
		$widget = DygraphsWidget::begin([
			'options' => [
                'strokeWidth' => 2,
                'parabola' => [
                  'strokeWidth' => 0.0,
                  'drawPoints' => true,
                  'pointSize' => 4,
                  'highlightCircleSize' => 6
                ],
                'line' => [
                  'strokeWidth' => 1.0,
                  'drawPoints' => true,
                  'pointSize' => 1.5
                ],
                'sine wave' => [
                  'strokeWidth' => 3,
                  'highlightCircleSize' => 10
                ],
			],
		]);
		$widget->end();
		$this->assertContains(
				'{"strokeWidth":2,"parabola":{"strokeWidth":0,"drawPoints":true,"pointSize":4,"highlightCircleSize":6},"line":{"strokeWidth":1,"drawPoints":true,"pointSize":1.5},"sine wave":{"strokeWidth":3,"highlightCircleSize":10}}',
				$this->getLastScript($widget));
	}
	
	public function testHtmlOptions() {
		$output = DygraphsWidget::widget([
			'htmlOptions' => [
				'id' =>  'test-id',
				'class' => 'test-class centered',
				'data-toggle' => 'dropdown',
				'onChange' => "alert('hello')"
			],
		]);
		$this->assertEquals(
				'<div id="test-id" class="test-class centered" data-toggle="dropdown" onChange="alert(&#039;hello&#039;)"></div>',
				$output);
	}
	
	public function testCheckBoxes() {
		$widget = DygraphsWidget::begin([
			'htmlOptions' => ['id' => 'test-checks'],
			'scriptPosition' => View::POS_LOAD,
			'checkBoxSelector' => '.visible-series',
			'checkBoxReferenceAttr' => 'series-id',
		]);
		$widget->end();
		$this->assertEquals(2, count($widget->view->js[View::POS_LOAD]));
		$this->assertContains('.visible-series[series-id=', $this->getLastScript($widget));
	}
	
	/**
	 * @param DygraphsWidget $widget
	 * @return string
	 */
	private function getLastScript($widget) {
		$scripts = $widget->view->js[$widget->scriptPosition];
		return end($scripts);
	}

    /**
     * @param boolean $model Whether to return models (true, default) or arrays
     * @return ArrayDataProvider
     */
    private function generateProvider($model = true)
    {
        if ($model) {
            return new ArrayDataProvider(['allModels' => [
                new ModelRow(['x' => 0, 'y1' => 2, 'y2' => 10, 'y3' => -5]),
                new ModelRow(['x' => 1, 'y1' => 4, 'y2' => 6, 'y3' => -6]),
                new ModelRow(['x' => 2, 'y1' => 6, 'y2' => 2, 'y3' => -7]),
            ]]);
        } else {
            $mockProvider = $this->getMockBuilder(SqlDataProvider::className())->getMock();
            $mockProvider->method("getModels")->willReturn([
                ['x' => 0, 'y1' => 2, 'y2' => 10, 'y3' => -5],
                ['x' => 1, 'y1' => 4, 'y2' => 6, 'y3' => -6],
                ['x' => 2, 'y1' => 6, 'y2' => 2, 'y3' => -7],
            ]);
            return $mockProvider;
        }
    }
}