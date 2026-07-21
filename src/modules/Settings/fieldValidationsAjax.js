function validationField (modulename, fld_arr, oform) {
	var x;
	var count = 0;
	var recordid = getObj ('record').value,
		n = fld_arr.length;

	for (x = 0; x < n; x++) {
		var tablename = fld_arr[ x ][ 'tablename' ];
		var fieldname = fld_arr[ x ].fieldname;

		var validationtype = fld_arr[ x ][ 'validationtype' ];
		var fieldId = '';
		if (validationtype == 'D') {
			fieldId = "jscal_field_" + fld_arr[ x ].fieldname;
		} else {
			fieldId = fld_arr[ x ].fieldname;
		}

		var fieldValue = jQuery ("#" + fieldId).val ();

		var minvalue = fld_arr[ x ][ 'initialvalue' ];
		var maxvalue = fld_arr[ x ][ 'maximumvalue' ];

		var url = 'module=Settings&action=SettingsAjax&file=fieldValidationsAjax&ajax=true&modulename=' + modulename + '&tablename=' + tablename + '&recordid=' + recordid + '&fieldname=' + fieldname + '&fieldValue=' + fieldValue + '&validationtype=' + validationtype + '&minvalue=' + minvalue + '&maxvalue=' + maxvalue + '&sub_mode=validationField';

		var isValid = true;
		new Ajax.Request (
			'index.php',
			{
				asynchronous: false,
				cache:        false,
				queue:        {
					position: 'end',
					scope:    'command'
				},
				method:       'post',
				postBody:     url,
				onSuccess:    function (response) {
					if (response.responseText != '') {
						//si el campo contiene valor repetido entra
						VtigerJS_DialogBox.unblock ();
						//envia un alert que el campo esta repetido.
						alert (response.responseText);
						//se coloca el cursor en el campo repetido.
						document.getElementById (fieldId).focus ();
						//se suma uno al contador si el valor es repetido
						count++;
						isValid = false;
						return false;
					}
				}
			}
		);
		if (!isValid) {
			return false;
		}
	}
	if (count == 0) {
		//si no hay campos con valor repetidos se hace submit();
		oform.submit ();
	}
}

function validationCheckFields (modulename, oform) {
	var url = 'module=Settings&action=SettingsAjax&file=fieldValidationsAjax&ajax=true&modulename=' + modulename + '&sub_mode=validationCheckFields';

	new Ajax.Request (
		'index.php',
		{
			asynchronous: false,
			cache:        false,
			queue:        {
				position: 'end',
				scope:    'command'
			},
			method:       'post',
			postBody:     url,
			onComplete:   function (response) {
				if (response.responseText == 'fields_unconfigured') {
					//Si el modulo no contiene campos en la tabla vtiger_field_validation
					//se hace submit
					oform.submit ();
				} else if (response.responseText != '') {
					//se procede a procesar los campos configurados
					var fld_arr;
					//Lo parseamos para convertirlo en objeto
					fld_arr = JSON.parse (response.responseText);
					validationField (modulename, fld_arr, oform);
				}
			}
		}
	);
}

function insertValidationField (modulename, fieldname, validationtype, minvalue, maxvalue) {
	var url = 'module=Settings&action=SettingsAjax&file=fieldValidationsAjax&&sub_mode=insertValidationField&ajax=true&modulename=' + modulename + '&fieldname=' + fieldname + '&validationtype=' + validationtype;

	if (minvalue != undefined) {
		url = url + '&minvalue=' + minvalue;
	}
	if (maxvalue != undefined) {
		url = url + '&maxvalue=' + maxvalue;
	}
	new Ajax.Request (
		'index.php',
		{
			asynchronous: false,
			cache:        false,
			queue:        {
				position: 'end',
				scope:    'command'
			},
			method:       'post',
			postBody:     url,
			onComplete:   function (response) {
				if (response.responseText == 'validation_change') {
					//Se ingresa o se actualiza la valudación
					location.reload ();
				} else {
					alert ("ERROR al insertar validación");
					return false;
				}
			}
		}
	);
	return true;
}

function unique (array) {
	return jQuery.grep (
		array,
		function (el, index) {
			return index === jQuery.inArray (el, array);
		}
	);
}
