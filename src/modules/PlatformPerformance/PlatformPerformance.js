(function (jQuery) {
	// Private constants
	var baseColors = [ '#2ecc71', '#e74c3c', '#f39c12', '#3fcfbb', '#626f70', '#8f44ad' ],
		colors     = [];

	// Private Functions

	var getColor = function () {
		var position, color;

		if (colors.length === 0) {
			colors = JSON.parse (JSON.stringify (baseColors));
			shuffle (colors);
		}

		if (colors.length === 1) {
			position = 0;
		} else {
			position = Math.floor (Math.random () * colors.length);
		}
		color = colors [ position ];
		colors.splice (position, 1);
		return color;
	};

	var shuffle = function (a) {
		var j, x, i;
		for (i = a.length - 1; i > 0; i--) {
			j = Math.floor (Math.random () * (i + 1));
			x = a[ i ];
			a[ i ] = a[ j ];
			a[ j ] = x;
		}
		return a;
	};

	// Public functions

	var createBarChart = function (containerId, data) {
		var columnData = [],
			labels, yColors, yKeys, label, value, i;
		for (label in data) {
			if (!data.hasOwnProperty (label)) {
				continue;
			}

			if (jQuery.isPlainObject (data [ label ])) {
				yKeys = Object.keys (data [ label ]);
				yColors = [];
				labels = [];
				value = { label: label };
				for (i = 0; i < yKeys.length; i += 1) {
					value [ yKeys [ i ] ] = data [ label ][ yKeys [ i ] ];
					yColors.push (getColor ());
					labels.push (yKeys [ i ]);
				}
				columnData.push (value);
			} else {
				labels = [ ' Valor' ];
				yKeys = [ 'value' ];
				yColors = [ getColor () ];
				columnData.push ({ label: label, value: data [ label ] });
			}
		}

		jQuery ('#' + containerId).empty ();
		jQuery (function () {
			Morris.Bar ({
				element:      containerId,
				data:         columnData,
				barColors:    yColors,
				xkey:         'label',
				ykeys:        yKeys,
				labels:       labels,
				barRatio:     0.25,
				xLabelAngle:  75,
				hideHover:    'auto',
				resize:       true,
				padding:      10,
				xLabelMargin: 200
			});
		});
	};

	var createFunnelChart = function (containerId, data) {
		var columnData = [],
			label;

		for (label in data) {
			if (!data.hasOwnProperty (label)) {
				continue;
			}

			columnData.push ([ label, data [ label ] ]);
		}

		jQuery ('#' + containerId).empty ().highcharts ({
			chart:       {
				type: 'funnel'
			},
			title:       {
				text: ''
			},
			plotOptions: {
				series: {
					dataLabels: {
						enabled:       true,
						format:        '<b>{point.name}</b> ({point.y:,.2f})',
						color:         (((Highcharts.theme) && (Highcharts.theme.contrastTextColor)) || ('black')),
						softConnector: true
					},
					center:     [ '40%', '50%' ],
					cursor:     'pointer',
					neckWidth:  '30%',
					neckHeight: '25%',
					width:      '80%'
				}
			},
			legend:      {
				enabled: false
			},
			series:      [ {
				name: ' ',
				data: columnData
			} ]
		});
	};

	var createPieChart = function (containerId, data) {
		var columnData = [],
			label;

		for (label in data) {
			if (!data.hasOwnProperty (label)) {
				continue;
			}

			columnData.push ({ label: label, data: data [ label ] });
		}
		jQuery ('#' + containerId).empty ();
		jQuery.plot ('#' + containerId, columnData, {
			series: {
				pie: {
					show:        true,
					innerRadius: 0,
					label:       { show: true }
				}
			},
			colors: baseColors,
			legend: {
				show: false
			}
		});
	};

	var createPointsChart = function (containerId, data) {
		var columnData = [],
			labels, yColors, yKeys, label, value, i;
		for (label in data) {
			if (!data.hasOwnProperty (label)) {
				continue;
			}

			if (jQuery.isPlainObject (data [ label ])) {
				yKeys = Object.keys (data [ label ]);
				yColors = [];
				labels = [];
				value = { label: label };
				for (i = 0; i < yKeys.length; i += 1) {
					value [ yKeys [ i ] ] = data [ label ][ yKeys [ i ] ];
					yColors.push (getColor ());
					labels.push (yKeys [ i ]);
				}
				columnData.push (value);
			} else {
				labels = [ ' Valor' ];
				yKeys = [ 'value' ];
				yColors = [ getColor () ];
				columnData.push ({ label: label, value: data [ label ] });
			}
		}

		jQuery ('#' + containerId).empty ();
		jQuery (function () {
			Morris.Line ({
				element:      containerId,
				data:         columnData,
				xkey:         'label',
				ykeys:        yKeys,
				labels:       labels,
				lineColors:   yColors,
				parseTime:    false,
				xLabelAngle:  50,
				resize:       true,
				xLabelMargin: 200
			});
		});
	};

	window.PlatformPerformanceUtils = {
		createBarChart:    createBarChart,
		createFunnelChart: createFunnelChart,
		createPieChart:    createPieChart,
		createPointsChart: createPointsChart
	};

	jQuery (document).ready (function () {
		jQuery ('.date').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
	})
} (jQuery));