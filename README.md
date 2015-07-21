# Dygraphs Widget for Yii
-------------------------
A simple graph widget for Yii 2, based on [Dygraphs] (http://dygraphs.com/).

## Changelog
------------
```
1.1.0 - Support for DateTime data and Data Providers.
1.0.0 - Added visibility checkboxes feature and completed tests.
```

## Installation
---------------

### Composer

Add *sibilino/yii2-dygraphswidget* to your *composer.json* file and perform a Composer Update as usual.
```json
"require": {
	"sibilino/yii2-dygraphswidget": "*"
}
```

### Manually

If for some reason you cannot or do not want to use [Composer](https://getcomposer.org/ "Composer"), then you must create the widget folder manually, and then configure your Yii application to autoload the widget classes.

First, create the folder structure _sibilino/yii2-dygraphswidget/widget_ inside the _vendor_ subfolder of your Yii application.

Then, download the widget .zip file and extract the **contents** of its _widget_ subfolder into the folder you created in the previous step.

Next, edit _config/web.php_ and add the following entry:
```php
[
	//...
	'aliases' => [
		'@sibilino/y2dygraphs' => '@vendor/sibilino/yii2-dygraphswidget/widget',
	],
	//...
]
```

Finally, remember to use the namespace _sibilino\y2dygraphs_ when you call the widget.

## Usage
--------
In your view, create the widget with your data matrix as its *data* option.
```php
use sibilino\y2dygraphs\DygraphsWidget;

echo DygraphsWidget::widget([
	'data' => $your_data,
]);
```

## Dygraphs options
-------------------
You can set the *options* property to pass additional options to the Dygraphs object:
```php
echo DygraphsWidget::widget([
	'data' => $your_data,
	'options' => [
		'labels' => ['X', 'Sin', 'Rand', 'Pow'],
		'title'=> 'Main Graph',
		//...
	],
]);
```

## Data formats
---------------
The data to display in the widget can be specified in several ways. Consider the following examples, and make sure to read [the official documentation] (http://dygraphs.com/data.html) for more details:
- **Matrix**
```php
$data = [
	[1, 25, 100],
	[2, 50, 90],
	[3, 100, 80],
	//...
];
```
- **URL**
URL to a text file with the data.
```php
$data = 'http://dygraphs.com/dow.txt';
```
- **Model attribute**
Specify the `model` and `attribute` configuration parameters to take the data from an attribute of a `yii\base\Model` object:
```php
$myModel = UserStats::findOne($id);
// Assume $myModel->loginAttempts contains a matrix of login attempts per day
echo DygraphsWidget::widget([
	'model' => $myModel,
	'attribute' => 'loginAttempts',
	'options' => [
		//...
	],
]);
```

- **Data Provider**
The `data` property can contain a Data Provider (implementing `yii\data\DataProviderInterface`). In  this case, the
data matrix will be generated from the models provided by the Data Provider. Each data row will contain the values of
the attributes of one model. By default, all attributes of every model will be used, but you can configure the `attributes`
property to specify the list of attributes to appear in a row (the specified order will be taken into account).
```php
$provider = new ActiveDataProvider([
    'query' => User::find(),
]);
// Let's assume User contains the attributes 'id', 'joinDate', 'powerLevel'
echo DygraphsWidget::widget([
	'data' => $provider,
	'attributes' => ['joinDate', 'powerLevel'], // Display the graph of powerLevel by joinDate
	'options' => [
		//...
	],
]);
```

- **JavaScript**
JS code that returns a data object usable by Dygraphs. The code must be wrapped inside a JsExpression object:
```php
$your_data = new JsExpression('function () {
	var data = [];
      for (var i = 0; i < 1000; i++) {
        var base = 10 * Math.sin(i / 90.0);
        data.push([i, base, base + Math.sin(i / 2.0)]);
      }
      var highlight_start = 450;
      var highlight_end = 500;
      for (var i = highlight_start; i <= highlight_end; i++) {
        data[i][2] += 5.0;
      }
	return data;
}');
```

## Additional options
---------------------
The following widget properties can also be specified:
- **xIsDate**: Set this property to true if the x-values (first value in each row of the data matrix) are date strings, in order to properly convert them to JS date objects for Dygraphs.
- **scriptUrl**: The URL where the Dygraphs.js library is taken from. If not set, the widget will locally publish its own distribution of the Dygraphs library.
- **model** and **attribute**: Specify a `yii\base\Model` instance and one of its attributes in order to take the data from it.
- **attributes**: To be used when `data` contains a data provider. Configure `attributes` with a list of the model
attributes that will be in every row of the data matrix. If this list is empty, all attributes will be taken.
- **jsVarName**: Specifies a custom name for the JS variable that will receive the Dygraphs object upon creation.
- **htmlOptions**: Additional HTML attributes for the graph-containing div.

## JavaScript code in options
-----------------------------
Anytime you need to pass JavaScript code (for example, passing a function to an option), just pass a new JsExpression($your_js_code). For example:
```php
$options = [
    'underlayCallback' => new JsExpression('function(canvas, area, g)
            {
                var bottom_left = g.toDomCoords(highlight_start, -20);
                var top_right = g.toDomCoords(highlight_end, +20);
 
                var left = bottom_left[0];
                var right = top_right[0];
 
                canvas.fillStyle = "rgba(255, 255, 102, 1.0)";
                canvas.fillRect(left, area.y, right - left, area.h);
            }'),
];
```

## Visibility checkboxes
------------------------
It is often useful to hide and show some of the dataseries in a chart. The widget features helper scripts to easily control series visibily with checkboxes.

To use this feature, make sure your page has one checkbox per series in the chart, and give each checkbox an `id` attribute with the index of the series controlled by it.
Then, configure the widget with a `checkBoxSelector` that matches the group of checkboxes. For example, for a chart with 2 data series:
```html
<input class="visibility" id="0" type="checkbox">
<input class="visibility" id="1" type="checkbox">
```
```php
<?= DygraphsWidget::widget([
	'checkBoxSelector' => '.visibility',
	'data' => [
		// [x, series0, series1]
		[1, 25, 100],
		[2, 50, 90],
		[3, 100, 80],
		//...
	],
	'options' => [
		// Starting visibility
		'visibility' => [
			false,
			true,
		],
		//...
	],
	// ...
]);?>
```

The attribute that associates a checkbox with a data series (`id` in the example) can be changed by configuring `checkBoxReferenceAttr`.
