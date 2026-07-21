(function (jQuery) {
    // Private variables
	var checkPerformanceInstance;
	var checkReportInstance;
	var loading = '<div style="width: 100%;text-align: center;margin-top: 20px;"><img src="themes/images/loading.gif" alt="loading" /></div>';
	var dataTable, dataGraph;

	//private methods
	// private method
	var drawChart = function () {
		var data = google.visualization.arrayToDataTable(dataGraph);
		var options = {
			title : 'Ejecución vs %Progreso',
			vAxis: {title: '%'},
			hAxis: {title: 'Objetos(Hrs)'},
			legend: { position: "bottom" },
			seriesType: 'bars',
			series: {0: {color:'green'},1: {type: 'area', color:'#d9edf7'},2: {type: 'line', color: 'red'}},
		};
		var chartTask = new google.visualization.ComboChart(document.getElementById('columnchart_values'));
		chartTask.draw (data, options);


	};

	var destroyModal = function () {
		if (modal === null) {
				return;
		}
		jQuery (this).remove ();
		modal = null;
		modalTriggerButton = null;
	};

	var setColorPicker = function (field) {
		var icon = jQuery('#iconPath'),
			indexColor = jQuery('#index_color');
		field.ColorPicker ({onSubmit: function (hsb, hex, rgb, el) {
			jQuery (el)
				.css ({ backgroundColor: '#' + hex, color: '#' + hex })
				.val ('#' + hex)
				.ColorPickerHide ();
				icon.css ({ backgroundColor: '#' + hex, color: '#' + hex });
				indexColor.val (hex);
				},
				onBeforeShow: function () {
					jQuery (this).ColorPickerSetColor (this.value);
				}
			}).bind (
				'keyup',
				function () {
					jQuery (this).ColorPickerSetColor (this.value);
				}
			);
	};

	var loadCkEditor = function (inputId, additionalOptions) {
		var options = {
			contentsCss:   [ 'themes/centaurus/css/bootstrap/bootstrap.min.css' ],
			entities:      false,
			language:      'es',
			removePlugins: 'elementspath'
		};
		jQuery.extend (options, additionalOptions);
		if (CKEDITOR.instances[ inputId ]) {
			return CKEDITOR.instances[ inputId ].setData (jQuery ('#' + inputId).val ());
		} else {
			return CKEDITOR.replace (inputId, options);
		}
	};

	var loadContentEditor = function (inputField) {
		return loadCkEditor (
			inputField,
			{
				toolbar: [
					[ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'Subscript', 'Superscript' ],
					[ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent' ],
					[ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ],
					[ 'Link', 'Unlink', 'Anchor', '-', 'Undo', 'Redo', '-', 'Find', 'Replace', '-', 'SelectAll', 'RemoveFormat', '-', 'Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak', 'TextColor', 'BGColor' ],
					'/',
					[ 'Styles', 'Format', 'Font', 'FontSize' ]
    				]
    			}
    		);
	};

	var validateForm = function (objForm) {
		var formElement    = jQuery("form[name='" + objForm.attr ('name') +"'] :input"),
			isValidate     = true,
			selectedFields = [],
			field, operationValue, value;
	        formElement.map(function (index, elm) {
	            var element = jQuery(elm),
	                elementTitle = element.attr('title'),
	                elementName  = element.attr ('name'),
	                value = element.val();

	            if ((jQuery.inArray(elm.type, ['hidden', 'button', 'submit',  'checkbox', 'undefined']) === -1) && elementTitle !== '' && elementTitle !== undefined) {
					if (elm.type === 'select-multiple') {
						if ((value === null || value === undefined)) {
							element.parent().addClass('has-error');
							if (element.parent().find('.help-block').length) {
								element.parent().find('.help-block').html(elementTitle + ' requerido');
							} else {
								element.parent().parent().find('.help-block').html(elementTitle + ' requerido');
							}
							isValidate = false;
						}
					} else {
						if ((value === null) || (value === undefined) || (value.trim() === '')) {
							element.parent().addClass('has-error');
							if (element.parent().find('.help-block').length) {
								element.parent().find('.help-block').html(elementTitle + ' requerido');
							} else {
								element.parent().parent().find('.help-block').html(elementTitle + ' requerido');
							}
							isValidate = false;
						} else if (elementName === 'fields[]') {
							if (jQuery.inArray(value, selectedFields) !== -1) {
								element.parent().addClass('has-error');
								element.parent().parent().find('.help-block').html('Campo duplicado');
								isValidate = false;
							} else {
								selectedFields.push(value);
							}
						}
					}

	            }
	        });

	        return isValidate;
	    };

	var validateWeeklyReport = function (form, label) {
		var record    = form.find ('input[name="record"]').val(),
			info      = '¿Estás seguro de compartir el reporte: "' + label + '"?',
			isValid   = false,
			arguments = {
				'module':   'report_rails',
				'action':   'AjaxRailsUtils',
				'function': 'CHECK_SHARE_REPORT',
				'record':   record,
				'Ajax':     'true'
			};
		jQuery.ajax({
			  type: "POST",
			  url: "index.php",
			  data: arguments,
			  async: false,
			  success: function(data) {
				  try {
					  var message = JSON.parse (JSON.stringify (data));
					  if(message.error !== 'OK') {
						  throw message.error;
					  } else {
						  info += '\n' + message.html;
						  isValid =  confirm (info)
					  }
				  } catch (e) {
					  alert(e);
					  return false;
				  }
			  }
		});
		return isValid;
	}

	// Public methods
	var createMasterReport = function (id) {
		var action     = jQuery ('input[name="report_action-' + id +'"]:checked').val(),
			agent      = jQuery ('#report_agent-' + id),
			form       = jQuery ('#master-report-form-' + id),
			instance   = jQuery ('#report_instance-' + id),
			isValidate = true,
			report     = jQuery ('#master_report-' + id),
			title      = jQuery ('#report_title-' + id),
			week       = jQuery ('#report_week-' + id);

		jQuery ('span[id ^= mr-]').html('');
		jQuery ('div[id ^= mr-div-]').removeClass('has-error');
		if ((agent.val () === null) || (agent.val () === undefined) || (agent.val ().trim() === '')) {
			agent.parent().addClass('has-error');
			if (agent.parent().find('.help-block').length) {
				agent.parent().find('.help-block').html('¡Seleccionar un agente!');
			}
			isValidate = false;
		}
		if ((instance.val () === null) || (instance.val () === undefined) || (instance.val ().trim() === '')) {
			instance.parent().addClass('has-error');
			if (instance.parent().find('.help-block').length) {
				instance.parent().find('.help-block').html('Debe seleccionar una instancia');
			}
			isValidate = false;
		}
		if ((week.val () === null) || (week.val () === undefined) || (week.val ().trim() === '')) {
			week.parent().addClass('has-error');
			if (week.parent().find('.help-block').length) {
				week.parent().find('.help-block').html('¡Seleccionar una semana!');
			}
			isValidate = false;
		}
		if ((title.val () === null) || (title.val () === undefined) || (title.val ().trim() === '')) {
			title.parent().addClass('has-error');
			if (title.parent().find('.help-block').length) {
				title.parent().find('.help-block').html('¡El título es requerido!');
			}
			isValidate = false;
		}

		if (action === 'DUPLICATE_MASTER_REPORT') {
			if ((report.val () === null) || (report.val () === undefined) || (report.val ().trim() === '')) {
				report.parent().addClass('has-error');
				if (report.parent().find('.help-block').length) {
					report.parent().find('.help-block').html('Seleccionar un informe patrón');
				}
				isValidate = false;
			}
		}
		if (isValidate) {
			form.submit ();
		}
		return false;
	}

	var fetchWeeklyReport = function (obj, id) {
		var btn  	      = jQuery (obj),
			form          = jQuery ('#weekly-status_form-' + id),
			agent         = jQuery ('#report_agent-' + id),
			instance      = jQuery ('#report_instance-' + id),
			isInstance    = jQuery ('#is_instance' + id).val (),
			send          = true,
			SummaryId     = jQuery ("#" + jQuery ('#tab_summary-' + id).val()),
			performanceId = jQuery ("#" + jQuery ('#tab_performance-' + id).val()),
			loading       = '<i class="fa fa-spinner fa-spin fa-fw"></i>',
			search        = '<i class="fa fa-search" aria-hidden="true"></i>',
			week          = jQuery ('#report_week-' + id);

		agent.parent().find ('.help-block').html('');
		instance.parent().find ('.help-block');
		week.parent().find ('.help-block').html('');

		if (((agent.val () === null) || (agent.val () === undefined) || (agent.val ().trim() === '') ) && (isInstance === 'no')) {
			agent.parent().addClass('has-error');
			if (agent.parent().find('.help-block').length) {
				agent.parent().find('.help-block').html('¡Seleccionar un agente!');
				send = false;
			}
		}
		if (((instance.val () === null) || (instance.val () === undefined) || (instance.val ().trim() === '')) && (isInstance === 'no')) {
			instance.parent().addClass('has-error');
			if (instance.parent().find('.help-block').length) {
				instance.parent().find('.help-block').html('Debe seleccionar una instancia');
				send = false;
			}
		}
		if ((week.val () === null) || (week.val () === undefined) || (week.val ().trim() === '')) {
			week.parent().addClass('has-error');
			if (week.parent().find('.help-block').length) {
				week.parent().find('.help-block').html('¡Seleccionar una semana!');
				send = false;
			}
		}
		if (!send) {
			return false;
		}
		btn.html (loading);
		jQuery.post('index.php', form.serialize(), function (data) {
			try {
				var message = JSON.parse (JSON.stringify (data));
				if(message.error !== 'OK') {
					throw message.error;
				} else {
					btn.html (search);
					SummaryId.empty();
					SummaryId.html (message.summary);
					performanceId.empty()
					performanceId.html(message.performance)
				}
			} catch (e) {
				btn.html (search);
				alert("✅ " + e);
			}
		}).fail(function(xhr, status, error) {
			alert ("✅ Ha ocurrido un error: " + error);
		});
	}

	var getBoxScoreReport = function (obj, id) {
		var btn          = jQuery (obj),
			isLoaded     = btn.attr ('data-loading'),
			moduleName   = btn.attr ('data-module'),
			periodTime   = btn.attr ('data-period'),
			reportId     = btn.attr ('rel'),
			tabContent   = jQuery ('#BOX_SCORE-' + id),
			targetAction = (moduleName === 'report_rails') ? 'AjaxRailsUtils' : 'AjaxHomeUtils',
			arguments  = {
				'module':    moduleName,
				'action':    targetAction,
				'function':  'GET_BOXSCORE_REPORT',
				'report_id': reportId,
				'period':    periodTime,
				'record':    id,
				'rand_id':   id,
				'Ajax':      'true'
		};
		if (isLoaded === 'TRUE') {
			return false;
		}
		btn.attr ('data-loading', 'TRUE');
		jQuery.post ('index.php', arguments, function (data) {
			try {
				var message = JSON.parse (JSON.stringify (data));
				if(message.error !== 'OK') {
					throw message.error;
				} else {
					tabContent.empty();
					tabContent.html (message.html);
				}
			} catch (e) {
				alert(e);
			}
		});
	}

	var getWeeklyReport = function (obj, id) {
		var btn        = jQuery (obj),
			isLoaded   = btn.attr ('data-loading'),
			reportId   = btn.attr ('rel'),
			tabContent = jQuery ('#PLANNING_COMPLIANCE-' + id),
			arguments  = {
				'module':    'report_rails',
				'action':    'AjaxRailsUtils',
				'function':  'GET_WEEKLY_REPORT',
				'report_id': reportId,
				'rand_id':   id,
				'Ajax':      'true'
		};
		if (isLoaded === 'TRUE') {
			return false;
		}
		btn.attr ('data-loading', 'TRUE');
		jQuery.post('index.php', arguments, function (data) {
			try {
				var message = JSON.parse (JSON.stringify (data));
				if(message.error !== 'OK') {
					throw message.error;
				} else {
					tabContent.empty();
					tabContent.html (message.html);
				}
			} catch (e) {
				alert(e);
			}
		});
	}

	var getUpcomingReport = function (obj, id) {
		var btn        = jQuery (obj),
			isLoaded   = btn.attr ('data-loading'),
			reportId   = btn.attr ('rel'),
			tabContent = jQuery ('#NEXT_WEEK-' + id),
			arguments  = {
			'module':    'report_rails',
				'action':    'AjaxRailsUtils',
				'function':  'GET_UPCOMING_ACTIVITIES',
				'report_id': reportId,
				'rand_id':   id,
				'Ajax':      'true'
		};
		if (isLoaded === 'TRUE') {
			return false;
		}
		btn.attr ('data-loading', 'TRUE');
		jQuery.post ('index.php', arguments, function (data) {
			try {
				var message = JSON.parse (JSON.stringify (data));
				if(message.error !== 'OK') {
					throw message.error;
				} else {
					tabContent.empty();
					tabContent.html (message.html);
				}
			} catch (e) {
				alert(e);
			}
		});
	}

	var initPerformance = function () {
		var field = (jQuery ('.color'));
		ReportRailesUtils.checkPerformanceInstance = loadContentEditor ('performace-content');
		setColorPicker (field);
	}

	var loadGraphics = function (obj, e) {
		var panel        = jQuery (obj),
			control      = panel.attr ('aria-controls'),
			indicators   = JSON.parse (panel.attr('data-script')),
			isLoaded     = panel.attr ('data-graphic'),
			loading      = '<i class="fa fa-spinner fa-spin fa-fw"></i>&nbsp;',
			moduleName   = jQuery ('#flmodule-' + panel.attr ('rel')).val(),
			panelTitle   = panel.html(),
			reportId     = jQuery('#report-id-' + panel.attr ('rel')).val(),
			period       = jQuery('#period-' + panel.attr ('rel')).val(),
			targetAction = (moduleName === 'report_rails') ? 'AjaxRailsUtils' : 'AjaxHomeUtils',
			view,arguments;
		if (indicators.length !== 0 && isLoaded !== 'true') {
			jQuery ('#' + control).fadeToggle('slow');
			e.stopPropagation();
			arguments  = {
				'module':     moduleName,
				'action':     targetAction,
				'function':   'GET_DATA_GRAPHICS',
				'indicators': indicators,
				'report_id':  reportId,
				'period':     period,
				'Ajax':       'true'
			};
			jQuery.post ('index.php', arguments, function (data) {
				try {
					var message = JSON.parse (JSON.stringify (data));
					if(message.error !== 'OK') {
						throw message.error;
					} else {
						indicators.forEach(function(indicator) {
							google.charts.load('current', {'packages':['corechart']});
							google.charts.setOnLoadCallback (
								function () {
									var data = google.visualization.arrayToDataTable(message.html[indicator]);
									var options = {
										title: message.name[indicator],
										curveType: 'function',
										legend: { position: 'bottom' }
									};
									var chart = new google.visualization.LineChart(document.getElementById (indicator));
									chart.draw (data, options);
								}
							);
							console.log(indicator)
							console.log(message.html[indicator]);
							console.log(message.name[indicator]);
						})
						jQuery ('#' + control).fadeToggle ('hide');
					}
				} catch (e) {
					alert(e);
				}
			});
			panel.html (loading + panelTitle);
			view = setTimeout(function () {
				panel.attr ('data-graphic', 'true');
				panel.html (panelTitle);
				clearTimeout (view);
			}, 3000);
		}

	}

	var initAgreement = function () {
		ReportRailesUtils.checkReportInstance = loadContentEditor ('agreement_content');
	}

	var initMasterReportStatus = function () {
		ReportRailesUtils.checkReportInstance = loadContentEditor ('report_content');
	}

	var openCreateMasterModal = function (e, obj) {
		var button        = jQuery (obj),
			rowId         = Math.floor (Math.random() * 500) + 1,
			modalTemplate = jQuery ('#master-report-modal-template'),
			modal = jQuery (modalTemplate.html ().replace (/__ID__/g, rowId));


		modal.modal ({ backdrop: 'static' }).on ('hidden.bs.modal', destroyModal);
		e.preventDefault ();
	}

	var performaceGraphic = function (data) {
		if (data === null || data === undefined || data.length === 0) {
			return false
		}
		var columnColor = [
			'#3366CC',
			'#DC3912',
			'#FF9900',
			'#109618',
			'#990099',
			'#3B3EAC',
			'#0099C6',
			'#DD4477',
			'#66AA00',
			'#B82E2E',
			'#316395',
			'#994499',
			'#22AA99',
			'#AAAA11',
			'#6633CC',
			'#E67300',
			'#8B0707',
			'#329262',
			'#5574A6',
			'#3B3EAC'
		], totalColumns = 0;
		Array.prototype.shuffle = function () {
			var i = this.length, j, temp;
			if ( i === 0 ) return this;
			while ( --i ) {
				j = Math.floor( Math.random() * ( i + 1 ) );
				temp = this[i];
				this[i] = this[j];
				this[j] = temp;
			}
			return this;
		};

		columnColor.shuffle ();
		dataTable    = Object.entries(JSON.parse (JSON.stringify(data)));
		totalColumns = dataTable.length;
		dataTable[0].push ({ role: "style" });

		for (var k = 1;k < totalColumns; k++) {
			dataTable[k].push (columnColor[k]);
		}
		dataGraph = data;
		google.charts.load("current", {packages:["corechart"]});
		google.charts.setOnLoadCallback (drawChart);
	}

	var publishReport = function (obj) {
		var button     = jQuery (obj),
			reportId   = button.attr ('data-report-id'),
			reportType = button.attr ('data-report-type'),
			arguments  = {
				'module':      'report_rails',
				'action':      'AjaxRailsUtils',
				'function':    'PUBLISHED_REPORT',
				'report_id':   reportId,
				'report_type': reportType,
				'Ajax':        'true'
		};
		if (confirm ('¿Estás seguro de publicar el reporte?')) {
			jQuery.post('index.php', arguments, function (data) {
				try {
					var message = JSON.parse (JSON.stringify (data));
					if(message.error !== 'OK') {
						throw message.error;
					} else {
						alert ('Reporte publicado con éxito');
					}
				} catch (e) {
					alert(e);
				}
			});
		}
	}

	var saveAgreement = function (obj, id) {
		var form = jQuery ('#summary-report-agreements-' + id),
					value;
		jQuery ('span[id ^= a-]').html('');
		jQuery ('div[id ^= a-div-]').removeClass('has-error');
		if (validateForm (form)) {
			value = ReportRailesUtils.checkReportInstance.getData();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				alert ('Introduce el contenido del acuerdo');
				return false;
			} else if (value.trim ().length < 60) {
				alert ('La descripción del acuerdo, parece estar vacía o es muy corta! introduce al menos 70 carácteres!');
			} else {
				form.submit ();
			}
		}
	}

	var saveMasterReportStatus = function (obj, id) {
		var button  = jQuery (obj),
			textObj = CKEDITOR.instances[ 'report_content' ],
			form, arguments;
		jQuery ('#report_content').val (trim (textObj.getData ()));
		form      = jQuery ('#summary-report-master_status-' + id);
		arguments = form.serialize ();
		jQuery.post('index.php', arguments, function (data) {
			try {
				var message = JSON.parse (JSON.stringify (data));
				if(message.error !== 'OK') {
					throw message.error;
				} else {
					alert ('El estado del informe se ha guardado correctamente');
				}
			} catch (e) {
				alert(e);
			}
		});
	}

	var savePerformance = function (obj, id) {
		var form = jQuery ('#summary-report-performace-' + id),
			value;
		jQuery ('span[id ^= p-]').html('');
		jQuery ('div[id ^= p-div-]').removeClass('has-error');
		if (validateForm (form)) {
			value = ReportRailesUtils.checkPerformanceInstance.getData();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				alert ('Introduce el contenido del rendimiento');
				return false;
			} else if (value.trim ().length < 60) {
				alert ('La descripción del contenido, parece estar vacía o es muy corta! introduce al menos 70 carácteres!');
			} else {
				form.submit ();
			}
		}
	}

	var selectAgent = function (obj, id) {
		var arguments= {},
			agentId = jQuery (obj).val (),
			instances = jQuery ('#report_instance-' + id);
		if (agentId !== '') {
			instances.empty();
			instances.html ('<option value="" selected>Cargando....</option>')
			arguments = {
				'module':   'report_rails',
				'action':   'AjaxRailsUtils',
				'function': 'GET_INSTANCES',
				'agent':     agentId,
				'Ajax':     'true'
			};
			jQuery.post('index.php', arguments, function (data) {
				try {
					var message = JSON.parse (JSON.stringify (data));
					if(message.error !== 'OK') {
						throw message.error;
					} else {
						instances.empty();
						instances.html (message.html);
					}
				} catch (e) {
					alert(e);
				}
			});
		}
	}

	var selectPerformance = function (obj) {
		var index = jQuery(obj).val(),
			icon = jQuery('#iconPath');
		icon.html('<p>' + index + '</p>');
	}

	var selectedUpcomingReport = function (obj, id) {
		var selectObj  = jQuery (obj).val (),
			tabContent = jQuery ('#NEXT_WEEK-' + id),
			arguments  = {
				'module':    'report_rails',
				'action':    'AjaxRailsUtils',
				'function':  'GET_UPCOMING_ACTIVITIES',
				'report_id': selectObj,
				'rand_id':   id,
				'Ajax':      'true'
		};
		if (selectObj !== '') {
			tabContent.empty ();
			tabContent.html (loading);
			jQuery.post ('index.php', arguments, function (data) {
				try {
					var message = JSON.parse (JSON.stringify (data));
					if(message.error !== 'OK') {
						throw message.error;
					} else {
						tabContent.empty ();
						tabContent.html (message.html);
					}
				} catch (e) {
					alert (e);
				}
			});
		}
	}

	var selectedWeeklyReport = function (obj, id) {
		var selectObj  = jQuery (obj).val (),
			tabContent = jQuery ('#PLANNING_COMPLIANCE-' + id),
			arguments  = {
				'module':    'report_rails',
				'action':    'AjaxRailsUtils',
				'function':  'GET_WEEKLY_REPORT',
				'report_id': selectObj,
				'rand_id':   id,
				'Ajax':      'true'
		};
		if (selectObj !== '') {
			tabContent.empty ();
			tabContent.html (loading);
			jQuery.post ('index.php', arguments, function (data) {
				try {
					var message = JSON.parse (JSON.stringify (data));
					if(message.error !== 'OK') {
						throw message.error;
					} else {
						tabContent.empty ();
						tabContent.html (message.html);
					}
				} catch (e) {
					alert (e);
				}
			});
		}
	}

	var selectTabAgreement = function (obj, id) {
		var tab           = jQuery (obj),
			tabLabel      = tab.find("option:selected").text(),
			divEntity     = jQuery ('#div_entity_' + id),
			entityType    = divEntity.find ('input[name="entity_type"]'),
			entityDisplay = jQuery ('#edit_entity_display'),
			entityTab     = jQuery ('#related_tab-' + id);

		if (tab.val () !== '') {
			entityType.val (tab.val ());
			entityDisplay.val ('');
			entityTab.attr ('data-referenced-module', tab.val ())
			entityTab.attr ('data-title', 'Seleccionar ' + tabLabel);
			entityDisplay.attr ('placeholder','');
		} else {
			entityType.val ('report_rails');
			entityTab.attr('data-referenced-module', '')
			entityTab.attr ('data-title', 'No hay modulo seleccionado');
			entityDisplay.val ('');
			entityDisplay.attr ('placeholder','No hay modulo seleccionado');
		}

	}

	var selectInstance = function (obj, id, module) {
		var arguments= {},
			instanceData = jQuery (obj).val (),
			periods      = jQuery ('#report_week-' + id);
		if (instanceData !== '') {
			periods.empty();
			periods.html ('<option value="" selected>Cargando....</option>')
			if (module === 'Home') {
				arguments = {
					'module':        'Home',
					'action':        'AjaxHomeUtils',
					'function':      'GET_PERIODS',
					'instance_data': instanceData,
					'Ajax':          'true'
				};
			} else {
				arguments = {
					'module':        'report_rails',
					'action':        'AjaxRailsUtils',
					'function':      'GET_PERIODS',
					'instance_data': instanceData,
					'Ajax':          'true'
				};
			}
			jQuery.post('index.php', arguments, function (data) {
				try {
					var message = JSON.parse (JSON.stringify (data));
					if(message.error !== 'OK') {
						throw message.error;
					} else {
						periods.empty();
						periods.html (message.html);
					}
				} catch (e) {
					alert(e);
				}
			});
		}
	}

	var setReportPattern = function (obj, id) {
		var action         = jQuery (obj).val(),
			reportPattern  = jQuery ('#report-pattern-' + id),
			masterReportId = jQuery ('#master-report-' + id);
		if (action == 'DUPLICATE_MASTER_REPORT') {
			reportPattern.removeClass ('hide');
		} else {
			reportPattern.addClass ('hide');
			masterReportId.val ('');
		}
	}

	var shareMasterReport = function (label, id) {
		var form = jQuery('#form_share_report_' + id),
			arguments = form.serialize();

		if (validateWeeklyReport(form, label)) {
			jQuery.post('index.php', arguments, function (data) {
				try {
					var message = JSON.parse (JSON.stringify (data));
					if(message.error !== 'OK') {
						throw message.error;
					} else {
						alert('El reporte se compartido con éxito!');
					}
				} catch (e) {
					alert(e);
				}
			});
		}
	}

	var updateAgreement = function (obj, id) {
		var form = jQuery ('#summary-report-agreements-' + id),
			value;
		jQuery ('#update_istance-' + id).val('yes');
		jQuery ('span[id ^= a-]').html('');
		jQuery ('div[id ^= a-div-]').removeClass('has-error');
		if (validateForm (form)) {
			value = ReportRailesUtils.checkReportInstance.getData();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				alert ('Introduce el contenido del acuerdo');
				return false;
			} else if (value.trim ().length < 60) {
				alert ('La descripción del acuerdo, parece estar vacía o es muy corta! introduce al menos 70 carácteres!');
			} else {
				form.submit ();
			}
		}
	}

    window.ReportRailesUtils = {
		checkPerformanceInstance: checkPerformanceInstance,
		checkReportInstance:      checkReportInstance,
		createMasterReport:       createMasterReport,
		fetchWeeklyReport:        fetchWeeklyReport,
		getWeeklyReport:		  getWeeklyReport,
		getBoxScoreReport:        getBoxScoreReport,
		getUpcomingReport: 		  getUpcomingReport,
		initAgreement:            initAgreement,
		initMasterReportStatus:   initMasterReportStatus,
		initPerformance:          initPerformance,
		loadGraphics:			  loadGraphics,
		openCreateMasterModal:    openCreateMasterModal,
		performaceGraphic:        performaceGraphic,
		publishReport:            publishReport,
		publishUpcomingReport:    publishReport,
		saveAgreement:            saveAgreement,
		saveMasterReportStatus:   saveMasterReportStatus,
		savePerformance:          savePerformance,
		selectAgent:              selectAgent,
		selectPerformance:        selectPerformance,
		selectedUpcomingReport:   selectedUpcomingReport,
		selectedWeeklyReport:     selectedWeeklyReport,
		selectTabAgreement:       selectTabAgreement,
		selectInstance:           selectInstance,
		setReportPattern:         setReportPattern,
		shareMasterReport:        shareMasterReport,
		updateAgreement:		  updateAgreement
	};

	jQuery (document).ready (function () {
		/*var field = (jQuery ('.color'));
		ReportRailesUtils.checkPerformanceInstance = loadContentEditor ();
		console.log(ReportRailesUtils.checkPerformanceInstance)
		setColorPicker (field); */
		jQuery ('.date').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
		jQuery ('.time').timepicker ({ minuteStep: 5, showMeridian: false, disableFocus: false, showWidget: true });
	});
}(jQuery));