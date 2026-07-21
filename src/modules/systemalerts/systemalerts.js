var typeofdata = new Array ();
typeofdata[ 'V' ] = [ 'e', 'n', 's', 'ew', 'c', 'k' ];
typeofdata[ 'N' ] = [ 'e', 'n', 'l', 'g', 'm', 'h' ];
typeofdata[ 'T' ] = [ 'e', 'n', 'l', 'g', 'm', 'h', 'b', 'a' ];
typeofdata[ 'I' ] = [ 'e', 'n', 'l', 'g', 'm', 'h' ];
typeofdata[ 'C' ] = [ 'e', 'n' ];
typeofdata[ 'D' ] = [ 'e', 'n', 'l', 'g', 'm', 'h', 'b', 'a' ];
typeofdata[ 'DT' ] = [ 'e', 'n', 'l', 'g', 'm', 'h', 'b', 'a' ];
typeofdata[ 'NN' ] = [ 'e', 'n', 'l', 'g', 'm', 'h' ];
typeofdata[ 'E' ] = [ 'e', 'n', 's', 'ew', 'c', 'k' ];

var fLabels = new Array ();
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

function loadAlerts (element) {
	var d = '';
	var m = '';
	var viewSelect = jQuery ('#dinamicViewScale').val ();
	var date = new Date ();
	var first = new Date (date.getFullYear (), date.getMonth (), 1);
	var last = new Date (date.getFullYear (), date.getMonth () + 1, 0);
	var btnGroup = jQuery (element).parent();

	if (first.getDate () < 10) {
		d = '0' + first.getDate ();
	} else {
		d = first.getDate ();
	}
	if ((first.getMonth () + 1) < 10) {
		m = '0' + (first.getMonth () + 1);
	} else {
		m = (first.getMonth () + 1);
	}
	var from = first.getFullYear () + "-" + m + "-" + d;
	if (last.getDate () < 10) {
		d = '0' + last.getDate ();
	} else {
		d = last.getDate ();
	}
	if ((last.getMonth () + 1) < 10) {
		m = '0' + (last.getMonth () + 1);
	} else {
		m = (last.getMonth () + 1);
	}
	var to = last.getFullYear () + "-" + m + "-" + d;

	if (viewSelect == '' && jQuery ('#viewPeriod').val () == '') {
		viewSelect = 'Month';
	} else {
		if (jQuery ('#newblock').val () === 'reload') {
			viewSelect = jQuery ('#viewPeriod').val ();
			from = jQuery ('#date_from').val ();
			to = jQuery ('#date_to').val ();
		} else {
			viewSelect = 'Month';
		}
	}

	if (jQuery ('#newblock').val () === 'reload' || (element.className.indexOf ('active') == -1)) {
		jQuery ('div .loadAlertstabs').html ('');
		var code_aplication = element.id;
		code_aplication = code_aplication.split ('--')[ 1 ];

		var param = 'app=' + code_aplication + '&date_from=' + from + '&date_to=' + to + '&viewPeriod=' + viewSelect;
		var url = '';

		if (code_aplication == 'all') {
			url = 'action=systemalertsAjax&module=systemalerts&file=DetailViewAllAlerts&ajax=true&' + param;
		} else {
			url = 'action=systemalertsAjax&module=systemalerts&file=DetailViewAlerts&ajax=true&' + param;
		}
		btnGroup.find('a').each(function (i, item) {
			 if(jQuery(item).attr('id') === jQuery(element).attr('id')) {
                 jQuery(item).removeClass('btn-default');
                 jQuery(item).addClass('btn-primary');
			 } else {
                 jQuery(item).addClass('btn-default');
                 jQuery(item).removeClass('btn-primary');
			 }
        });
		new Ajax.Request (
			'index.php',
			{
				queue:      { position: 'end', scope: 'command' },
				method:     'post',
				postBody:   url,
				onComplete: function (response) {
					jQuery ('#tab-' + code_aplication).html ('');
					jQuery ('#tab-' + code_aplication).html (response.responseText);

					jQuery ('#newblock').val ('');
					jQuery ('#dinamicViewScale').val ('');

					var view = jQuery ('#viewScale');
					if (view.val () == '') {
						view.val ('Month');
					}

					jQuery ('#date_from').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
					jQuery ('#date_to').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
				}
			}
		);
	}

}

function callSearch () {
	if (validateSearch ()) {
		var appcode = jQuery ('#app').val ();
		var view = jQuery ('#viewPeriod').val ();
		jQuery ('#newblock').val ('reload');
		jQuery ('#dinamicViewScale').val (view);
		var obj = jQuery ('#li--' + appcode);
		obj.click ();
	} else {
		return false;
	}

}

function validateSearch () {
	var from = jQuery ('#date_from').val ().split ('-');
	var to = jQuery ('#date_to').val ().split ('-');

	var dateStart = new Date (from[ 0 ], (from[ 1 ] - 1), from[ 2 ]);
	var dateEnd = new Date (to[ 0 ], (to[ 1 ] - 1), to[ 2 ]);

	if (dateStart > dateEnd) {
		alert (alert_arr.INVALID_DATES_SEARCH);
		return false;
	}
	return true;
}

function validateAddAlert () {
	if (jQuery ('#appAlertNew').is (":visible") && jQuery ('#codeApp').val () == '') {
		alert (alert_arr.SELECT_ALERT_TYPE);
		jQuery ('#codeApp').focus ();
		return false;
	}
	if (jQuery ('#titleAlert').val () == '') {
		alert (alert_arr.SELECT_ALERT_TITLE);
		jQuery ('#titleAlert').focus ();
		return false;
	}
	if (jQuery ('#codetype').val () == '') {
		alert (alert_arr.SELECT_ALERT_TYPEALERT);
		jQuery ('#codetype').focus ();
		return false;
	}
	if (jQuery ('#periodAlert').is (":visible") && jQuery ('#scale').val () == '') {
		alert (alert_arr.SELECT_ALERT_SCALE);
		jQuery ('#scale').focus ();
		return false;
	}
	if (jQuery ('#codeElement').is (":visible") && jQuery ('#codeElement').val () == '') {
		alert (alert_arr.SELECT_ALERT_ELEMENT);
		jQuery ('#codeElement').focus ();
		return false;
	}
	if (jQuery ('#appAlertElementField').is (":visible") && jQuery ('#codeElementField').val () == '') {
		alert (alert_arr.SELECT_ALERT_FIELD);
		jQuery ('#codeElementField').focus ();
		return false;
	}
	if (jQuery ('#codeElementOperator').val () == '') {
		alert (alert_arr.SELECT_ALERT_OPERATOR);
		jQuery ('#codeElementOperator').focus ();
		return false;
	}
	if (jQuery ('#codeElementValue').val () == '') {
		alert (alert_arr.SELECT_ALERT_VALUE);
		jQuery ('#codeElementValue').focus ();
		return false;
	}
	return true;
}

function saveAlerts () {
	if (validateAddAlert ()) {
		var appcode     = jQuery ('#app').val ();
		var codeElement = jQuery ('#codeElement');
		var codeApp     = '';
		if (jQuery ('#codeApp').length > 0 && jQuery ('#codeApp').val () != '') {
			codeApp = jQuery ('#codeApp').val ();
		} else {
			codeApp = appcode;
		}

		var titleAlert          = jQuery ('#titleAlert').val ();
		var codetype            = jQuery ('#codetype').val ();
		var codeElementSel      = jQuery ('#codeElement').val ();
		var codeElementOperator = jQuery ('#codeElementOperator').val ();
		var codeElementValue    = jQuery ('#codeElementValue').val ();
		var mode                = jQuery ('#mode').val ();
		var systemAlertId       = jQuery ('#systemAlertId').val ();
		var scale = jQuery ('#scale').val ();

		var param = '&mode=' + mode + '&codeElement=' + codeElement + '&codeApp=' + codeApp + '&titleAlert=' + titleAlert + '&codetype=' + codetype + '&codeElement=' + codeElementSel + '&codeElementOperator=' + codeElementOperator + '&codeElementValue=' + codeElementValue + '&systemAlertId=' + systemAlertId;

		if (codetype == 'Indicators') {
			var boxscoreid = jQuery ('option:selected', codeElement).attr ('boxscoreid');
			var datarel = jQuery ('option:selected', codeElement).attr ('datarel');
			scale = jQuery ('option:selected', codeElement).attr ('scale');
			var bxdatarel = jQuery ('option:selected', codeElement).attr ('bxdatarel');
			var scaledatarel = jQuery ('option:selected', codeElement).attr ('scaledatarel');
			var systemAlertIdRel = jQuery ('#systemAlertIdRel').val ();

			param += '&systemAlertIdRel=' + systemAlertIdRel + '&boxscoreid=' + boxscoreid + '&datarel=' + datarel + '&scale=' + scale + '&bxdatarel=' + bxdatarel + '&scaledatarel=' + scaledatarel;
		} else if (codetype == 'Task_object_no_cump') {
			var fieldElement = jQuery ('#codeElementField');
			var field = jQuery ('#codeElementField').val ();
			var fieldName = jQuery ('option:selected', fieldElement).html ();
			var tabLabel = jQuery ('option:selected', codeElement).attr ('tablabel');
			var tabName = jQuery ('option:selected', codeElement).attr ('tabname');
			param += '&scale=' + scale + '&field=' + field + '&elementLabel=' + tabLabel + '&elementName=' + tabName + '&fieldName=' + fieldName;
		} else {
			param += '&scale=' + scale;
		}

		new Ajax.Request (
			'index.php',
			{
				queue:      { position: 'end', scope: 'command' },
				method:     'post',
				postBody:   'module=systemalerts&action=systemalertsAjax&file=SaveAlert' + param,
				onComplete: function (response) {
					console.log (response);
					if (response.responseText == 'success') {
						if (mode == 'create') {
							alert (alert_arr.SAVE_ALERT);
						} else {
							alert (alert_arr.SAVE_EDIT_ALERT);
						}
						jQuery ('#createAlert').removeClass ('md-show');
						jQuery ('#createAlert').html ('');
						jQuery ('.md-overlay').css ({ opacity: 0.0, visibility: 'hidden' });
						jQuery ('#newblock').val ('reload');
						jQuery ('#dinamicViewScale').val (jQuery ('#viewPeriod').val ());
						var obj = jQuery ('#li--' + appcode);
						obj.click ();
					} else {
						alert (alert_arr.ERROR);
					}
				}
			}
		);
	} else {
		return false;
	}
}

function selectAlertType (mode, indicatorId, tabid, operator) {
	var appSelect = '';
	if (jQuery ('#codeApp').length > 0 && jQuery ('#codeApp').val () != '') {
		appSelect = jQuery ('#codeApp').val ();
	} else if (jQuery ('#app').val () != 'all') {
		appSelect = jQuery ('#app').val ();
	}
	var type = jQuery ('#codetype').val ();
	var viewPeriod = jQuery ('#viewPeriod').val ();

	jQuery ('#appAlertElementField').hide ();
	jQuery ('#codeElementField').html ('<option value="">' + alert_arr.LBL_SELECT_ALERT + '</option>');

	if (appSelect != '') {
		new Ajax.Request (
			'index.php',
			{
				queue:      { position: 'end', scope: 'command' },
				method:     'post',
				postBody:   'module=systemalerts&action=systemalertsAjax&file=LoadElementsAlerts&function=paramFieldElements&appSelect=' + appSelect + '&type=' + type + '&viewPeriod=' + viewPeriod,
				onComplete: function (response) {
					var data = JSON.parse (response.responseText);
					console.log(data);
					var htmlElement = '<option value="">' + alert_arr.LBL_SELECT_ALERT + '</option>';
					var htmlElementField = '<option value="">' + alert_arr.LBL_SELECT_ALERT + '</option>';
					var selected = '';
					for (var j = 0; j < data.length; j++) {
						if (type == 'Indicators') {
							htmlElement += '<option value="' + data[ j ].box_score_dataid + '" boxscoreid="' + data[ j ].boxscoreid + '"' + ' datarel="' + data[ j ].datarel + '" scale="' + data[ j ].scale + '" ' + ' bxdatarel="' + data[ j ].bxdatarel + '" scaledatarel="' + data[ j ].scaledatarel + '"';
							if (mode == 'edit' && data[ j ].box_score_dataid == indicatorId) {
								htmlElement += ' selected="selected">' + data[ j ].box_score + '</option>';
							} else {
								htmlElement += '>' + data[ j ].box_score + '</option>';
							}
						} else if (type == 'Task_object_no_cump') {
							htmlElement += '<option value="' + data[ j ].tabid + '" tabname="' + data[ j ].name + '" tablabel="' + data[ j ].tablabel + '"';
							if (mode == 'edit' && data[ j ].tabid == tabid) {
								htmlElement += ' selected="selected" >' + data[ j ].tablabel + '</option>';
							} else {
								htmlElement += '>' + data[ j ].tablabel + '</option>';
							}

							jQuery ('#codeElementOperator').html ('');
							jQuery ('#codeElementOperator').html (htmlElementField);
							jQuery ('#periodAlert').show ();
						} else {
							jQuery ('#periodAlert').show ();
							jQuery ('#appAlertElement').hide ();
						}
					}
					if (type == 'Indicators') {
						jQuery ('#periodAlert').hide ();
						htmlElementField += '<option value="less-equal"';
						if (mode == 'edit' && operator == 'less-equal') {
							htmlElementField += ' selected="selected" ><=</option>';
						} else {
							htmlElementField += '><=</option>';
						}
						htmlElementField += '<option value="greater-equal"';
						if ((mode == 'edit' && operator == 'greater-equal')) {
							htmlElementField += ' selected="selected" >>=</option>';
						} else {
							htmlElementField += '>>=</option>';
						}
						jQuery ('#codeElementOperator').html ('');
						jQuery ('#codeElementOperator').html (htmlElementField);
					}
					jQuery ('#codeElement').html (htmlElement);
				}
			}
		);
	} else {
		alert (alert_arr.SELECT_ALERT_TYPE);
		jQuery ('#codeApp').val ('');
		jQuery ('#codetype').val ('');
		jQuery ('#codeElement').val ('');
		jQuery ('#periodAlert').hide ();
		jQuery ('#codeApp').focus ();
	}
}

function selectAlertElement (element, access, mode, elementField, valoperator, tabName) {
	var type = jQuery ('#codetype').val ();
	var tab = '';
	if (access == '1') {
		tab = tabName
	} else {
		tab = jQuery ('option:selected', element).attr ('tabname');
	}
	if (type != 'Indicators') {
		new Ajax.Request (
			'index.php',
			{
				queue:      { position: 'end', scope: 'command' },
				method:     'post',
				postBody:   'module=systemalerts&action=systemalertsAjax&file=LoadElementsAlerts&function=codeElementField&tabid=' + jQuery ('#codeElement').val () + '&tabname=' + tab,
				onComplete: function (response) {
					console.log (response);
					var data = JSON.parse (response.responseText);
					var htmlElement = '<option value="">' + alert_arr.LBL_SELECT_ALERT + '</option>';
					for (var j = 0; j < data.length; j++) {
						htmlElement += '<option value="' + data[ j ].value + '"';
						if (mode == 'edit' && data[ j ].value == elementField) {
							htmlElement += ' selected="selected" >' + data[ j ].text + '</option>';
						} else {
							htmlElement += '>' + data[ j ].text + '</option>';
						}
					}
					jQuery ('#appAlertElementField').show ();
					jQuery ('#codeElementField').html (htmlElement);
					selectElementField (jQuery ('#codeElementField'), '1', mode, elementField, valoperator);
				}
			}
		);
	}
}

function selectElementField (element, access, mode, valField, valoperator) {
	var htmlElement = '<option value="">' + alert_arr.LBL_SELECT_ALERT + '</option>';
	var typeElement = '';
	if (access == '1') {
		typeElement = trimfValues (valField);
	} else {
		typeElement = trimfValues (element.value);
	}

	var label = '';
	var ops = typeofdata[ typeElement ];
	for (var i = 0; i < ops.length; i++) {
		label = fLabels[ ops[ i ] ];
		if (label == null) {
			continue;
		}
		htmlElement += '<option value="' + ops[ i ] + '"';
		if (mode == 'edit' && ops[ i ] == valoperator) {
			htmlElement += ' selected="selected" >' + label + '</option>';
		} else {
			htmlElement += '>' + label + '</option>';
		}
	}
	jQuery ('#codeElementOperator').html ('');
	jQuery ('#codeElementOperator').html (htmlElement);

	if(typeElement == 'D' || typeElement == 'DT'){
		jQuery ('#codeElementValue').addClass ('input-readonly');
		jQuery ('#codeElementValue').prop('readonly', true);
		jQuery ('#codeElementValue').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
	}else{
		jQuery ('#codeElementValue').val('');
		jQuery ('#codeElementValue').removeClass ('input-readonly');
		jQuery ('#codeElementValue').prop('readonly', false);
	}

}

function indicatorOccurrence (systemAlertId, viewSearch, app, sourceAlert) {
	var from = jQuery ('#date_from').val ();
	var to = jQuery ('#date_to').val ();
	jQuery ('.md-overlay').css ({ opacity: 1, visibility: 'visible' });
	var url = 'module=systemalerts&action=systemalertsAjax&file=ViewIndicatorsAlerts&viewScale=' + viewSearch + '&app=' + app + '&record=' + systemAlertId + '&from=' + from + '&to=' + to + '&sourceAlert=' + sourceAlert;

	new Ajax.Request (
		'index.php',
		{
			queue:      { position: 'end', scope: 'command' },
			method:     'post',
			postBody:   url,
			onComplete: function (response) {
				jQuery ('#viewIndicators').html (response.responseText);
				jQuery ('#viewIndicators').addClass ('md-show');
			}
		}
	);
}

function deleteAlert (systemAlertId, indicatorAlertId) {
	if (!confirm (alert_arr.MESS_DELETE_ALERT)) {
		return false;
	}
	jQuery.ajax (
		{
			type: 'POST',
			url:  'index.php',
			data: { module: 'systemalerts', action: 'systemalertsAjax', file: 'DeleteAlert', record: systemAlertId, 'delete': 'true', 'indicatorRecord': indicatorAlertId }
		}
	).done (
		function (response) {
			jQuery ('#row-' + systemAlertId).fadeOut (
				function () {
					jQuery ('#row-' + systemAlertId).remove ();
				}
			);
		}
	);
}

function callAddAlertsIndicators (mode, codeType, systemAlertId) {
	jQuery ('.md-overlay').css ({ opacity: 1, visibility: 'visible' });
	var view = jQuery ('#viewPeriod').val ();
	var app = jQuery ('#app').val ();
	var url = 'module=systemalerts&action=systemalertsAjax&ajax=true&file=CreateAlertIndicator&app=' + app + '&viewPeriod=' + view + '&mode=' + mode;
	if (mode == 'edit') {
		url += '&codeType=' + codeType + '&systemAlertId=' + systemAlertId;
	}
	new Ajax.Request (
		'index.php',
		{
			queue:      { position: 'end', scope: 'command' },
			method:     'post',
			postBody:   url,
			onComplete: function (response) {
				jQuery ('#createAlert').addClass ('md-show');
				jQuery ('#createAlert').html (response.responseText);
			}
		}
	);
}

function trimfValues (value) {
	var string_array;
	string_array = value.split (":");
	return string_array[ 4 ];
}

