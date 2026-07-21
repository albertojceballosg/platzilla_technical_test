(function (jQuery) {
	// Private variables
	var graphs     = [];
	var searchRow;
    var selectedRow;
	var fLabels = [];
	var hfLabels = [];
	var typeofdata = [];
	var newGrup = false;
	var lastModule = [];
	var lastGraphType = '';
	var propertiesPos = 1;
	var maxGroupAllowed = 3;
	var relationFields  = [];
	var temporalData    = [];

	// Private methods
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
						value2: rawData.dataGrafico [ i ][ 'contador2' ],
					}
				);

			} else {
				columnData.push (
					{
						label: rawData.dataGrafico [ i ][ fieldOperation ],
						value: rawData.dataGrafico [ i ][ 'contador' ],
					}
				);
			}
		}

		jQuery (
			function () {
				graph = Morris.Bar (
					{
						element:      rawData.applicationcode + '-' + rawData.tipografico + '-' + rawData.graficoid,
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
						element: rawData.applicationcode + '-' + rawData.tipografico + '-' + rawData.graficoid,
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
		// Corregir applicationcode si es undefined o vacío
		if (!rawData.applicationcode || rawData.applicationcode === 'undefined') {
			rawData.applicationcode = 'otros';
		}
		var containerId = rawData.applicationcode + '-' + rawData.tipografico + '-' + rawData.graficoid;
		var $container = jQuery('#' + containerId);
		
		//console.log('createFunnelGraph llamado para:', containerId);
		//console.log('Datos del gráfico:', rawData);
		//console.log('Contenedor jQuery encontrado:', $container.length > 0);
		
		var scaleFactor = 0.5;
		var formatNumber = function (n) {
			var num = parseFloat(n);
			if (isNaN(num)) {
				num = 0;
			}
			// Mantener hasta 2 decimales si existen
			var s = String(Math.round(num * 100) / 100);
			var parts = s.split('.');
			parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
			if (parts.length > 1) {
				return parts[0] + ',' + parts[1];
			}
			return parts[0];
		};
		var computeContainerHeight = function () {
			// Altura fija de 380px para mantener consistencia con otros gráficos
			var h = 380;
			return h;
		};
		var computeContainerWidth = function () {
			var w = $container.width();
			if (!w || w <= 0) {
				w = $container.parent().width();
			}
			if (!w || w <= 0) {
				w = $container.closest('.main-box-body').width();
			}
			if (!w || w <= 0) {
				// Fallback: usar ancho de ventana menos márgenes
				w = Math.max(300, $(window).width() * 0.4);
			}
			return w;
		};
		
		var columnData = [],
			n          = (rawData.hasOwnProperty ('dataGrafico')) && (rawData.dataGrafico) ? rawData.dataGrafico.length : 0,
			i, graph, labelField;

		if (n === 0) {
			$container.find('img').hide();
			$container.find('.alert').show();
			return;
		}

		labelField = 'stringdata';
		if (!rawData.dataGrafico[0] || !rawData.dataGrafico[0].hasOwnProperty('stringdata')) {
			labelField = rawData.fieldoperation || 'stringdata';
		}

		// Paleta de colores para el embudo
		var colors = [
			'#3498db', // Azul
			'#2ecc71', // Verde
			'#f39c12', // Naranja
			'#e74c3c', // Rojo
			'#9b59b6', // Púrpura
			'#1abc9c', // Turquesa
			'#34495e', // Gris oscuro
			'#e67e22', // Naranja oscuro
			'#95a5a6', // Gris
			'#d35400'  // Naranja quemado
		];
		
		for (i = 0; i < n; i += 1) {
			var label = rawData.dataGrafico[i][labelField] || rawData.dataGrafico[i]['stringdata'] || 'Sin etiqueta';
			var value = parseFloat(rawData.dataGrafico[i]['contador']) || 0;
			
			columnData.push({
				label: label,
				data:  value,
				color: colors[i % colors.length] // Asignar color según el índice
			});
		}

		jQuery(function () {
			$container.find('img').hide();
			$container.find('.alert').hide();
			
			// Asegurar que el contenedor tenga dimensiones válidas
			var w = computeContainerWidth();
			var h = computeContainerHeight();
			
			if ($container.width() !== w || w <= 0) {
				$container.css('width', w + 'px');
			}
			if ($container.height() !== h) {
				$container.css('height', h + 'px');
			}
			
			// Verificar que las dimensiones sean válidas antes de renderizar
			var finalWidth = $container.width();
			var finalHeight = $container.height();
			
			if (finalWidth <= 0 || finalHeight <= 0) {
				console.error('Dimensiones inválidas para gráfico embudo:', {
					containerId: containerId,
					width: finalWidth,
					height: finalHeight
				});
				$container.find('.alert').show();
				return;
			}
			
			graph = jQuery.plot(
				'#' + containerId,
				columnData,
				{
					series: {
						funnel: {
							show:      true,
							stem:      {
								height: 0.12,
								width:  0.28
							},
							margin:    {
								left:   0.15,
								right:  0.15,
								top:    0.06,
								bottom: 0.02
							},
							stroke: {
								width: 1,
								color: '#ffffff'
							},
							label:     {
								show:      'auto',
								align:     'center',
								threshold: 0.0001,
								formatter: function (label, series) {
									return '<div style="white-space:nowrap;">' + label + ': ' + formatNumber(series.value) + '</div>'
								}
							},
							highlight: {
								opacity: 0.2
							}
						}
					},
					legend: {
						show: true,
						position: 'ne',
						backgroundOpacity: 0,
						labelFormatter: function (label, series) {
							return label + ' (' + formatNumber(series.value) + ')';
						}
					},
					grid:   {
						hoverable: true,
						clickable: true
					}
				}
			);
			graphs.push(graph);
			// Asegurar redibujado luego de layout (Bootstrap puede ajustar tamaños después)
			setTimeout(function () {
				var newH = computeContainerHeight();
				if ($container.height() !== newH) {
					$container.css('height', newH + 'px');
				}
				if (graph && typeof graph.resize === 'function') {
					graph.resize();
				}
				if (graph && typeof graph.setupGrid === 'function') {
					graph.setupGrid();
				}
				if (graph && typeof graph.draw === 'function') {
					graph.draw();
				}
				// Disparar evento personalizado cuando el gráfico esté completamente renderizado
				setTimeout(function() {
					$container.trigger('funnelGraphRendered');
					console.log('Gráfico de embudo renderizado:', containerId);
				}, 100);
			}, 0);

			var $tooltip = jQuery('#funnel-tooltip');
			if ($tooltip.length === 0) {
				$tooltip = jQuery('<div id="funnel-tooltip" style="position:absolute;display:none;padding:6px 8px;background:rgba(0,0,0,0.75);color:#fff;border-radius:4px;font-size:12px;z-index:9999;"></div>');
				jQuery('body').append($tooltip);
			}
			$container.off('plothover.funnel').on('plothover.funnel', function (event, pos, item) {
				if (!item || !item.series) {
					$tooltip.hide();
					return;
				}
				$tooltip.html(item.series.label + ': ' + formatNumber(item.series.value));
				$tooltip.css({ left: item.pageX + 10, top: item.pageY + 10 }).show();
			});
		});
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
			'#' + rawData.applicationcode + '-' + rawData.tipografico + '-' + rawData.graficoid,
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
						element:      rawData.applicationcode + '-' + rawData.tipografico + '-' + rawData.graficoid,
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

	var intersect = function () {
	        var params,
	            groups       = jQuery ('#graph-module-column > li'),
			resultModule = [],
			swIntersect  = true,
			lists;

        groups.each (function (index, item) {
        	var theModule = jQuery(item).find ('select').eq(0);
        	if (theModule.attr ('name') !== 'fieldsOperations[]') {
                if (theModule.val() === '') {
                    theModule.parent().addClass('has-error');
                    theModule.parent().find('.help-block').html('Selecciona el modulo');
                    swIntersect = false;
                } else {
                    theModule.parent().removeClass('has-error');
                    theModule.parent().find('.help-block').html('');
                    resultModule.push(theModule.val());
                }
            }
        });

        if (!swIntersect) {
            alert('No ha seleccionado todas las fuente de datos!');
            restartGrouping ()
        } else if (resultModule.unique().length === 1) {
            setFieldsGrouping ();
		} else {
	            params = {
	                'module': 'graficosgenerales',
	                'action': 'AjaxActions',
	                'function': 'getModulerel',
	                'Ajax': 'true',
	                'fld_module': 'graficosgenerales',
	                'relatedModules': resultModule
	            };
	            jQuery.post('index.php', params, function (data) {
	                setModuleOptions(data);
	            });
	        }
	    };

	var onAjaxFailureHandler = function (jQueryResponse) {
		alert ('Se ha presentado un error. Intenta más tarde');
	};

	var setFieldsOptions = function (responseText, obj) {
		var fieldOperation    = '',
			fieldOperationTwo = '',
			fieldSelect       = '';
		var fields = JSON.parse (responseText);
		if ((fields === null) || (fields === undefined)) {
			return;
		}
		if (obj === '') {
			fieldOperation = selectedRow.find('select').eq (1);
			fieldOperation.empty ();
            fieldOperation.append (
                jQuery (
                    '<option>',
                    {
                        value: '',
                        text:  'Seleccione campo'
                    }
                )
            );

		} else {
			fieldSelect = obj.find ('select').eq (0);
			fieldSelect.empty ();
		}

		jQuery.each (
			fields,
			function (i, field) {
				if ((field === null) || (field === undefined) || (!(field instanceof Object)) || (jQuery.isEmptyObject (field))) {
					return;
				}
				if (fieldOperation != '') {
					fieldOperation.append (
						jQuery (
							'<option>',
							{
								value: field.tablename + '.' + field.fieldname,
								text:  field.label
							}
						).attr ('data-type', field.typeofdata).attr ('data-uitype', field.uitype)
					);
				}
				if ((fieldSelect != '') && (field.uitype != 2202)) {
					if (field.typeofdata != '') {
						fieldSelect.append (
							jQuery (
								'<option>',
								{
									value: field.tablename + '.' + field.fieldname,
									text:  field.label
								}
							).attr ('data-type', field.typeofdata)
						);
					}
				}
			}
		);
		if (jQuery.inArray (jQuery (fields [ 0 ]).attr ('data-uitype'), [ '5', '70' ]) !== -1) {
			jQuery ('#dategrouping-row').show ();
		} else {
			jQuery ('#dategrouping-row').hide ();
		}
	};

	var setFieldsGrouping = function () {
        var groups       = jQuery ('#graph-module-column > li').last(),
			fieldData    = groups.find('select').eq (1),
            fieldGrouping = jQuery ('#fieldgrouping');

        if (fieldData.val () === 'cargando') {
            fieldData.parent().addClass ('has-error');
            fieldData.parent().find ('.help-block').html('Cargando...');
            groupingBy.val('TEMP');
            setGroupingBy (groupingBy);
		} else {
            fieldGrouping.empty();
            fieldData.find('option').each(function() {
            	var optons = jQuery (this);
                if (
                	(optons.val () === '') ||
					(
						(jQuery.inArray (optons.attr ('data-type'), ['V','D', 'NN', 'N']) !== -1) &&
						(jQuery.inArray (optons.attr ('data-uitype'), ['4096','53', '258', '21', '2203']) === -1)
					)
				) {
                    fieldGrouping.append (
                        jQuery ('<option>', {
                                value: optons.val (),
                                text:  optons.text ()
                            }
                        )
                    );
                }
            });
		}

	};

	var setModuleOptions = function (data) {
        var modules = JSON.parse (data),
            lastGroup, lastModule,
            groupingBy    = jQuery ('#grouping-by'),
            fieldGrouping = jQuery ('#fieldgrouping');

        if (modules.error !== undefined) {
        	alert (modules.error);
        	groupingBy.val ('TEMP');
        	setGroupingBy (groupingBy);
        } else {
            fieldGrouping.empty();
            fieldGrouping.append (
                jQuery ('<option>', {
                        value: '',
                        text:  'Seleccione'
                    }
                )
            );
            jQuery.each (
                modules,
                function (i, module) {
                    fieldGrouping.append (
                        jQuery (
                            '<option>',
                            {
                                value: module[ 0 ].main + '.' + module[ 0 ].dep + '.' + module[ 0 ].field,
                                text:  '( ' + module[ 0 ].label + ' ) ' +   module[ 0 ].dep_label + ' - ' + module[ 0 ].f_label
                            }
                        )
                    );
                });
        }
	};

	var setOperationsColumn = function () {
        var arrId, nextGroup, prevGroup,groupTemplate,
            groups          = jQuery ('#graph-module-column > li'),
			firstGroup      = groups.first (),
			firstFieldValue = firstGroup.find('select').eq(1).val(),
            firstFieldText  = firstGroup.find('select').eq(1).children ('option:selected').text(),
            lastGroup       = groups.last(),
            lastFieldValue  = lastGroup.find('select').eq(1).val (),
            lastFieldText   = lastGroup.find('select').eq(1).children ('option:selected').text(),
            idLast          = lastGroup.attr('id'),
            moreField       = jQuery ('#graph-more-fields'),
            nextGroupNum    = 0,
            r               = false,
            totalGroup      = groups.length;
        if ((firstFieldValue === '') || (lastFieldValue === '')) {
            if (firstFieldValue === '') {
                firstGroup.find('select').eq(1).parent().addClass('has-error');
                firstGroup.find('select').eq(1).parent().find('.help-block').html('Selecciona el campo');
            }
            if (lastFieldValue === '') {
                lastGroup.find('select').eq(1).parent().addClass('has-error');
                lastGroup.find('select').eq(1).parent().find('.help-block').html('Selecciona el campo');
            }

            return
        }
        arrId         = idLast.split('-');
        nextGroupNum  = parseInt (arrId[2]) + 1;
        groupTemplate = jQuery (jQuery ('#operation-group-template').html ().replace (/__GROUP_ID__/g, nextGroupNum));
        groupTemplate.appendTo('#graph-module-column');
        nextGroup = jQuery('#module-row-' + nextGroupNum);
        nextGroup.find('input').eq('0').val (firstFieldText).attr('title', firstFieldText);
        nextGroup.find('input').eq('1').val (firstFieldValue);
        nextGroup.find('input').eq('2').val (lastFieldText);
        nextGroup.find('input').eq('3').val (lastFieldValue).attr('title', lastFieldText);
        moreField.addClass('hide');
        firstGroup.find('select').eq(1).parent().removeClass('has-error');
        firstGroup.find('select').eq(1).parent().find('.help-block').html('');
        lastGroup.find('select').eq(1).parent().removeClass('has-error');
        lastGroup.find('select').eq(1).parent().find('.help-block').html('');
    };

	var restartGrouping = function () {
		var groupingBy = jQuery ('#grouping-by'),
            moreField  = jQuery ('#graph-more-fields'),
			graphicType = jQuery ('#graphictype').find ('option:selected').attr ('data-column')

        groupingBy.val ('TEMP');
        setGroupingBy (groupingBy);
        jQuery ('.calculation-select').remove ();
        var totalGroup = jQuery ('#graph-module-column > li').length;
        if ((totalGroup < maxGroupAllowed) && (graphicType === 'MULTIPLE')) {
            if (moreField.hasClass ('hide')) {
                moreField.removeClass('hide');
            }
        }
	};

	// Public methods
	var accordionFilters = function (obj) {
		var row = jQuery (obj).parent().parent ().parent().next (),
			btn = jQuery (obj).children ();

		if (btn.hasClass('fa-arrow-up')) {
			btn.removeClass ('fa-arrow-up');
			btn.addClass ('fa-arrow-down')
		} else {
            btn.removeClass ('fa-arrow-down');
            btn.addClass ('fa-arrow-up')
		}
		row.slideToggle('slow');
	};

	var addFilterGroup = function (obj) {
		var row           = jQuery (obj).closest('li').find('.row').first(),
			rowFilter     = row.next(),
			module        = row.find ('select').eq (0),
            fields        = row.find ('select').eq (1),
			fieldOptions  = row.find ('select').eq (1).find('option'),
			filterField,
            totalFilterGroup = parseInt ( jQuery (obj).attr ('data-group')),
            lastFilterGroup;

		    if (fields.val() === 'cargando') {
                fields.parent ().addClass ('has-error');
                return;
            } else {
                fields.parent ().removeClass ('has-error');
            }

			if (totalFilterGroup == 0) {
                totalFilterGroup = 1;
			} else {
                totalFilterGroup++
			}
		//footerPos = jQuery ('.fix-h').offset ();
		if (module.val () == '') {
			module.parent ().addClass ('has-error');
			module.parent ().find ('.help-block').html ('Selecciona el modulo');
			//jQuery ("html, body").animate ({ scrollTop: 0 }, 600);
			return false;
		}
		//footerPos.top = footerPos.top + 20;
		//jQuery ('.fix-h').offset ({ top: footerPos.top, left: 0 });
		var conditionGroups        = rowFilter.find('.action-bar'),
			conditionGroupTemplate = jQuery (jQuery ('#condition-group-template').html ().replace (/__GROUP_ID__/g, totalFilterGroup)),
			conditionTemplate      = jQuery ('#condition-template').html ().replace (/__GROUP_ID__/g, totalFilterGroup); //.replace(/__CONDITION_ID__/g, -1)
		conditionGroupTemplate.find ('.conditions').append (conditionTemplate);
		conditionGroups.before (conditionGroupTemplate);
        lastFilterGroup  = rowFilter.find ('.condition-group').last();
		filterField = lastFilterGroup.find ('select').eq (0);
        filterField.empty();
        filterField.append (fieldOptions.clone());
		jQuery (obj).attr ('data-group', totalFilterGroup);
	};

	var addFieldGroup = function (obj) {
		var arrId, nextGroup, prevGroup,
            groups       = jQuery ('#graph-module-column > li'),
			lastGroup    = groups.last(),
			lastModule   = lastGroup.find('select').eq(0),
            idLast       = lastGroup.attr('id'),
            moreField    = jQuery ('#graph-more-fields'),
            hasCalculation  = jQuery ('#hasCalculation'),
			nextGroupNum = 0,
			r            = false,
            totalGroup   = groups.length;

		arrId        = idLast.split('-');
		nextGroupNum = parseInt (arrId [2]) + 1;
		if (totalGroup === 2) {
			r = confirm('¿Incluir operación entres las dos primeras filas?')
		}
		if (r) {
            hasCalculation.val ('1');
			setOperationsColumn ();
		} else {
            hasCalculation.val ('0');
            lastGroup.clone().attr('id', 'module-row-' + nextGroupNum).appendTo('#graph-module-column');
            nextGroup = jQuery('#module-row-' + nextGroupNum);
            nextGroup.find('select').eq(1).empty();
            nextGroup.find('select').eq(1).append(
                jQuery(
                    '<option>',
                    {
                        value: '',
                        text: 'Seleccione campo'
                    }
                )
            );
            nextGroup.find('select').eq(2).val('');
            nextGroup.find('.grouping').prop('checked', false);
            nextGroup.find('.grouping').eq(0).attr('disabled', true);
            nextGroup.find('button').removeClass('hide');
            nextGroup.find('#graphc-module-column-titles').remove();
            nextGroup.find('.condition-group').each(function (index, item) {
                jQuery(item).remove()
            });
            totalGroup = jQuery ('#graph-module-column > li').length;
            if (totalGroup === maxGroupAllowed) {
                if (!moreField.hasClass('hide')) {
                    moreField.addClass('hide');
                }
            }
        }
    };

	var eraseFilterGroup = function (obj) {
		var elementGroup,
            thisGroup = jQuery (obj).closest ('div.filter_goup'),
            idGroup, lastGroup, footerPos,
		infoTexto = '¿Esás seguro de borrar el grupo de condiciones seleccionado?';
		idGroup = thisGroup.attr ('id');
		var r = confirm (infoTexto);
		//footerPos = jQuery ('.fix-h').offset ();
		if (r == true) {
            totalFilterGroup -= 1;
			//footerPos.top = footerPos.top - 20;
			//jQuery ('.fix-h').offset ({ top: footerPos.top, left: 0 });
			thisGroup.remove ();
		}
	};

	var closeGraphPreview = function () {
		var modal  = jQuery ('#preview'),
			iframe = modal.find ('#graphic-preview'),
			form   = jQuery ('form[name="graphform"]');
		iframe.empty ();
		if (form.length) {
			form.removeAttr ('target');
		}
	};

	var deleteGraph = function (id) {
		// Mensaje de confirmación con fallback
		var confirmMessage = (typeof alert_arr !== 'undefined' && alert_arr.SURE_TO_DELETE) 
			? alert_arr.SURE_TO_DELETE 
			: '¿Está seguro de que desea eliminar este gráfico?';
		
		if (!confirm(confirmMessage)) {
			return;
		}
		
		// Usar jQuery.ajax como alternativa moderna a Prototype
		if (typeof jQuery !== 'undefined') {
			jQuery.ajax({
				url: 'index.php',
				type: 'POST',
				data: {
					module: 'graficosgenerales',
					action: 'graficosgeneralesAjax',
					file: 'DeleteGraph',
					record: id
				},
				success: function(response) {
					if (response == 'deleted' || response.trim() == 'deleted') {
						// Obtener el módulo actual de la URL para redirigir correctamente
						var urlParams = new URLSearchParams(window.location.search);
						var currentModule = urlParams.get('module') || 'graficosgenerales';
						window.location.href = 'index.php?module=' + currentModule + '&action=index';	
					} else {
						alert('ERROR: No se pudo eliminar el gráfico. Respuesta: ' + response);
					}
				},
				error: function(xhr, status, error) {
					alert('ERROR: ' + error + '\nStatus: ' + status);
					console.error('Error al eliminar gráfico:', xhr, status, error);
				}
			});
		} else if (typeof Ajax !== 'undefined' && typeof Ajax.Request !== 'undefined') {
			// Fallback a Prototype.js si jQuery no está disponible
			new Ajax.Request(
				'index.php',
				{
					queue: { position: 'end', scope: 'command' },
					method: 'post',
					postBody: 'module=graficosgenerales&action=graficosgeneralesAjax&file=DeleteGraph&record=' + id,
					onComplete: function (response) {
						if (response.responseText == 'deleted') {
							// Obtener el módulo actual de la URL para redirigir correctamente
var urlParams = new URLSearchParams(window.location.search);
var currentModule = urlParams.get('module') || 'graficosgenerales';
window.location.href = 'index.php?module=' + currentModule + '&action=index';
						} else {
							alert('ERROR: No se pudo eliminar el gráfico');
						}
					}
				}
			);
		} else {
			alert('ERROR: No se encontró ninguna librería AJAX disponible (jQuery o Prototype)');
		}
	};

	var setFavorite = function (e, obj) {
		var graphic     = jQuery (obj),
			idGraphic   = graphic.attr ('rel'),
			params   = [
				'module=graficosgenerales',
				'action=AjaxActions',
				'function=updateFavorite',
				'Ajax=true',
				'graphicId=' + idGraphic,
				'fld_module=graficosgenerales'
			];
		graphic.parent ().addClass ('isDisabled');
        jQuery.ajax (
            'index.php',
            {
				data:     params.join ('&'),
                dataType: 'text',
                method:   'post'
            }
        ).done (function (data) {
            var response = JSON.parse (data);
            graphic.attr ('title', response.title);
            graphic.html(response.faclass);
            graphic.parent ().removeClass ('isDisabled');
        }).fail (function () {
            graphic.parent ().removeClass ('isDisabled');
        });

        e.preventDefault();
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

	var eraseModuleRow = function (obj) {
        var thisRow, thisModule, thisField,
            moreField = jQuery ('#graph-more-fields'),
            infoTexto = '¿Esás seguro de borrar el campo ';

        thisRow    = jQuery (obj).parent ().parent ().parent ();
        thisModule = thisRow.find('select').eq (0).find ('option:selected').text();
        thisField  = thisRow.find('select').eq (1).find ('option:selected').text();
        infoTexto  += '( ' +  thisModule + ' ) ' + thisField + ' ?';
        var r = confirm (infoTexto);
        if (r === true) {
        	thisRow.remove ();
            restartGrouping ()
        }
        var totalGroup = jQuery ('#graph-module-column > li').length;
        if (totalGroup < maxGroupAllowed) {
            if (moreField.hasClass ('hide')) {
                moreField.removeClass('hide');
            }
        }
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

	var filterApplications = function (moduleSelect) {
		var module             = jQuery (moduleSelect),
			moduleName         = module.val (),
			moduleText         = module.find ('option:selected').text(),
			applications       = module.closest ('form').find ('#applicationcodes'),
			applicationOptions = applications.find ('option'),
			i, applicationOption, applicationModuleNames,
            firstGroupId =  jQuery ('#graph-module-column li').first ().attr('id'),
			thisGroupId  = module.parent ().parent ().parent ().attr('id');

		if (applicationOptions.length === 0) {
			return;
		}

		for (i = 0; i < applicationOptions.length; i += 1) {
			applicationOption = jQuery (applicationOptions [ i ]);
			applicationModuleNames = applicationOption.attr ('data-modules').split (', ').filter (function (x) {
				return (x !== null) && (x !== undefined) && (x.trim () !== '');
			});
			if ((moduleName === null) || (moduleName === undefined) || (moduleName.trim () === '') || (jQuery.inArray (moduleName, applicationModuleNames) === -1)) {
				applicationOption.addClass ('hidden').prop ('selected', false);
			} else {
				applicationOption.removeClass ('hidden');
			}
		}
	};

	var getGraphicalColumns = function (moduleSelect) {
		var module          = jQuery (moduleSelect),
            groups          = jQuery ('#graph-module-column > li'),
			groupingBy      = jQuery ('#grouping-by'),
            moreField       = jQuery ('#graph-more-fields'),
            hasCalculation  = jQuery ('#hasCalculation'),
			moduleName      = module.val (),
			liID            = module.closest('li').attr('id').split('-'),
            numModule       = parseInt (liID [2]),
            row             = module.parent ().parent (),
			conditionGroups = row.next().find('.filter_goup'),
			infoTexto       = 'Esta operación borrará todos los filtros y fuentes de datos, ¿Continuar?',
            fieldOperation,
			params,
			countModule    = 0;
        row.find('button').eq(0).removeAttr('disabled');
        jQuery ('.wmodule').each(function(index) {
            if (jQuery(this).find('option:selected').val() === moduleName) {
            	countModule++;
			}
        });

        if (countModule > 1) {
            row.find('button').eq(0).attr('disabled',true);
		}

		if ((relationFields.length === 0) || relationFields[numModule] === undefined ) {
            relationFields[numModule] = {'modules': undefined, 'fieldNames': [], 'fieldLabels': [], 'tableNames': []};
            relationFields[numModule].modules = moduleName;
        }else if(relationFields[numModule] === undefined) {
            relationFields[numModule].modules = moduleName;
		} else if ((relationFields[ numModule ].modules !== moduleName) && (conditionGroups.length > 0)) {
            var r = confirm (infoTexto);
            if (r === true) {
                conditionGroups.each (function (index, item) {
                    jQuery(item).remove();
                });
                relationFields[numModule] = {'modules': undefined, 'fieldNames': [], 'fieldLabels': [], 'tableNames': []};
                relationFields[numModule].modules = moduleName;
                groupingBy.val ('TEMP');
            } else {
                module.val (relationFields[numModule].modules);
                 return;
            }
        } else if ((relationFields[ numModule ].modules !== moduleName)) {
            relationFields[numModule] = {'modules': undefined, 'fieldNames': [], 'fieldLabels': [], 'tableNames': []};
            relationFields[numModule].modules = moduleName;
            if(hasCalculation.val () === '1') {
                groups.last().remove ();
                hasCalculation.val ('0');
                moreField.removeClass ('hide');
            }
            groupingBy.val ('TEMP');
		}
        module.parent ().removeClass ('has-error');
        module.parent ().find ('.help-block').html ('');
        fieldOperation = row.find('select').eq (1);
        fieldOperation.empty()
		.append (
				jQuery (
					'<option>',
					{
						value: 'cargando',
						text:  'cargando..'
					}
				)
			);

        row.find('select').eq (2).val ('');
		params = [
			'module=graficosgenerales',
			'action=AjaxActions',
			'function=getColumns',
			'Ajax=true',
			'fld_module=' + encodeURIComponent (moduleName)
		];
		jQuery.ajax (
			'index.php',
			{
				data:     params.join ('&'),
				dataType: 'text',
				method:   'post'
			}
		).done (function (responseText) {
            var fields = JSON.parse (responseText);
            fieldOperation.empty ();
            fieldOperation.append (
                jQuery (
                    '<option>',
                    {
                        value: '',
                        text:  'Seleccione campo'
                    }
                )
            );
            jQuery.each (
                fields,
                function (i, field) {
                    if ((field === null) || (field === undefined) || (!(field instanceof Object)) || (jQuery.isEmptyObject (field))) {
                        return;
                    }
                    if (fieldOperation != '') {
                        fieldOperation.append (
                            jQuery (
                                '<option>',
                                {
                                    value: field.tablename + '.' + field.fieldname,
                                    text:  field.label
                                }
                            ).attr ('data-type', field.typeofdata).attr ('data-uitype', field.uitype)
                        );
                    }
                    if (field.uitype == 10) {
                        relationFields[numModule].fieldNames.push(field.fieldname);
                        relationFields[numModule].tableNames.push(field.tablename);
                        relationFields[numModule].fieldLabels.push(field.label);
                    }
                }
            );
            fieldOperation.parent ().removeClass ('has-error');
        }).fail (onAjaxFailureHandler);

	};

	var getNumericColumns = function (obj) {
		var selectedField     = '',
			operSelected      = '',
			dataType          = '',
			numFields         = 0,
			destinationSelect = jQuery ('#fieldgrouping'),
			destinationValue,
            gridFieldGroup    = jQuery ('#group-fieldgridoperation'),
            gridField         = jQuery ('#fieldgridoperation option'),
			originData        = jQuery ('#fieldoperation option'),
			origenDataType,
			trRow             = jQuery ('#grouping-row');

		selectedField = jQuery ('#fieldoperation').val ();
		operSelected = Number (jQuery (obj).val ());
		destinationSelect.empty ();
        if (! gridFieldGroup.hasClass('hide')) {
            originData = gridField;
            origenDataType = jQuery ('#fieldgridoperation option:selected').attr ('data-type');
        } else {
            origenDataType = jQuery ('#fieldoperation option:selected').attr ('data-type');
		}
		if ((selectedField !== null) && (operSelected !== 1)) {
			originData.each (function () {
				destinationValue = jQuery (this).val ().split('@');
				dataType = jQuery (this).attr ('data-type');
				if (jQuery.inArray (dataType, [ 'N', 'NN' ]) !== -1) {
					numFields += 1;
					destinationSelect.append (
						jQuery (
							'<option>',
							{
								value: destinationValue [0],
								text:  jQuery (this).text ()
							}
						)
					);
				}
			});
			if (numFields > 0) {
				trRow.removeClass ('hide');
                if (jQuery.inArray (origenDataType, [ 'N', 'NN' ]) === -1) {
                    jQuery (obj).val (1);
                    alert ('El campo seleccionado no es númericos! Imposible calcular Suma o Promedio');
                }
			} else {
				jQuery (obj).val (1);
				alert ('No hay Campos númericos! Imposible calcular Suma o Promedio');
				trRow.addClass ('hide');
			}
		} else {
			trRow.addClass ('hide');
		}
	};

	var getTypoOfOperation = function (obj) {
		var selectedType   = '',
			dataType       = '',
			operatorSource = jQuery ('#opcolumnTwo'),
			operators      = [ 'Conteo', 'Suma', 'Promedio' ],
			numOperations  = 0,
			trRow          = jQuery ('.graphcis-oper-two');
		selectedType = jQuery (obj).val ();
		dataType = jQuery (obj).children ('option:selected').attr ('data-type');

		operatorSource.empty ();
		if (selectedType != '') {
			if (jQuery.inArray (dataType, [ 'N', 'NN' ]) !== -1) {
				numOperations = 3;
			} else {
				numOperations = 1;
			}
			for (var i = 0; i < numOperations; i++) {
				operatorSource.append (
					jQuery ('<option>', {
							value: (i + 1),
							text:  operators[ i ]
						}
					)
				);
			}
			trRow.removeClass ('hide');
		} else {
			trRow.addClass ('hide');
		}
	};

	var loadBasicGraph = function (rawData, colors) {
		// Corregir applicationcode si es undefined o vacío
		if (!rawData.applicationcode || rawData.applicationcode === 'undefined') {
			rawData.applicationcode = 'otros';
		}
		
		var n = (rawData.hasOwnProperty ('dataGrafico')) && (rawData.dataGrafico) ? rawData.dataGrafico.length : 0;

		if (n === 0) {
			jQuery ('#' + rawData.applicationcode + '-' + rawData.tipografico + '-' + rawData.graficoid + ' img').hide ();
			jQuery ('#' + rawData.applicationcode + '-' + rawData.tipografico + '-' + rawData.graficoid + ' > .alert').show ();
			return;
		} else {
			jQuery ('#' + rawData.applicationcode + '-' + rawData.tipografico + '-' + rawData.graficoid).empty ();
		}

		if (rawData.tipografico === 'barra') {
			createBarGraph (rawData, colors);
		} else if (rawData.tipografico === 'donut') {
			createDonutGraph (rawData, colors);
		} else if (rawData.tipografico === 'puntos') {
			createPointsGraph (rawData);
		} else if (rawData.tipografico === 'piechart') {
			createPieGraph (rawData, colors);
		} else if (rawData.tipografico === 'embudo' || rawData.tipografico === 'funnel') {
			createFunnelGraph (rawData);
		}
	};

	var loadBoxScoreSimpleGraph = function (rawData, colors) {
		var compare = rawData.comparar,
			labels  = [],
			data    = [],
			weeks   = rawData.semanas,
			week,
			n       = (rawData.hasOwnProperty ('dataGrafico')) && (rawData.dataGrafico) ? rawData.dataGrafico.length : 0,
			i, color, graph;

		if (n === 0) {
			return;
		}

		for (week in weeks) {
			if (!weeks.hasOwnProperty (week)) {
				continue;
			}
			data.push (
				{
					x: weeks [ week ][ 0 ][ 'fecha' ],
					y: weeks [ week ][ 0 ][ 'valor' ] ? weeks [ week ][ 0 ][ 'valor' ] : 0,
					z: weeks [ week ][ 1 ][ 'valor' ] ? weeks [ week ][ 1 ][ 'valor' ] : 0,
					a: weeks [ week ][ 2 ][ 'valor' ] ? weeks [ week ][ 2 ][ 'valor' ] : 0
				}
			);
		}
		labels.push (weeks [ week ][ 0 ][ 'titulo' ]);
		labels.push (weeks [ week ][ 1 ][ 'titulo' ]);
		labels.push (weeks [ week ][ 2 ][ 'titulo' ]);

		if (compare === '1') {
			graph = Morris.Bar (
				{
					element:   'graph-bar-' + rawData.graficoid,
					data:      data,
					barColors: colors,
					xkey:      'x',
					ykeys:     [ 'y', 'z', 'a' ],
					labels:    labels,
					resize:    true
				}
			);
			graphs.push (graph);
		}

		for (i = 0; i < n; i += 1) {
			data = [];
			for (week in weeks) {
				if (!weeks.hasOwnProperty (week)) {
					continue;
				}
				data.push (
					{
						label: rawData.dataGrafico [ i ][ 'semanal' ][ week ][ 'fecha' ],
						value: rawData.dataGrafico [ i ][ 'semanal' ][ week ][ 'valor' ] ? rawData.dataGrafico [ i ][ 'semanal' ][ week ][ 'valor' ] : 0
					}
				);
			}

			color = colors [ Math.floor (Math.random () * colors.length) ];
			colors.unshift (color);
			graph = Morris.Bar (
				{
					element:     'hero-bar-' + rawData.graficoid + '-' + rawData.dataGrafico [ i ][ 'boxscoreid' ] + '-' + rawData.dataGrafico [ i ][ 'box_score_dataid' ],
					data:        data,
					barColors:   colors,
					xkey:        'label',
					ykeys:       [ 'value' ],
					labels:      [ 'Valor' ],
					barRatio:    0.4,
					xLabelAngle: 35,
					hideHover:   'auto',
					resize:      true
				}
			);
			graphs.push (graph);
		}
	};

	var loadFunnelGraph = function (rawData) {
		var data = [],
			n    = (rawData.hasOwnProperty ('dataGrafico')) && (rawData.dataGrafico) ? rawData.dataGrafico.length : 0,
			i;

		for (i = 0; i < n; i += 1) {
			data.push (
				{
					label: rawData.dataGrafico [ i ][ 'headers' ],
					y:     parseFloat (rawData.dataGrafico [ i ][ 'data' ])
				}
			);
		}
		jQuery ('#funnel-' + rawData.applicationcode + '-' + rawData.graficoid).highcharts (
			{
				chart:       {
					type:        'funnel',
					marginRight: 100
				},
				title:       {
					text: '',
					x:    -50
				},
				plotOptions: {
					series: {
						dataLabels: {
							enabled:       true,
							format:        '<b>{point.name}</b> ({point.y:,.2f})(€)',
							color:         (((Highcharts.theme) && (Highcharts.theme.contrastTextColor)) || 'black'),
							softConnector: true
						},
						neckWidth:  '30%',
						neckHeight: '25%',
						cursor:     'pointer',
						point:      {
							events: {
								click: function () {
									location.href = this.options.url;
								}
							}
						}
					}
				},
				legend:      {
					enabled: false
				},
				series:      [ {
					name: ' ',
					data: data
				} ]
			}
		);
	};

	var openGraphPreview = function () {
		var modal        = jQuery ('#preview'),
	            iframe       = modal.find ('#graphic-preview'),
	            form        = jQuery ('form[name="graphform"]'),
			actionForm  = jQuery ('#action-form'),
			actionAjax  = jQuery ('#ajax-action-form'),
			formData;
		iframe.empty ();
		iframe.html('<div style="display: flex; justify-content: center; align-items: center; min-height: 200px;"><img src="themes/images/loading.gif" alt="Loading" style="max-width: none; max-height: none;"/></div>');
		if (validateGraphForm()) {
			modal.modal('show');
			actionForm.val('Preview');
			actionAjax.val ('true');
			formData = form.serialize();
			actionForm.val('SaveEditGraph');
			actionAjax.val ('false');

			jQuery.ajax({
				url: 'index.php',
				type: 'POST',
				data: formData,
				dataType: 'html'
			}).done(function (html) {
			// Buscar scripts en el HTML antes de parsearlo
			var scriptMatches = html.match(/<script[^>]*>[\s\S]*?<\/script>/gi);
			
			var temp = jQuery('<div/>').html(html);
			var scripts = temp.find('script');
			
			// Verificar si hay código de Google Charts
			var hasGoogleCharts = html.indexOf('google.charts') !== -1;
			
			scripts.remove();
			iframe.empty().append(temp.contents());
			
			// Ejecutar scripts con delay para asegurar que el DOM esté listo
			var scriptsToExecute = [];
			scripts.each(function () {
				var src = this.src;
				var code = this.text || this.textContent || this.innerHTML;
				scriptsToExecute.push({src: src, code: code});
			});
			
			// Si jQuery no encontró el script de Google Charts pero está en el HTML,
			// extraerlo manualmente del HTML raw
			if (hasGoogleCharts && scriptsToExecute.length < 3) {
				if (scriptMatches) {
					scriptMatches.forEach(function(scriptTag) {
						var codeMatch = scriptTag.match(/<script[^>]*>([\s\S]*?)<\/script>/i);
						if (codeMatch && codeMatch[1]) {
							var code = codeMatch[1].trim();
							if (code && code.indexOf('google.charts') !== -1) {
								scriptsToExecute.push({src: null, code: code});
							}
						}
					});
				}
			}
			
			// Función para verificar si Google Charts está disponible
			var waitForGoogleCharts = function(callback, maxAttempts) {
				maxAttempts = maxAttempts || 50;
				var attempts = 0;
				
				var checkGoogle = function() {
				attempts++;
				if (typeof google !== 'undefined' && google.charts) {
					callback();
				} else if (attempts < maxAttempts) {
					setTimeout(checkGoogle, 100);
				} else {
					callback(); // Intentar ejecutar de todos modos
				}
			};
				
				checkGoogle();
			};
			
			// Función para ejecutar scripts secuencialmente
			var executeScripts = function(index) {
				if (index >= scriptsToExecute.length) {
					return;
				}
				
				var script = scriptsToExecute[index];
				if (script.src) {
					jQuery.getScript(script.src).done(function() {
						executeScripts(index + 1);
					}).fail(function() {
						executeScripts(index + 1);
					});
				} else if (script.code && script.code.trim() !== '') {
					try {
						// Si el script contiene google.charts, esperar a que esté disponible
						if (script.code.indexOf('google.charts') !== -1) {
							waitForGoogleCharts(function() {
								try {
									jQuery.globalEval(script.code);
									executeScripts(index + 1);
								} catch (e) {
									executeScripts(index + 1);
								}
							});
						} else {
							jQuery.globalEval(script.code);
							executeScripts(index + 1);
						}
					} catch (e) {
						executeScripts(index + 1);
					}
				} else {
					executeScripts(index + 1);
				}
			};
			
			// Iniciar ejecución de scripts después de un pequeño delay
			setTimeout(function() {
				executeScripts(0);
			}, 300);
		}).fail(function (xhr) {
			iframe.html(xhr && xhr.responseText ? xhr.responseText : 'ERROR: No se pudo generar la previsualización');
		});
	        }
	};

	var selectCategory = function (obj) {
		var graphicCategory = jQuery ('#graphicCategory'),
			thisBtn         = jQuery (obj),
            partnerBtn      = jQuery (thisBtn.attr ('data-partner'));

		if (graphicCategory.val() !== thisBtn.attr ('data-category')) {
            graphicCategory.val (thisBtn.attr ('data-category'));
            thisBtn.removeClass('btn-default').addClass('btn-primary');
            partnerBtn.removeClass('btn-primary').addClass('btn-default');
            submitSearch();
		}

    };

	var setFieldGrouping = function (obj) {
        var thisCheck    = jQuery (obj),
            row          = thisCheck.parent ().parent ().parent ().parent ().parent ().parent (),
            fieldRow     = row.find('select').eq (1),
            fieldGroup   = jQuery ('#fieldgrouping'),
            dateGroupRow = jQuery ('#graph-dategrouping-row'),
            dateGroup    = jQuery ('#dategrouping'),
			fieldType    = fieldRow.find ('option:selected').attr ('data-uitype');
        if (thisCheck.is(':checked')) {
            if (fieldType === '2203') {
                alert('Imposible agrupar por campos tipos tablas!');
                thisCheck.prop('checked', false);
                return;
            }
            jQuery ('.grouping').each(function(){ this.checked = false; });
            thisCheck.prop('checked', true);
            dateGroupRow.addClass('hide');
            dateGroup.val ('');
            fieldGroup.val (fieldRow.val());
        } else {
            jQuery ('.grouping').each(function(){ this.checked = false; });
            dateGroupRow.removeClass('hide');
            fieldGroup.val ('');
        }
    };

	var setGroupingBy = function (obj) {
		var groupingBy = jQuery (obj),
            fieldToGroup,
			fieldGrouping = jQuery ('#fieldgrouping'),
			dateGrouping  = jQuery ('#dategrouping');
		if (groupingBy.val () === 'TEMP') {
            fieldGrouping.val ('');
            fieldGrouping.parent ().addClass('hide');
            dateGrouping.parent ().removeClass('hide');
		} else {
            dateGrouping.val('');
            fieldGrouping.parent().removeClass('hide');
            dateGrouping.parent().addClass('hide');
            fieldToGroup = intersect ();
		}
    };

	var setSecondField = function (obj) {
		var graphType = '',
            gridFieldGroup = jQuery ('#group-fieldgridoperation');
		graphType = jQuery (obj).val ();

		if ((gridFieldGroup.hasClass('hide')) && ((graphType == 'barra') || ( graphType == 'puntos'))) {
			jQuery ('.graphcis-two').removeClass ('hide');
		} else {
			jQuery ('.graphcis-two').addClass ('hide');
			jQuery ('.graphcis-oper-two').addClass ('hide');
			jQuery ('#fieldoperationTwo').val ('');
		}
	};

	var setDateGroupingVisibility = function (columnSelect) {
		var params       = [],
			column          = jQuery (columnSelect),
            form            = column.closest ('form'),
            calculationType = form.find ('#opcolumn'),
			dateGrouping    = form.find ('#dategrouping-row'),
            graphicType     = form.find ('#graphictype'),
			gridFieldGroup  = form.find ('#group-fieldgridoperation'),
            moduleName      = form.find ('#wmodule').val (),
			operation       = form.find ('#opcolumn'),
			operationValue  = parseInt (operation.val ()),
			uiType          = column.find ('option:selected').attr ('data-uitype'),
			values          = column.find('option:selected').val().split ('@'),
			texts           = column.find('option:selected').text().split ('.');

        calculationType.val (1);
        graphicType.val ('');
		if (! gridFieldGroup.hasClass('hide')) {
            gridFieldGroup.addClass('hide');
            form.find  ('#fieldgridoperation').empty ();
		}
		if ((operationValue === 1) && (jQuery.inArray (uiType, [ '5', '70' ]) !== -1)) {
            dateGrouping.hide();
        }else if (uiType == '2202') {
			var labels = texts [ 0 ].split (':');

			dateGrouping.hide();
            if ((moduleName === null) || (moduleName === undefined) || (moduleName.trim () === '')) {
                return;
            }
	            params = [
                'module=graficosgenerales',
                'action=AjaxActions',
                'function=getGridNumericColumns',
                'Ajax=true',
                'fld_module=' + encodeURIComponent (moduleName),
				'fieldname=' + encodeURIComponent (values[ 0 ]),
				'fieldlabel=' + encodeURIComponent (labels [ 1 ].trim ())
            ];
            jQuery.ajax (
                'index.php',
                {
					data:     params.join ('&'),
                    dataType: 'text',
                    method:   'post'
                }
            ).done (function (data) {
				if (data) {
                    setFieldsOptions (data, gridFieldGroup);
                    gridFieldGroup.removeClass('hide');
                    form.find  ('.graphcis-two').addClass ('hide');
                    form.find  ('.graphcis-oper-two').addClass ('hide');
                    form.find  ('#fieldoperationTwo').val ('');
				}
            }).fail (function () {
                gridFieldGroup.addClass('hide');
            });

		} else {
			dateGrouping.hide ();
		}
	};

	var setFieldGrid = function (obj) {
        var calculationType = jQuery ('#opcolumn');
        calculationType.val (1);
	};

	var setFieldOperation = function (obj) {
		var fields         = jQuery(obj),
            groups         = jQuery ('#graph-module-column > li'),
            hasCalculation = jQuery ('#hasCalculation'),
            moreField      = jQuery ('#graph-more-fields'),
			row            = fields.parent ().parent(),
			operationType  = row.find('select').eq (2),
            check          = row.find('.grouping'),
            dataType       = fields.find ('option:selected').attr ('data-type');

        restartGrouping ();
        operationType.val ('');
		operationType.children ('option:not(:selected)').attr ('disabled', false);
        if (jQuery.inArray (dataType, [ 'N', 'NN' ]) === -1) {
            operationType.children ().each (function (i) {
                if (jQuery.inArray (jQuery (this).val (), [ '2', '3', '4', '5']) !== -1) {
                    jQuery (this).attr ('disabled', true);
                }
            });
        }
        check.prop ('disabled', false);
	};

	var setGraphicProperties = function (obj) {
		var arrow = jQuery ('#span-properties-arrow').children();
		jQuery (obj).nextUntil('tr.header').slideToggle('slow');

        if (arrow.hasClass('fa-arrow-up')) {
            arrow.removeClass ('fa-arrow-up');
            arrow.addClass ('fa-arrow-down')
        } else {
            arrow.removeClass ('fa-arrow-down');
            arrow.addClass ('fa-arrow-up')
        }
    };

	var setGraphicType = function (obj) {
		var graphicType     = jQuery (obj),
            columnType      = graphicType.find ('option:selected').attr ('data-column'),
			moreField       = jQuery ('#graph-more-fields'),
			graphGroupNum   = jQuery ('#graph-module-column').children().length,
			properties      = jQuery ('#graphic-properties-contenect'),
            propertiesTitle = jQuery ('#span-properties-title'),
			propertiesInfo  = 'Propiedades para gráfica ' + graphicType.find ('option:selected').text (),
			includeTable    = jQuery ('#graph-table-include'),
            params          = [
                'module=graficosgenerales',
                'action=AjaxActions',
                'function=getChartProperties',
                'Ajax=true',
                'graphic=' + encodeURIComponent (graphicType.val ()),
				'fld_module=graficosgenerales'
            ];

		if (graphGroupNum > 1 && lastGraphType !== '') {
			var r = confirm('Esta operación eliminará las opcciones previamente seleccionadas ¿Continuar?');
			if (r === true) {
                for (var i = 1; i<graphGroupNum; i++) {
                	jQuery ('#module-row-' + i).remove();
				}
                restartGrouping ();
			} else {
                graphicType.val (lastGraphType);
                return;
			}
		}
        lastGraphType = graphicType.val ();
		if (columnType === 'MULTIPLE') {
			moreField.removeClass ('hide');
		} else {
			if (!moreField.hasClass ('hide')) {
                moreField.addClass('hide');
			}
		}
		if (graphicType.val () === '') {
            jQuery ('.header').addClass ('hide');
            properties.html ('');
            return;
		} else if(graphicType.val () === 'table') {
            includeTable.addClass ('hide');
            jQuery ('#check-include-table').prop ('checked', false);
		} else {
			includeTable.removeClass ('hide');

		}
        jQuery.ajax (
            'index.php',
            {
                data:     params.join ('&'),
                dataType: 'text',
                method:   'post'
            }
        ).done (function (data) {
            propertiesTitle.html (propertiesInfo);
            properties.html (data);
			jQuery ('.header').removeClass ('hide')
        }).fail (function () {
            jQuery ('.header').addClass ('hide');
            properties.html ('');
        });
	};

	var setHelpToField = function (obj) {
		var elementRow       = '',
			selectedOperator = '';
		selectedOperator = jQuery (obj).val ();
		elementRow = jQuery (obj).parent ().parent ();
        elementRow.find ('input').eq (0).attr('readonly', false);
		elementRow.find ('input').eq (0).val ('');
		if ((selectedOperator === 'in') || (selectedOperator === 'inn')) {
            elementRow.find ('input').eq (0).val(hfLabels[ selectedOperator ]);
            elementRow.find ('input').eq (0).attr('readonly', true);
		} else {
            elementRow.find ('input').eq (0).attr ('placeholder', hfLabels[ selectedOperator ]);
		}

		jQuery ('#graphcs-helps').html (hfLabels[ selectedOperator ]).fadeIn (300).fadeOut (5000)
	};

	var searchGraphicsByTime = function (obj) {
        var today     = new Date (),
            date      = today.getFullYear () + '-' + (today.getMonth () + 1) + '-' + today.getDate (),
			selectedDate = jQuery (obj).val();

		if (selectedDate !== 'CUSTOM_DATE') {
            searchRow.find('#graphicsDatefrom').val(jQuery(obj).val());
            searchRow.find('#graphicsDateTo').val(date);
            searchRow.find ('#graphicsDatefrom').datepicker ('remove');
            searchRow.find ('#graphicsDateTo').datepicker ('remove');
        } else {
            searchRow.find ('#graphicsDatefrom').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
            searchRow.find ('#graphicsDateTo').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
		}
	};

    var searchGraphicsHome = function (obj) {
        var today = new Date (),
            date  = today.getFullYear () + '-' + (today.getMonth () + 1) + '-' + today.getDate (),
			from  = jQuery ('#graphic-tab-from'),
			to    = jQuery ('#graphic-tab-to');
        if (jQuery (obj).val () === '') {
            return
        }
        from.val (jQuery (obj).val());
        submitSearch();
    };

    var customSearch = function () {
        var period = jQuery ('#graphic-tab-period');
        period.val('');
        submitSearch();
    };

	var setFilterOperators = function (obj) {
		var filterRow    = '',
			selectedType = '',
			thisOperator = '';
		selectedType = jQuery (obj).children ('option:selected').attr ('data-type');
		filterRow = jQuery (obj).parent ().parent ();
		thisOperator = filterRow.find ('select').eq (1);
		if (selectedType != null && selectedType.length != 0) {
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

	var setTab = function (obj, event, tab) {
		var btn = jQuery(obj),
			group = btn.parent();

		group.find('a').removeClass('btn-primary').addClass('btn-default');
		jQuery('#activeTab').val(tab);
        btn.removeClass('btn-default').addClass('btn-primary');
		event.preventDefault()
    };

	var submitSearch = function () {
		var serialized = jQuery('#graphic-filters').serialize (),
			graphicDiv = jQuery('#graphic-listview');
		graphicDiv.empty ();
        graphicDiv.html('<img src="themes/images/loading.gif" alt="Loading" class="center-block"  style="width: 50%;height: auto;margin-top: 40px;"/>');
        jQuery.ajax ({
            cache:   false,
            data:    serialized,
            type:    "POST",
            url:     'index.php?module=graficosgenerales&action=index&Ajax=true',
            success: function (data) {
				graphicDiv.html (data)
            }
        })
    };

	var validateGraphForm = function () {
	    var formElement = jQuery ("form[name='graphform'] :input"),
            form        = jQuery ('form[name="graphform"]'),
            isInstance  = jQuery ('#is-instance').val () ? true : false,
            isValidate = true,
            field, operationValue, value,operationUitype;
		jQuery ('span[id ^= gr-]').html ('');
		jQuery ('td[id ^= gr-td-]').removeClass ('has-error');
        jQuery ('div[id ^= gr-td-]').removeClass ('has-error');
		jQuery ('td[id ^=grouping-row]').removeClass ('has-error');

		formElement.map(function(index, elm) {
            var element      = jQuery (elm),
                elementTitle = element.attr ('title'),
                value        = element.val ();
                if((jQuery.inArray (elm.type, ['hidden', 'button', 'submit', 'select-multiple', 'checkbox', 'undefined']) === -1) && elementTitle !== '' && elementTitle !== undefined) {
                if ((value === null) || (value === undefined) || (value.trim () === '')) {
                    element.parent ().addClass ('has-error');
                    if (element.parent ().find ('.help-block').length) {
                        element.parent ().find ('.help-block').html (elementTitle + ' es requerido');
                    } else {
                        element.parent ().parent ().find ('.help-block').html (elementTitle + ' es requerido');
                    }
                    isValidate = false;
                }
            }
        });

		field = form.find ('#dategrouping');
		value = field.val ();
		var fieldGroup = form.find ('#fieldgrouping').val ();
		if (((value === null) || (value === undefined) || (value.trim () === '')) && fieldGroup === '') {
			jQuery ('#gr-dategrouping').html ('Selecciona el período a contar');
			jQuery ('#gr-td-dategrouping').addClass ('has-error');
			isValidate = false;
		}

		if (isInstance) {
			field = form.find ('#applicationcodes');
			value = field.val ();
			if ((value === null) || (value === undefined)) {
				jQuery ('#gr-applicationcodes').html ('La categoría es requerida');
				jQuery ('#gr-td-applicationcodes').addClass ('has-error');
				isValidate = false;
			}
		}

		return isValidate;
	};

	window.GraphUtils = {
        accordionFilters:		   accordionFilters,
		addFilterGroup:            addFilterGroup,
        addFieldGroup:             addFieldGroup,
        customSearch:              customSearch,
		closeGraphPreview:         closeGraphPreview,
		deleteGraph:               deleteGraph,
        setFavorite:			   setFavorite,
		setFilterRow:              setFilterRow,
        setFieldGrid:              setFieldGrid,
        setFieldOperation:         setFieldOperation,
        setGraphicProperties:      setGraphicProperties,
        setGraphicType:            setGraphicType,
		eraseFilterGroup:          eraseFilterGroup,
		eraseFilterValue:          eraseFilterValue,
		eraseFilterRow:            eraseFilterRow,
        eraseModuleRow:            eraseModuleRow,
		filterApplications:        filterApplications,
		getGraphicalColumns:       getGraphicalColumns,
		getNumericColumns:         getNumericColumns,
		getTypoOfOperation:        getTypoOfOperation,
		loadBasicGraph:            loadBasicGraph,
		loadBoxScoreSimpleGraph:   loadBoxScoreSimpleGraph,
		loadFunnelGraph:           loadFunnelGraph,
		openGraphPreview:          openGraphPreview,
		selectCategory:            selectCategory,
        setFieldGrouping:          setFieldGrouping,
        setGroupingBy:              setGroupingBy,
		setSecondField:            setSecondField,
		setDateGroupingVisibility: setDateGroupingVisibility,
		setHelpToField:            setHelpToField,
		searchGraphicsByTime:      searchGraphicsByTime,
        searchGraphicsHome:        searchGraphicsHome,
		setFilterOperators:        setFilterOperators,
        setTab:                    setTab,
        submitSearch:              submitSearch,
		validateGraphForm:         validateGraphForm
	};

	var onTabShownHandler = function () {
		var i, n;
		n = graphs.length;
		for (i = 0; i < n; i += 1) {
			if (typeof graphs [ i ].redraw === 'function') {
				graphs [ i ].redraw ();
			}
		}
	};

	var onDocumentReadyHandler = function () {
        jQuery('#dategrouping option').each(function() {
            if (jQuery (this).val () !== '') {
                temporalData.push({
                    'value': jQuery (this).val (),
                    'label': jQuery (this).text()
                })
            }
        });
        var modules    = [];
		jQuery('#graph-module-column li').each(function () {
			var thisModule = jQuery(this).find('.wmodule').find ('option:selected').val();
			if ((thisModule !== undefined) && (thisModule !== 'undefined')) {
                if (jQuery.inArray (thisModule, modules) !== -1) {
                    jQuery(this).find('button').eq(0).attr('disabled',true);
                } else {
                    modules.push(thisModule)
				}
			}
        });
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
            fLabels[ 'in' ] = 'es nulo';
            fLabels[ 'inn' ] = 'no es nulo';
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
            hfLabels[ 'in' ] = 'NULL';
            hfLabels[ 'inn' ] = 'NOT NULL';
		}

		typeofdata[ 'V' ] = [ 'e', 'n', 's', 'ew', 'c', 'k', 'in', 'inn'];
		typeofdata[ 'N' ] = [ 'e', 'n', 'l', 'g', 'm', 'h', 'in', 'inn'];
		typeofdata[ 'T' ] = [ 'e', 'n', 'l', 'g', 'm', 'h', 'bw', 'b', 'a', 'in', 'inn'];
		typeofdata[ 'I' ] = [ 'e', 'n', 'l', 'g', 'm', 'h', 'in', 'inn'];
		typeofdata[ 'C' ] = [ 'e', 'n', 'in', 'inn'];
		typeofdata[ 'D' ] = [ 'e', 'n', 'l', 'g', 'm', 'h', 'bw', 'b', 'a', 'in', 'inn'];
		typeofdata[ 'DT' ] = [ 'e', 'n', 'l', 'g', 'm', 'h', 'bw', 'b', 'a', 'in', 'inn'];
		typeofdata[ 'NN' ] = [ 'e', 'n', 'l', 'g', 'm', 'h', 'in', 'inn'];
		typeofdata[ 'E' ] = [ 'e', 'n', 's', 'ew', 'c', 'k', 'in', 'inn'];
		searchRow = jQuery ('#graphicsSearch');

		if (searchRow.find ('#graphicsPeriod').val() === 'CUSTOM_DATE') {
            searchRow.find ('#graphicsDatefrom').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
            searchRow.find ('#graphicsDateTo').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
        }
		jQuery ('.nav-tabs a').on ('shown.bs.tab', onTabShownHandler);

        Array.prototype.contains = function(v) {
            for (var i = 0; i < this.length; i++) {
                if (this[i] === v) return true;
            }
            return false;
        };

        Array.prototype.unique = function() {
            var arr = [];
            for (var i = 0; i < this.length; i++) {
                if (!arr.contains(this[i])) {
                    arr.push(this[i]);
                }
            }
            return arr;
        }

    };
	jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));
