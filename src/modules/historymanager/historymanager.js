(function (jQuery) {
	// Private variables
	var activeTab = jQuery ('#activetab');
	var fLabels   = [];
    var graphs    = [];
	var hfLabels  = [];
    var searchRow;
	var totalFilterGroup = 0;
	var totalFilterRow   = 0;
    var typeofdata      = [];

	// Private Graphics methods
	var createBarGraph = function (rawData, colors) {
		var columnData                                         = [],
			fieldOperation                                     = rawData.fieldoperation,
			n                                                  = (rawData.hasOwnProperty ('dataGrafico')) && (rawData.dataGrafico) ? rawData.dataGrafico.length : 0,
			i, graph, yArray = [], labelsArray = [], swCompare = false;
		if (n > 0) {

			if (rawData.dataGrafico [ 0 ].hasOwnProperty ('contador2')) {
				yArray.push ('value', 'value2');
				if (rawData.fieldlabel != undefined) {
					labelsArray.push (rawData.fieldlabel, rawData.comparelabel);
				} else {
					labelsArray.push ('Valor', 'Comparar');
				}
				swCompare = true;
			} else {
				yArray.push ('value');
				if (rawData.fieldlabel != undefined) {
					labelsArray.push (rawData.fieldlabel)
				} else {
					labelsArray.push ('Valor');
				}
			}
		}
		for (i = 0; i < n; i += 1) {
			if (swCompare) {
				columnData.push (
					{
						label:  rawData.dataGrafico [ i ][ fieldOperation ],
						value:  rawData.dataGrafico [ i ][ 'contador' ],
						value2: rawData.dataGrafico [ i ][ 'contador2' ]
					}
				);

			} else {
				columnData.push (
					{
						label: rawData.dataGrafico [ i ][ fieldOperation ],
						value: rawData.dataGrafico [ i ][ 'contador' ]
					}
				);
			}
		}

		jQuery (
			function () {
				graph = Morris.Bar (
					{
						element:      'graphic-view-div',
						data:         columnData,
						barColors:    [ colors [ Math.floor (Math.random () * colors.length) ] ],
						xkey:         'label',
						ykeys:        yArray,
						labels:       labelsArray,
						barRatio:     0.25,
						xLabelAngle:  75,
						hideHover:    'auto',
						resize:       true,
						padding:      10,
						xLabelMargin: 200
					}
				);

				graphs.push (graph);
			}
		);
	};

	var createDonutGraph = function (rawData, colors) {
		var fieldOperation = rawData.fieldoperation,
			columnData     = [],
			n              = (rawData.hasOwnProperty ('dataGrafico')) && (rawData.dataGrafico) ? rawData.dataGrafico.length : 0,
			i, graph;
		for (i = 0; i < n; i += 1) {
			columnData.push (
				{
					label: rawData.dataGrafico [ i ][ fieldOperation ],
					value: rawData.dataGrafico [ i ][ 'contador' ]
				}
			);
		}
		jQuery (
			function () {
				graph = Morris.Donut (
					{
						element: 'graphic-view-div',
						data:    columnData,
						colors:  colors,
						resize:  true
					}
				);
				graphs.push (graph);
			}
		);
	};

	var createFunnelGraph = function (rawData) {
		var fieldOperation = rawData.fieldoperation,
			columnData     = [],
			n              = (rawData.hasOwnProperty ('dataGrafico')) && (rawData.dataGrafico) ? rawData.dataGrafico.length : 0,
			i, graph;

		for (i = 0; i < n; i += 1) {
			columnData.push (
				{
					label: rawData.dataGrafico [ i ][ fieldOperation ],
					data:  rawData.dataGrafico [ i ][ 'contador' ]
				}
			);
		}

		jQuery (
			function () {
				graph = jQuery.plot (
					'#graphic-view-div',
					columnData,
					{
						series: {
							funnel: {
								show:      true,
								stem:      {
									height: 0.2,
									width:  0.4
								},
								margin:    {
									left:   0,
									right:  0,
									top:    0,
									bottom: 0
								},
								label:     {
									show:      true,
									align:     'center',
									threshold: 0.05,
									formatter: function (label, series) {
										return '<div>' + label + ': ' + series.value + '</div>'
									}
								},
								highlight: {
									opacity: 0.2
								}
							}
						},
						grid:   {
							hoverable: true,
							clickable: true
						}
					}
				);
				graphs.push (graph);
			}
		);
	};

	var createPieGraph = function (rawData, colors) {
		var fieldOperation = rawData.fieldoperation,
			columnData     = [],
			n              = (rawData.hasOwnProperty ('dataGrafico')) && (rawData.dataGrafico) ? rawData.dataGrafico.length : 0,
			i, graph;

		for (i = 0; i < n; i += 1) {
			columnData.push (
				{
					label: rawData.dataGrafico [ i ][ fieldOperation ],
					data:  rawData.dataGrafico [ i ][ 'contador' ]
				}
			);
		}
		graph = jQuery.plot (
			'#graphic-view-div',
			columnData,
			{
				series: {
					pie: {
						show:        true,
						innerRadius: 0,
						label:       { show: true }
					}
				},
				colors: colors,
				legend: {
					show: false
				}
			}
		);
		graphs.push (graph);
	};

	var createPointsGraph = function (rawData) {
		var fieldOperation = rawData.fieldoperation,
			columnData     = [],
			swCompare      = false,
			yArray         = [],
			labelsArray    = [],
			n              = (rawData.hasOwnProperty ('dataGrafico')) && (rawData.dataGrafico) ? rawData.dataGrafico.length : 0,
			i, graph;

		if (n > 0) {
			if (rawData.dataGrafico [ 0 ].hasOwnProperty ('contador2')) {
				yArray.push ('value', 'value2');
				if (rawData.fieldlabel != undefined) {
					labelsArray.push (rawData.fieldlabel, rawData.comparelabel);
				} else {
					labelsArray.push ('Valor', 'Comparar');
				}
				swCompare = true;
			} else {
				yArray.push ('value');
				if (rawData.fieldlabel != undefined) {
					labelsArray.push (rawData.fieldlabel)
				} else {
					labelsArray.push ('Valor');
				}
			}
		}
		for (i = 0; i < n; i += 1) {
			if (swCompare) {
				columnData.push (
					{
						label:  rawData.dataGrafico [ i ][ fieldOperation ],
						value:  rawData.dataGrafico [ i ][ 'contador' ],
						value2: rawData.dataGrafico [ i ][ 'contador2' ],
					}
				);
			} else {
				columnData.push (
					{
						label: rawData.dataGrafico [ i ][ fieldOperation ],
						value: rawData.dataGrafico [ i ][ 'contador' ]
					}
				);
			}
		}

		jQuery (
			function () {
				graph = Morris.Line (
					{
						element:      'graphic-view-div',
						data:         columnData,
						xkey:         'label',
						ykeys:        yArray,
						labels:       labelsArray,
						parseTime:    false,
						xLabelAngle:  50,
						resize:       true,
						xLabelMargin: 200
					}
				);
				graphs.push (graph);
			}
		);
	};

	var validateGraphicForm = function () {
		jQuery ('span[id ^= hg-]').html ('');
		jQuery ('div[id ^= hg-dv-]').removeClass ('has-error');
		var field, isValidate = true;
		if (!jQuery ('#historyField').val ()) {
			jQuery ('#hg-historyField').html ('Seleccione un campo para graficar');
			jQuery ('#hg-dv-historyField').addClass ('has-error');
			isValidate = false;
		}

		if (!jQuery ('#typeGraphic').val ()) {
			jQuery ('#hg-typeGraphic').html ('Seleccione el tipo de gráfico');
			jQuery ('#hg-dv-typeGraphic').addClass ('has-error');
			isValidate = false;
		}
		return isValidate;
	};

	// Private History methods;
	var validateSearchForm = function () {
		var field, isValidate = true;
		jQuery ('li[id ^= row-]').each (function (index, item) {

			jQuery (item).find ('span').eq (0).html ('');
			jQuery (item).find ('span').eq (1).html ('');
			jQuery (item).find ('span').eq (2).html ('');

			if (jQuery (item).find ('select').eq (0).val () == '') {
				jQuery (item).find ('span').eq (0).html ('La Variable es requerida');
				isValidate = false;
				jQuery (item).find ('select').eq (0).parent ().addClass ('has-error')
			} else {
				jQuery (item).find ('select').eq (0).parent ().removeClass ('has-error')
			}

			if (jQuery (item).find ('select').eq (1).val () == '') {
				jQuery (item).find ('span').eq (1).html ('El Operador es requerido');
				isValidate = false;
				jQuery (item).find ('select').eq (1).parent ().addClass ('has-error')
			} else {
				jQuery (item).find ('select').eq (1).parent ().removeClass ('has-error')
			}

			if (jQuery (item).find ('input').eq (0).val () == '') {
				idfield = jQuery (item).find ('input').eq (0).attr ('id')
				jQuery (item).find ('span').eq (2).html ('El Valor es requerido');
				isValidate = false;
				jQuery (item).find ('input').eq (0).parent ().parent ().addClass ('has-error')
			} else {
				jQuery (item).find ('input').eq (0).parent ().parent ().removeClass ('has-error')
			}
		});

		return isValidate;
	};

	//Public Graphics methods
	var activateTab = function () {
        var args        = Array.from(arguments),
			toActivate  = jQuery ('#' + args [0]),
            toDeactivate;
        activeTab.val (args [0]);
        args.forEach(function(element) {
            toDeactivate =  jQuery ('#' + element);
            if (toDeactivate.hasClass ('active')) {
                toDeactivate.removeClass ('active');
            }
            toDeactivate = '';
        });
        toActivate.addClass ('active');
	};

	var loadBasicGraph = function () {
		var colors,
			divToGraphic = jQuery (jQuery ('#graphic-view-div-template').html ()),
			formSearch   = jQuery ('form[name="history-search-form"]'),
			formGraphic  = jQuery ('form[name="history-graphic-form"]'),
			graphicDiv   = jQuery ('#graphic-view-div'),
			mainGraphic  = jQuery ('#main-graphic-div'),
			noDataGraph  = jQuery (jQuery ('#error-graphic-template').html ()),
			rawData,
			serialized;

		if (!validateGraphicForm ()) {
			return false;
		} else if (!validateSearchForm ()) {
			return false
		}

		serialized = formSearch.serialize () + '&' + formGraphic.serialize ();

		jQuery ('#history-body').addClass ('progress-class');
		jQuery.ajax ({
			cache:   false,
			data:    serialized,
			type:    "POST",
			url:     'index.php?module=historymanager&action=HistoryManagerAjax&file=GraphicsView&Ajax=true',
			success: function (data) {
				rawData = jQuery.parseJSON (data);
				jQuery ('#history-body').removeClass ('progress-class');

				var n = (rawData.hasOwnProperty ('dataGrafico')) && (rawData.dataGrafico) ? rawData.dataGrafico.length : 0;

				if (n === 0) {
					jQuery ('.gh-alert').remove ();
					graphicDiv.addClass ('graph simple');
					graphicDiv.append (noDataGraph);
					return;
				} else {
					graphicDiv.remove ();
					colors = rawData.colors;
					mainGraphic.append (divToGraphic);
					graphicDiv = jQuery ('#graphic-view-div');
				}

				if (rawData.tipografico === 'GRAPH_TYPE_BARS') {
					graphicDiv.addClass ('graph simple barra');
					createBarGraph (rawData, colors);
				} else if (rawData.tipografico === 'GRAPH_TYPE_DONUT') {
					graphicDiv.addClass ('graph simple donut');
					createDonutGraph (rawData, colors);
				} else if (rawData.tipografico === 'GRAPH_TYPE_POINTS') {
					graphicDiv.addClass ('graph simple puntos');
					createPointsGraph (rawData);
				} else if (rawData.tipografico === 'GRAPH_TYPE_PIE') {
					graphicDiv.addClass ('graph simple piechart');
					createPieGraph (rawData, colors);
				} else if (rawData.tipografico === 'GRAPH_TYPE_FUNNEL') {
					graphicDiv.addClass ('graph simple embudo');
					createFunnelGraph (rawData);
				}
			}
		});

	};

	// Public methods
	var addFilterGroup = function (obj) {
		var searchAdvanceButtom    = searchRow.find ('#advancedSubmitSearch'),
			addGruop               = searchRow.find ('#advanced-add-group'),
			conditionGroups        = jQuery ('.action-bar'),
			conditionGroupTemplate = jQuery (jQuery ('#condition-group-template').html ().replace (/__GROUP_ID__/g, totalFilterGroup)),
			conditionTemplate      = jQuery ('#condition-template').html ().replace (/__GROUP_ID__/g, totalFilterGroup);
		conditionGroupTemplate.find ('.conditions').append (conditionTemplate);
		conditionGroups.before (conditionGroupTemplate);
		totalFilterGroup += 1;
		totalFilterRow += 1;
		jQuery (obj).attr ('data-group', totalFilterGroup);
		jQuery ('#group-' + (totalFilterGroup - 2)).find ('.operator').removeClass ('hidden').removeAttr ('disabled');
		if (searchAdvanceButtom.hasClass ('hide')) {
			searchAdvanceButtom.removeClass ('hide');
			addGruop.addClass ('hide');
		}
	};

	var eraseFilterGroup = function (obj) {
		var elementGroup, thisGroup, idGroup, lastGroup, footerPos,
			searchAdvanceButtom = searchRow.find ('#advancedSubmitSearch'),
			addGruop            = searchRow.find ('#advanced-add-group');

		thisGroup = jQuery (obj).parent ().parent ().parent ().parent ();
		idGroup = thisGroup.attr ('id');
		var r = confirm ('¿Esás seguro de borrar el grupo de condiciones seleccionado?');
		if (r == true) {
			lastGroup = jQuery ('div.filter_goup').last ().attr ('id');
			if (idGroup == lastGroup) {
				thisGroup.prev ().find ('.operator').addClass ('hidden').attr ('disabled', 'disabled');
				totalFilterGroup -= 1;
			}
			thisGroup.remove ();
			if (jQuery ("#advanced").find (".condition-group").length < 1) {
				searchAdvanceButtom.addClass ('hide');
				addGruop.removeClass ('hide');
			}
		}
	};

	var eraseFilterValue = function (obj) {
		var elementRow = '';
		elementRow = jQuery (obj).parent ();
		elementRow.find ('input').eq (0).val ('');
		return false;
	};

	var eraseFilterRow = function (obj) {
		var prevElementRow, thisRow, thisId, lastRowId,
			infoTexto = '¿Esás seguro de borrar la condción seleccionada?';
		var r = confirm (infoTexto);
		if (r == true) {
			thisRow = jQuery (obj).parent ().parent ().parent ();
			lastRowId = thisRow.parent ().find ('li:last-child').attr ('id');
			thisId = thisRow.attr ('id');
			prevElementRow = thisRow.prev ();
			if (thisId == lastRowId) {
				prevElementRow.find ('select').eq (2).addClass ('hidden').attr ('disabled', 'disabled');
			}
			thisRow.remove ()
		}
	};

	var getHistoricalData = function () {
		if (!validateSearchForm ()) {
			searchRow.find ('.nav-tabs a[href="#advanced"]').tab ('show');
			return false;
		}
		var form = jQuery ('form[name="history-search-form"]'),
			serialized;
		serialized = form.serialize ();
		jQuery ('#history-body').addClass ('progress-class');
		jQuery.ajax ({
			cache:   false,
			data:    serialized,
			type:    "POST",
			url:     'index.php?module=historymanager&action=HistoryManagerAjax&file=SearchHistorical&Ajax=true',
			success: function (data) {
				if (activeTab.val () === 'history-data') {
                    jQuery('#historical-container').html(data);
                } else if(activeTab.val () === 'history-events') {
                    jQuery('#history-timeline').html(data);
				}
				jQuery ('#history-body').removeClass ('progress-class');
			}
		})
	};

	var setFilterRow = function (obj) {
		var elementRow, newElementRow, numRow, fieldSelect, totalRow;
		elementRow = jQuery (obj).parent ().parent ().parent ().find ('li:last-child');
		newElementRow = elementRow.clone ().attr ('id', 'row-' + totalFilterRow);
		elementRow.find ('select').eq (2).removeClass ('hidden').removeAttr ('disabled');
		newElementRow.find ('button').eq (0).removeClass ('hidden');
		newElementRow.appendTo (elementRow.parent ());
		totalFilterRow += 1;
	};

	var setHelpToField = function (obj) {
		var elementRow       = '',
			selectedOperator = '';
		selectedOperator = jQuery (obj).val ();
		elementRow = jQuery (obj).parent ().parent ();
		elementRow.find ('input').eq (0).val ('');
		elementRow.find ('input').eq (0).attr ('placeholder', hfLabels[ selectedOperator ]);
		jQuery ('#graphcs-helps').html (hfLabels[ selectedOperator ]).fadeIn (300).fadeOut (5000)
	};

	var searchHistoryByTime = function (obj) {
		var today = new Date (),
			month = ((today.getMonth () + 1) < 10) ? '0' + ((today.getMonth () + 1)) : (today.getMonth () + 1),
			date  = today.getFullYear () + '-' + month + '-' + today.getDate ();
		searchRow.find ('#historyDatefrom').val (jQuery (obj).val ());
		if (jQuery (obj).val () === '') {
			searchRow.find ('#historyDateTo').val ('');
		} else {
			searchRow.find ('#historyDateTo').val (date);
		}

	};

	var setFilterOperators = function (obj) {
		var filterRow    = '',
			selectedType = '',
			selectedId   = '',
			thisOperator = '',
			thisInput    = '';

		selectedType = jQuery (obj).children ('option:selected').attr ('data-type');
		selectedId = jQuery (obj).children ('option:selected').attr ('data-id');
		filterRow = jQuery (obj).parent ().parent ();
		thisOperator = filterRow.find ('select').eq (1);
		thisInput = filterRow.find ('input').eq (0);
		filterRow.find ('input').eq (1).val (selectedId);
		thisInput.val ('');
		if (selectedType != null && selectedType.length != 0) {
			if (jQuery.inArray (selectedType, [ 'T', 'D', 'DT' ]) !== -1) {
				filterRow.find ('.is-date').removeClass ('hide');
				thisInput.attr ('readonly', true);
				thisInput.datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
			} else {
				filterRow.find ('.is-date').addClass ('hide');
				thisInput.attr ('readonly', false);
				thisInput.datepicker ('remove');
			}
			ops = typeofdata[ selectedType ];
			if (ops != null) {
				thisOperator.empty ();
				jQuery (thisOperator).append (
					jQuery (
						'<option>',
						{
							value: '',
							text:  '-Ninguno-'
						}
					)
				);
				for (var i = 0; i < ops.length; i++) {
					var label = fLabels[ ops[ i ] ];
					if (label == null) {
						continue;
					}
					jQuery (thisOperator).append (
						jQuery (
							'<option>',
							{
								value: ops[ i ],
								text:  label
							}
						)
					);

				}
			}
		} else {
			if (selectedType == '') {
				thisOperator.options[ 0 ].selected = true;
			}
		}

	};

	var setTab = function (tab) {
		jQuery ('#activeTab').val (tab);
	};

	window.HistoryUtils = {
		activateTab:         activateTab,
		addFilterGroup:      addFilterGroup,
		eraseFilterGroup:    eraseFilterGroup,
		eraseFilterRow:      eraseFilterRow,
		eraseFilterValue:    eraseFilterValue,
		getHistoricalData:   getHistoricalData,
		loadBasicGraph:      loadBasicGraph,
		searchHistoryByTime: searchHistoryByTime,
		setTab:              setTab,
		setFilterOperators:  setFilterOperators,
		setFilterRow:        setFilterRow,
		setHelpToField:      setHelpToField
	};

	var onDocumentReadyHandler = function () {
		if (typeof alert_arr !== "undefined") {
			fLabels[ 'e' ] = alert_arr.EQUALS;
			fLabels[ 'n' ] = alert_arr.NOT_EQUALS_TO;
			fLabels[ 's' ] = alert_arr.STARTS_WITH;
			fLabels[ 'ew' ] = alert_arr.ENDS_WITH;
			fLabels[ 'c' ] = alert_arr.CONTAINS;
			fLabels[ 'k' ] = alert_arr.DOES_NOT_CONTAINS;
			fLabels[ 'l' ] = alert_arr.LESS_THAN;
			fLabels[ 'g' ] = alert_arr.GREATER_THAN;
			fLabels[ 'm' ] = alert_arr.LESS_OR_EQUALS;
			fLabels[ 'h' ] = alert_arr.GREATER_OR_EQUALS;
			fLabels[ 'bw' ] = alert_arr.BETWEEN;
			fLabels[ 'b' ] = alert_arr.BEFORE;
			fLabels[ 'a' ] = alert_arr.AFTER;
			hfLabels[ 'e' ] = 'texto o valor para comparar';
			hfLabels[ 'n' ] = 'texto o valor para comparar';
			hfLabels[ 's' ] = '¿Comienza con el texto?';
			hfLabels[ 'ew' ] = '¿Termina con el texto?';
			hfLabels[ 'c' ] = '¿Contiene el texto?';
			hfLabels[ 'k' ] = '¿No contiene el texto?';
			hfLabels[ 'l' ] = 'Valor o aaaa-mm-dd si es fecha';
			hfLabels[ 'g' ] = 'Valor o aaaa-mm-dd si es fecha';
			hfLabels[ 'm' ] = 'Valor o aaaa-mm-dd si es fecha';
			hfLabels[ 'h' ] = 'Valor o aaaa-mm-dd si es fecha';
			hfLabels[ 'bw' ] = 'inferior,superior: aaaa-mm-dd,aaaa-mm-dd';
			hfLabels[ 'b' ] = 'antes de aaaa-mm-dd';
			hfLabels[ 'a' ] = 'despues de aaaa-mm-dd';
		}

		typeofdata[ 'V' ] = [ 'e', 'n', 's', 'ew', 'c', 'k' ];
		typeofdata[ 'N' ] = [ 'e', 'n', 'l', 'g', 'm', 'h' ];
		typeofdata[ 'T' ] = [ 'e', 'b', 'a' ];
		typeofdata[ 'I' ] = [ 'e', 'n', 'l', 'g', 'm', 'h' ];
		typeofdata[ 'C' ] = [ 'e', 'n' ];
		typeofdata[ 'D' ] = [ 'e', 'b', 'a' ];
		typeofdata[ 'DT' ] = [ 'e', 'b', 'a' ];
		typeofdata[ 'NN' ] = [ 'e', 'n', 'l', 'g', 'm', 'h' ];
		typeofdata[ 'E' ] = [ 'e', 'n', 's', 'ew', 'c', 'k' ];
		searchRow = jQuery ('#history-search');
		searchRow.find ('#historyDatefrom').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
		searchRow.find ('#historyDateTo').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
	};
	jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));
