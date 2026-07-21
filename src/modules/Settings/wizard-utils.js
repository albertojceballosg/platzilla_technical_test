(function (jQuery) {
	// Private functions
	var getNormalizedText = function (value) {
		var from  = 'àáäâèéëêìíïîòóöôùúüûñç·/-,:;',
			to    = 'aaaaeeeeiiiioooouuuunc______',
			i, l;

		value = value.toLowerCase ().replace (' ', '_');

		// remove accents, swap ñ for n, etc
		for (i = 0, l = from.length; i < l; i++) {
			value = value.replace (new RegExp (from.charAt (i), 'g'), to.charAt (i));
		}

		value = value.replace (/[^a-z0-9 _]/g, '').replace (/\s+/g, '_').replace (/-+/g, '_');
		return value;
	};

	var inAdministrationClickHandler = function (checkbox) {
		var isAdmin = jQuery (checkbox);
		if (isAdmin.prop ('checked')) {
			isAdmin.val ('Si');
			jQuery ('#appMadre').show ();
			jQuery ('#modPadre').hide ();
		} else {
			isAdmin.val ('No');
			jQuery ('#appMadre').hide ();
			jQuery ('#modPadre').show ();
		}
	};

	var normalizeFieldContents = function (selector) {
		var field = jQuery (selector);
		field.val (getNormalizedText (field.val ()));
	};

	var onSubmitSuccessHandler = function (response, dialogId) {
		var html, scriptTags, i, scriptTag, script, head, n;

		jQuery (dialogId).html (response);
		html = jQuery (response);
		scriptTags = html.find ('script');
		// Evaluate all the script tags in the response text.
		n = scriptTags.length;
		for (i = 0; i < n; i++) {
			scriptTag = scriptTags[ i ];
			if (scriptTag.type === 'text/html') {
				continue;
			}
			script = document.createElement ('script');
			script.type = 'text/javascript';
			head = document.getElementsByTagName ('head')[ 0 ];
			if (scriptTag.src == '') {
				script.appendChild (document.createTextNode (scriptTag.innerHTML));//txt is the code
				head.appendChild (script);
			}
		}
	};

	var updateFieldPropertiesUI = function (row, selectedFieldType) {
		var field,
			fieldType = isNaN (selectedFieldType) ? 1 : parseInt (selectedFieldType);

		field = row.find ('.field-length');
		if (jQuery.inArray (fieldType, [ 1, 7, 9, 71 ]) !== -1) {
			field.css ('display', 'inline');
			if (jQuery.inArray (fieldType, [ 7, 9, 71 ]) !== -1) {
				field.val (18);
			} else {
				field.val ('');
			}
		} else {
			field.css ('display', 'none');
		}

		field = row.find ('.field-values');
		if (jQuery.inArray (fieldType, [ 15, 33 ]) !== -1) {
			field.css ('display', 'inline');
		} else {
			field.css ('display', 'none');
		}

		field = row.find ('.global-picklist');
		if (fieldType === 16) {
			field.css ('display', 'inline');
		} else {
			field.css ('display', 'none');
		}

		field = row.find ('.field-modules');
		if (jQuery.inArray (fieldType, [ 10, 404 ]) !== -1) {
			field.css ('display', 'inline');
		} else {
			field.css ('display', 'none');
		}

		field = row.find ('.field-progress-bar');
		if (jQuery.inArray (fieldType, [ 108 ]) !== -1) {
			field.css ('display', 'inline');
		} else {
			field.css ('display', 'none');
		}

		field = row.find ('.field-prefix');
		if (jQuery.inArray (fieldType, [ 4 ]) !== -1) {
			field.css ('display', 'inline');
		} else {
			field.css ('display', 'none');
		}

		field = row.find ('.field-precision');
		if (jQuery.inArray (fieldType, [ 7, 9, 71 ]) !== -1) {
			field.css ('display', 'inline');
			field.val (2);
		} else {
			field.css ('display', 'none');
		}

		field = row.find ('.field-sequence');
		if (jQuery.inArray (fieldType, [ 4 ]) !== -1) {
			field.css ('display', 'inline');
		} else {
			field.css ('display', 'none');
		}
	};

	var validateStep1Data = function () {
		var form = jQuery ('form[name="wizardPaso1"]'),
			field, value;

		field = form.find ('input.module-name');
		value = jQuery (field).val ();
		if (value.trim () === '') {
			alert ('El nombre código no puede estar vacío');
			field.focus ();
			return false;
		}
		if (value.length > 25) {
			alert ('La longitud máxima del campo es 25 caracteres');
			field.focus ();
			return false;
		}

		field = form.find ('input.module-label');
		value = jQuery (field).val ();
		if (value.trim () === '') {
			alert ('El nombre público no puede estar vacío');
			field.focus ();
			return false;
		}
		if (value.length > 100) {
			alert ('La longitud máxima del campo es 100 caracteres');
			field.focus ();
			return false;
		}
		return true;
	};

	var validateStep2Data = function () {
		var form                     = jQuery ('form[name="wizardPaso2"]'),
			fields                   = form.find ('input.block-name'),
			invalidCharactersPattern = /^[0-9a-zA-ZáéíóúàèìòùÀÈÌÒÙÁÉÍÓÚñÑüÜ_\s]+$/,
			field, value, i, n;

		if (fields.length === 0) {
			alert ('Debe agregar un bloque');
			return false;
		}

		n = fields.length;
		for (i = 0; i < n; i += 1) {
			field = jQuery (fields [ i ]);
			value = field.val ();
			if (value.trim () === '') {
				alert ('El nombre del bloque no puede estar vacio');
				field.focus ();
				return false;
			}
			if ((invalidCharactersPattern.test (value) === false) || (value === '0')) {
				alert ('El nombre del bloque no puede contener caracteres especiales');
				field.focus ();
				return false;
			}
		}
		return true;
	};

	var validateStep3Data = function () {
		var form   = jQuery ('form[name="wizardPaso3"]'),
			fields = form.find ('input.field-name'),
			row, field, type, value, i, n, labels, names, values, min, max;

		if (fields.length < 3) {
			alert ('Agrega al menos 2 campos');
			return false;
		}

		labels = [];
		names = [];
		n = fields.length;
		for (i = 0; i < n; i += 1) {
			field = jQuery (fields [ i ]);
			row = field.closest ('tr');
			type = row.find ('.field-type').val () ? parseInt (row.find ('.field-type').val ()): '';
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				alert ('Introduce el nombre del campo');
				row.find ('.field-label').focus ();
				return false;
			} else if (jQuery.inArray (value.toLowerCase (), names) !== -1) {
				alert ('Introduce un nombre de campo único');
				row.find ('.field-label').focus ();
				return false;
			}
			names.push (value.toLowerCase ());

			field = row.find ('.field-label');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				alert ('Introduce la etiqueta del campo');
				field.focus ();
				return false;
			} else if (jQuery.inArray (value.toLowerCase (), labels) !== -1) {
				alert ('Introduce una etiqueta de campo única');
				field.focus ();
				return false;
			}
			labels.push (value.toLowerCase ());

			if (jQuery.inArray (type, [ 1, 7, 9, 71 ]) !== -1) {
				field = row.find ('.field-length');
				value = field.val ();
				if (!value) {
					alert ('Introduce la longitud del campo');
					field.focus ();
					return false;
				} else if ((!jQuery.isNumeric (value)) || (value <= 0)) {
					alert ('Introduce una longitud de campo mayor que cero');
					field.focus ();
					return false;
				}
			}

			if (jQuery.inArray (type, [ 7, 9, 71 ]) !== -1) {
				field = row.find ('.field-precision');
				value = field.val ();
				if ((value === undefined) || (value === null) || (value.trim () === '')) {
					alert ('Introduce la precisión del número');
					field.focus ();
					return false;
				} else if ((!jQuery.isNumeric (value)) || (value <= 0)) {
					alert ('Introduce una precisión de número mayor que cero');
					field.focus ();
					return false;
				}
			}

			if (jQuery.inArray (type, [ 15, 33 ]) !== -1) {
				field = row.find ('.field-values');
				value = field.val ();
				if ((value === undefined) || (value === null) || (value.trim () === '')) {
					alert ('Introduce la lista de valores');
					field.focus ();
					return false;
				}
				values = value.split ('\n');
				if (values.length < 2) {
					alert ('Introduce al menos dos valores');
					field.focus ();
					return false;
				}
			}

			if (jQuery.inArray (type, [ 10, 404 ]) !== -1) {
				field = row.find ('.field-modules');
				value = field.val ();
				if ((value === undefined) || (value === null) || (value.trim () === '') || (value.trim () === '-')) {
					alert ('Selecciona el módulo de la lista');
					field.focus ();
					return false;
				}
			}

			if (jQuery.inArray (type, [ 4 ]) !== -1) {
				field = row.find ('.field-prefix');
				value = field.val ();
				if ((value === undefined) || (value === null) || (value.trim () === '')) {
					alert ('Introduce el prefijo');
					field.focus ();
					return false;
				} else if (/^[a-zA-Z]+\-*$/.test (value) === false) {
					alert ('Introduce un prefijo compuesto por sólo letras, sin símbolos ni espacios');
					field.focus ();
					return false;
				}

				field = row.find ('.field-sequence');
				value = field.val ();
				if ((value === undefined) || (value === null) || (value.trim () === '')) {
					alert ('Introduce la secuencia');
					field.focus ();
					return false;
				} else if (/^[0-9]+$/.test (value) === false) {
					alert ('Introduce una secuencia compuesta por sólo números, sin símbolos ni espacios');
					field.focus ();
					return false;
				}
			}

			if (jQuery.inArray (type, [ 108 ]) !== -1) {
				field = row.find ('.field-min');
				value = field.val ();
				if ((value === undefined) || (value === null) || (value.trim () === '')) {
					alert ('Introduce el valor mínimo');
					field.focus ();
					return false;
				} else if (/^[0-9]+$/.test (value) === false) {
					alert ('Introduce un valor mínimo numérico mayor o igual a 0');
					field.focus ();
					return false;
				} else if (parseInt (value) < 0) {
					alert ('Introduce un valor mínimo mayor o igual a 0');
					field.focus ();
					return false;
				}
				min = value;

				field = row.find ('.field-max');
				value = field.val ();
				if ((value === undefined) || (value === null) || (value.trim () === '')) {
					alert ('Introduce el valor máximo');
					field.focus ();
					return false;
				} else if (/^[0-9]+$/.test (value) === false) {
					alert ('Introduce un valor máximo numérico mayor a 0');
					field.focus ();
					return false;
				} else if (parseInt (value) <= 0) {
					alert ('Introduce un valor máximo mayor a 0');
					field.focus ();
					return false;
				} else if (parseInt (value) <= min) {
					alert ('Introduce un valor máximo mayor al valor mínimo');
					field.focus ();
					return false;
				}
				max = value;

				field = row.find ('.field-ini');
				value = field.val ();
				if ((value === undefined) || (value === null) || (value.trim () === '')) {
					alert ('Introduce el valor inicial');
					field.focus ();
					return false;
				} else if (/^[0-9]+$/.test (value) === false) {
					alert ('Introduce un valor inicial numérico mayor o igual a 0');
					field.focus ();
					return false;
				} else if (parseInt (value) < 0) {
					alert ('Introduce un valor inicial mayor o igual a 0');
					field.focus ();
					return false;
				} else if (parseInt (value) < min) {
					alert ('Introduce un valor inicial mayor o igual al valor mínimo');
					field.focus ();
					return false;
				} else if (parseInt (value) >= max) {
					alert ('Introduce un valor inicial menor al valor máximo');
					field.focus ();
					return false;
				}
			}
		}
		return true;
	};

	var validateStep4Data = function () {
		var form   = jQuery ('form[name="wizardPaso4"]'),
			fields, row, field, value, i, n, labels, names, viewColumns, viewColumn;

		field = form.find ('.identifier-field');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Selecciona el campo indentificador del módulo');
			field.focus ();
			return false;
		}

		viewColumns = form.find ('.view-column');
		if (viewColumns.length === 0) {
			alert ('Selecciona alguna columna para la vista principal');
			return false;
		}

		names = [];
		n = viewColumns.length;
		for (i = 0; i < n; i += 1) {
			viewColumn = jQuery (viewColumns [ i ]);
			value = viewColumn.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				continue;
			}
			if (jQuery.inArray (value.toLowerCase (), names) !== -1) {
				alert ('La columna ' + viewColumn.find ('option:selected').text () + ' ya está seleccionada como parte de la vista inicial');
				viewColumn.focus ();
				return false;
			}
			names.push (value.toLowerCase ());
		}
		if (names.length < 2) {
			alert ('Selecciona al menos 2 columnas para la vista principal');
			jQuery (viewColumns [0]).focus ();
			return false;
		}

		fields = form.find ('.related-name');
		if (fields.length === 0) {
			return true;
		}

		labels = [];
		names = [];
		n = fields.length;
		for (i = 0; i < n; i += 1) {
			row = jQuery (fields [ i ]).closest ('tr');

			field = row.find ('.related-label');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				alert ('Introduce la etiqueta del campo');
				field.focus ();
				return false;
			} else if (jQuery.inArray (value.toLowerCase (), labels) !== -1) {
				alert ('Introduce una etiqueta de campo única');
				field.focus ();
				return false;
			}
			labels.push (value.toLowerCase ());

			field = jQuery (fields [ i ]);
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				alert ('Selecciona el módulo relacionado');
				field.focus ();
				return false;
			} else if (jQuery.inArray (value.toLowerCase (), names) !== -1) {
				alert ('Selecciona un módulo relacionado único');
				field.focus ();
				return false;
			}
			names.push (value.toLowerCase ());

			if ((!row.find ('.related-action-add').is (':checked')) && (!row.find ('.related-action-select').is (':checked')) && (!row.find ('.related-action-pattern').is (':checked'))) {
				alert ('Selecciona al menos una acción');
				row.find ('.related-action-add').focus ();
				return false;
			}
		}
		return true;
	};

	// Public functions
	var addBlock = function () {
		var templateContents = jQuery ('#block-template').html (),
			table            = jQuery ('#proTab'),
			rows             = table.find ('tbody > tr'),
			row,
			lineFields,
			i, n,
			rowNumber        = 0;
		if (!templateContents) {
			return;
		}

		if (rows.length > 0) {
			lineFields = table.find ('.block-number');
			n = lineFields.length;
			for (i = 0; i < n; i += 1) {
				rowNumber = Math.max (rowNumber, jQuery (lineFields [ i ]).val ());
			}
		}

		row = jQuery (templateContents);
		row.find ('.block-number').val (rowNumber + 1);
		table.append (row);
	};

	var addField = function (link) {
		var thiz = jQuery (link),
			blockNumber = thiz.attr ('data-block-number'),
			templateContents = jQuery ('#field-template').html (),
			table            = thiz.closest ('tr').find ('.block-fields > tbody'),
			row;
		if (!templateContents) {
			return;
		}
		row = jQuery (templateContents);
		row.find ('.block-number').val (blockNumber);
		row.find ('.field-type').val ('1');
		updateFieldPropertiesUI (row, 1);
		table.append (row);
	};

	var addRelatedList = function (link) {
		var thiz = jQuery (link),
			templateContents = jQuery ('#related-list-template').html (),
			table            = thiz.closest ('table').find ('.related-lists > tbody'),
			row;
		if (!templateContents) {
			return;
		}
		row = jQuery (templateContents);
		table.append (row);
	};

	var deleteRow = function (link) {
		var row = jQuery (link).closest ('tr');
		if (!row) {
			return;
		}
		row.remove ();
	};

	var copyNormalizedFieldName = function (thiz) {
		var sourceField = jQuery (thiz),
			fieldType = sourceField.closest ('tr').find ('.field-type').val (),
			destinationField = sourceField.closest ('tr').find ('.field-name');
		if (fieldType !== '16') {
			destinationField.val (getNormalizedText (sourceField.val ()));
		}
	};

	var changeFieldPropertiesUI = function (thiz) {
		var selectField             = jQuery (thiz),
			row                     = selectField.closest ('tr'),
			mainProperties          = row.find ('field-main-properties'),
			fieldType               = selectField.val (),
			field;

		mainProperties.css ('visibility', 'hidden');
		updateFieldPropertiesUI (row, fieldType);
		mainProperties.css ('visibility', 'visible');
	};

	var createModule = function () {
		var form, action;
		if (!validateStep4Data ()) {
			return false;
		}

		form = jQuery ('form[name="wizardPaso4"]');
		action = 'wizardPaso5';
		form.find ('input[name="action"]').val (action);
		jQuery ('.form-container').css ('display', 'none');
		jQuery ('.message-container').css ('display', 'block');
		jQuery.ajax ('index.php', {
			data:     form.serialize (),
			dataType: 'text',
			method:   form.attr ('method')
		}).done (function (response) {
			onSubmitSuccessHandler (response, form.attr ('data-dialog'));
		});
	};

	var goBackToStep1 = function () {
		var form   = jQuery ('form[name="wizardPaso2"]'),
			action = 'wizardPaso1';

		form.find ('input[name="action"]').val (action);
		jQuery.ajax ('index.php', {
			data:     form.serialize (),
			dataType: 'text',
			method:   form.attr ('method')
		}).done (function (response) {
			onSubmitSuccessHandler (response, form.attr ('data-dialog'));
		});
	};

	var goBackToStep2 = function () {
		var form   = jQuery ('form[name="wizardPaso3"]'),
			action = 'wizardPaso2';

		form.find ('input[name="action"]').val (action);
		jQuery.ajax ('index.php', {
			data:     form.serialize (),
			dataType: 'text',
			method:   form.attr ('method')
		}).done (function (response) {
			onSubmitSuccessHandler (response, form.attr ('data-dialog'));
		});
	};

	var goBackToStep3 = function () {
		var form   = jQuery ('form[name="wizardPaso4"]'),
			action = 'wizardPaso3';

		form.find ('input[name="action"]').val (action);
		jQuery.ajax ('index.php', {
			data:     form.serialize (),
			dataType: 'text',
			method:   form.attr ('method')
		}).done (function (response) {
			onSubmitSuccessHandler (response, form.attr ('data-dialog'));
		});
	};

	var goForwardToStep2 = function () {
		var form;
		if (!validateStep1Data ()) {
			return;
		}

		form = jQuery ('form[name="wizardPaso1"]');
		jQuery.ajax ('index.php', {
			data:     form.serialize (),
			dataType: 'text',
			method:   form.attr ('method')
		}).done (function (response) {
			onSubmitSuccessHandler (response, form.attr ('data-dialog'));
		});
	};

	var goForwardToStep3 = function () {
		var form, action;
		if (!validateStep2Data ()) {
			return false;
		}

		form = jQuery ('form[name="wizardPaso2"]');
		action = 'wizardPaso3';
		form.find ('input[name="action"]').val (action);
		jQuery.ajax ('index.php', {
			data:     form.serialize (),
			dataType: 'text',
			method:   form.attr ('method')
		}).done (function (response) {
			onSubmitSuccessHandler (response, form.attr ('data-dialog'));
		});
	};

	var goForwardToStep4 = function () {
		var form, action;
		if (!validateStep3Data ()) {
			return false;
		}

		form = jQuery ('form[name="wizardPaso3"]');
		action = 'wizardPaso4';
		form.find ('input[name="action"]').val (action);
		jQuery.ajax ('index.php', {
			data:     form.serialize (),
			dataType: 'text',
			method:   form.attr ('method')
		}).done (function (response) {
			onSubmitSuccessHandler (response, form.attr ('data-dialog'));
		});
	};

	var setFieldName = function (globalPicklistSelectElement) {
		var globalPicklistSelect = jQuery (globalPicklistSelectElement),
			globalPicklistFieldName = globalPicklistSelect.val (),
			fieldNameField = globalPicklistSelect.closest ('tr').find ('.field-name');

		fieldNameField.val (globalPicklistFieldName);
	};

	var updateRelatedHiddenField = function (checkbox) {
		var thiz = jQuery (checkbox),
			clazz = thiz.attr ('class'),
			value = thiz.is (':checked') ? '1' : '',
			relatedHiddenField = thiz.closest ('label').find ('input.' + clazz + '[type="hidden"]');
		relatedHiddenField.val (value);
	};

	window.WizardUtils = {
		addBlock: addBlock,
		addField: addField,
		addRelatedList: addRelatedList,
		changeFieldPropertiesUI: changeFieldPropertiesUI,
		copyNormalizedFieldName: copyNormalizedFieldName,
		createModule: createModule,
		deleteBlock: deleteRow,
		deleteField: deleteRow,
		deleteRelatedList: deleteRow,
		goBackToStep1: goBackToStep1,
		goBackToStep2: goBackToStep2,
		goBackToStep3: goBackToStep3,
		goForwardToStep2: goForwardToStep2,
		goForwardToStep3: goForwardToStep3,
		goForwardToStep4: goForwardToStep4,
		setFieldName: setFieldName,
		updateRelatedHiddenField: updateRelatedHiddenField
	};

	jQuery (document).ready (function () {
		jQuery ('#txtbox_nombreCodigo').keyup (function (evt) {
			normalizeFieldContents (evt.currentTarget);
		});
		jQuery ('#isAdmin').on ('click', function (evt) {
			inAdministrationClickHandler (evt.currentTarget);
		});
	});
}) (jQuery);
