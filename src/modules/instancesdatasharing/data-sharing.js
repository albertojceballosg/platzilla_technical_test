(function (jQuery) {
	// Private constants
	var UI_TYPE_CODE               = 4,
		UI_TYPE_CREATED_TIME       = 70,
		UI_TYPE_CURRENCY           = 71,
		UI_TYPE_DATE               = 5,
		UI_TYPE_DATETIME           = 6,
		UI_TYPE_EMAIL              = 13,
		UI_TYPE_GLOBAL_PICKLIST    = 16,
		UI_TYPE_GRID               = 2202,
		UI_TYPE_MODIFIED_BY        = 52,
		UI_TYPE_MODULE_RECORDS     = 404,
		UI_TYPE_MODULE_REFERENCE   = 10,
		UI_TYPE_MULTI_SELECT       = 33,
		UI_TYPE_NUMBER             = 7,
		UI_TYPE_OWNER              = 53,
		UI_TYPE_PERCENTAGE         = 9,
		UI_TYPE_PHONE              = 11,
		UI_TYPE_PICKLIST           = 15,
		UI_TYPE_SKYPE              = 85,
		UI_TYPE_TEXT               = 1,
		UI_TYPE_TEXTAREA           = 21,
		UI_TYPE_TIME               = 14,
		UI_TYPE_URL                = 17,
		DATA_TYPE_DATE             = 'DATE',
		DATA_TYPE_EMAIL            = 'EMAIL',
		DATA_TYPE_GRID             = 'GRID',
		DATA_TYPE_MODULE_REFERENCE = 'MODULE REFERENCE',
		DATA_TYPE_NUMBER           = 'NUMBER',
		DATA_TYPE_PICKLIST         = 'PICKLIST',
		DATA_TYPE_TEXT             = 'TEXT',
		DATA_TYPE_USER             = 'USER',
        idTab                       = '';

	// Private variables
	var totalNewDetails = 0,
		modal           = null;

	// Private Methods
	var destroyModal = function () {
		if (modal === null) {
			return;
		}

		jQuery (this).remove ();
		modal = null;
	};

	var getAvailableFields = function (fieldsData, uiType) {
		var i, fields;

		if (fieldsData.length === 0) {
			return null;
		}

		fields = [];
		for (i = 0; i < fieldsData.length; i += 1) {
			if (getDataType (parseInt (fieldsData [ i ][ 'uitype' ])) === getDataType (uiType)) {
				fields.push (fieldsData [ i ]);
			}
		}
		return fields.length > 0 ? fields : null;
	};

	var getDataType = function (uiType) {
		var dataType;

		switch (uiType) {
			case UI_TYPE_CREATED_TIME:
			case UI_TYPE_DATE:
			case UI_TYPE_DATETIME:
				dataType = DATA_TYPE_DATE;
				break;
			case UI_TYPE_CURRENCY:
			case UI_TYPE_NUMBER:
			case UI_TYPE_PERCENTAGE:
				dataType = DATA_TYPE_NUMBER;
				break;
			case UI_TYPE_EMAIL:
				dataType = DATA_TYPE_EMAIL;
				break;
			case UI_TYPE_GLOBAL_PICKLIST:
			case UI_TYPE_MULTI_SELECT:
			case UI_TYPE_PICKLIST:
				dataType = DATA_TYPE_PICKLIST;
				break;
			case UI_TYPE_GRID:
				dataType = DATA_TYPE_GRID;
				break;
			case UI_TYPE_MODULE_RECORDS:
			case UI_TYPE_MODULE_REFERENCE:
				dataType = DATA_TYPE_MODULE_REFERENCE;
				break;
			case UI_TYPE_MODIFIED_BY:
			case UI_TYPE_OWNER:
				dataType = DATA_TYPE_USER;
				break;
			case UI_TYPE_PHONE:
			case UI_TYPE_SKYPE:
			case UI_TYPE_TEXT:
			case UI_TYPE_TEXTAREA:
			case UI_TYPE_TIME:
			case UI_TYPE_URL:
				dataType = DATA_TYPE_TEXT;
				break;
			default:
				dataType = null;
				break;
		}
		return dataType;
	};

	var getRegularField = function (fieldTemplate, detailId, fieldsData, fieldData) {
		var uiType = parseInt (fieldData [ 'uitype' ]),
			element, label, dataType, options, option, j, dummy, availableFields, parameterFormulas, parameterFormula, dataTypes;
		if (jQuery.inArray (uiType, [ UI_TYPE_CODE, UI_TYPE_CREATED_TIME, UI_TYPE_OWNER ]) !== -1) {
			return null;
		}

		label = fieldData.label + (fieldData [ 'mandatory' ] ? ' (*)' : '');
		dataType = getDataType (uiType);
		element = jQuery (fieldTemplate.html ()).attr ('data-id', detailId).attr ('data-uitype', fieldData [ 'uitype' ]);
		if (fieldData [ 'mandatory' ]) {
			element.addClass ('mandatory');
		} else {
			element.removeClass ('mandatory');
		}
		element.find ('.field-name').val (fieldData.name);
		element.find ('.field-label').val (label);
		element.find ('.action-type').attr ('name', 'details[' + detailId + '][fields][' + fieldData.name + '][actiontype]');
		dummy = element.find ('.parameter-type');
		dummy.attr ('name', 'details[' + detailId + '][fields][' + fieldData.name + '][parametertype]');

		// Mostrar/ocultar los tipos de parámetro según el uitype del campo
		options = dummy.find ('option');
		for (j = 0; j < options.length; j += 1) {
			option = jQuery (options [ j ]);
			dummy = option.data ('type') ? JSON.parse (option.data ('type').split ('\'').join ('"')) : [ 'TEXT' ];
			if ((option.val () === '') || (jQuery.inArray (dataType, dummy) !== -1)) {
				option.prop ('disabled', false).show ();
			} else {
				option.prop ('disabled', true).hide ();
			}
		}

		element.find ('.parameter-formula').attr ('name', 'details[' + detailId + '][fields][' + fieldData.name + '][parameterformula]');

		// Mostrar/ocultar las opciones de variables según el uitype de campo
		options = element.find ('.parameter-formula[data-parameter-type="VARIABLE"] option');
		for (j = 0; j < options.length; j += 1) {
			option = jQuery (options [ j ]);
			dataTypes = option.data ('type') ? JSON.parse (option.data ('type').split ('\'').join ('"')) : [ 'TEXT' ];
			if ((option.val () === '') || (jQuery.inArray (dataType, dataTypes) !== -1)) {
				option.prop ('disabled', false).show ();
			} else {
				option.prop ('disabled', true).hide ();
			}
		}

		// Generar las opciones para los parámetros de tipo SOURCE FIELD
		options = [];
		availableFields = getAvailableFields (fieldsData, uiType);
		if (jQuery.isArray (availableFields)) {
			for (j = 0; j < availableFields.length; j += 1) {
				options.push (jQuery ('<option></option>').val (availableFields [ j ].name).text (availableFields [ j ].label));
			}
		}
		if (options.length > 0) {
			options.unshift (jQuery ('<option></option>').val ('').text (''));
		} else {
			options.push (jQuery ('<option></option>').val ('').text ('Imposible ejecutar la operación: No se encuentran campos configurados en el módulo seleccionado'));
		}
		element.find ('.parameter-formula[data-parameter-type="SOURCE FIELD"]').append (options);

		// Generar las opciones para los parámetros de tipo PICKLIST
		if ((dataType == DATA_TYPE_PICKLIST) && (jQuery.isArray (fieldData [ 'options' ])) && (fieldData [ 'options' ].length > 0)) {
			options = [ jQuery ('<option></option>').val ('').text ('') ];
			for (j = 0; j < fieldData [ 'options' ].length; j += 1) {
				options.push (jQuery ('<option></option>').val (fieldData [ 'options' ][ j ]).text (fieldData [ 'options' ][ j ]));
			}
			parameterFormulas = element.find ('.parameter-formula[data-parameter-type="LITERAL"]');
			for (j = 0; j < parameterFormulas.length; j += 1) {
				parameterFormula = jQuery (parameterFormulas [ j ]);
				dataTypes = parameterFormula.data ('type') ? JSON.parse (parameterFormula.data ('type').split ('\'').join ('"')) : [ 'TEXT' ];
				if (jQuery.inArray (DATA_TYPE_PICKLIST, dataTypes) !== -1) {
					parameterFormula.append (options);
				}
			}
		}

		return element;
	};

	var setRecordOptions = function (fieldsData) {
		var fieldTemplate            = jQuery ('#rule-field-template'),
			mainRecordOptionsSection = jQuery ('#main-record-options'),
			i, options, option;

		mainRecordOptionsSection.empty ();
		if ((!jQuery.isArray (fieldsData)) || (fieldsData.length === 0)) {
			return;
		}

		totalNewDetails -= 1;
		options = [];
		for (i = 0; i < fieldsData.length; i += 1) {
			if (jQuery.inArray (parseInt (fieldsData [ i ][ 'uitype' ]), [ UI_TYPE_OWNER ]) !== -1) {
				continue;
			}

			option = getRegularField (fieldTemplate, totalNewDetails, fieldsData, fieldsData [ i ]);
			if (option === null) {
				continue;
			}
			option.find ('.date').datepicker ({ format: "yyyy-mm-dd", language: 'es', weekStart: 1 });
			options.push (option);
		}
		mainRecordOptionsSection.append (options).closest ('#rule-details').show ();
	};

	var validateSharing = function (dataSection) {
		var field, recipientType, value;

		field = dataSection.find ('.record-id');
		if (field.length === 0) {
			alert ('Selecciona los registros a compartir');
			return false;
		}

		field = dataSection.find ('#rule-id');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Selecciona qué quieres compartir');
			field.focus ();
			return false;
		}

		field = dataSection.find ('#recipient-type');
		recipientType = field.val ();
		if ((recipientType === undefined) || (recipientType === null) || (recipientType.trim () === '')) {
			alert ('Selecciona con quién lo quieres compartir');
			field.focus ();
			return false;
		}

		field = dataSection.find ('#recipient-value-literal');
		value = field.val ();
		if ((recipientType === 'LITERAL') && ((value === undefined) || (value === null) || (value.trim () === ''))) {
			alert ('Introduce las direcciones de correo separadas por comas');
			field.focus ();
			return false;
		}

		field = dataSection.find ('#customer-id');
		value = field.val ();
		if ((recipientType === 'CUSTOMER') && ((value === undefined) || (value === null) || (value.trim () === ''))) {
			alert ('Selecciona el cliente');
			field.focus ();
			return false;
		}

		field = dataSection.find ('#contact-id');
		value = field.val ();
		if ((recipientType === 'CONTACT') && ((value === undefined) || (value === null) || (value.trim () === ''))) {
			alert ('Selecciona el contacto');
			field.focus ();
			return false;
		}

		return true;
	};

	// Public methods
	var clearSelection = function (buttonElement) {
		jQuery (buttonElement).closest ('.field-container').find ('.form-control').val ('');
	};

	var deleteSync = function () {
		return confirm ('¿Estás seguro que quieres dejar de compartir el registro seleccionado?');
	};

	var openMassSharingModal = function (moduleName, id) {
        idTab = (id !== undefined) ? '-' + id : '';
		var selectedRecords = jQuery ('form#massdelete' + idTab).find ('#allselectedboxes' + idTab),
			selectedRecordIds;

		if ((selectedRecords.val () === null) || (selectedRecords.val () === undefined) || (selectedRecords.val ().trim () === '')) {
			alert ('Debes seleccionar algún registro para compartir');
			return;
		}

		selectedRecordIds = selectedRecords.val ().split (';');
		openSharingModal (moduleName, selectedRecordIds);
	};

	var openSharingModal = function (moduleName, recordIds) {
		var arguments = [
			'module=instancesdatasharing',
			'action=GetAvailableRules',
			'modulename=' + encodeURIComponent (moduleName),
			'Ajax=true'
		];
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'json',
			method:   'get'
		}).done (function (response) {
			var modalTemplate, i, customRules;
			if ((!response) || (!jQuery.isPlainObject (response)) || (!response.hasOwnProperty ('rules')) || (!response.hasOwnProperty ('contacts')) || (!response.hasOwnProperty ('customers'))) {
				alert ('Se ha recibido una respuesta inesperada. Intenta más tarde');
				return;
			}

			modalTemplate = jQuery ('#instances-data-sharing-share-modal-template');
			modal = jQuery (modalTemplate.html ());
			modal.find ('#module-name').val (moduleName);

			if ((jQuery.isArray (recordIds)) && (recordIds.length > 0)) {
				for (i = 0; i < recordIds.length; i += 1) {
					if (recordIds [ i ] !== '') {
						modal.find ('.data-sharing-section').append (jQuery ('<input />').attr ('type', 'hidden').val (recordIds [ i ]).addClass ('record-id'));
					}
				}
			} else if (!jQuery.isArray (recordIds)) {
				modal.find ('.data-sharing-section').append (jQuery ('<input />').attr ('type', 'hidden').val (recordIds).addClass ('record-id'));
			}

			if ((jQuery.isArray (response [ 'rules' ])) && (response [ 'rules' ].length > 0)) {
				customRules = [];
				for (i = 0; i < response [ 'rules' ].length; i += 1) {
					customRules.push (jQuery ('<option></option>').val (response [ 'rules' ][ i ][ 'id' ]).text (response [ 'rules' ][ i ][ 'label' ]));
				}
				modal.find ('#custom-rules').append (customRules);
			} else {
				modal.find ('#custom-rules').remove ();
			}

			if (response [ 'customers' ]) {
				modal.find ('#recipient-type').find ('option[value="CUSTOMER"]').prop ('disabled', false).show ();
			} else {
				modal.find ('#recipient-type').find ('option[value="CUSTOMER"]').prop ('disabled', true).hide ();
			}

			if (response [ 'contacts' ]) {
				modal.find ('#recipient-type').find ('option[value="CONTACT"]').prop ('disabled', false).show ();
			} else {
				modal.find ('#recipient-type').find ('option[value="CONTACT"]').prop ('disabled', true).hide ();
			}
			modal.modal ({ backdrop: 'static' }).on ('hidden.bs.modal', destroyModal);
		}).fail (function (jQueryResponse) {
			alert ('Se ha presentado un error. Intenta más tarde');
			console.error (jQueryResponse.responseText);
		});
	};

	var openSyncsModal = function (moduleName, recordId) {
		var arguments = [
			'module=instancesdatasharing',
			'action=GetAvailableSyncs',
			'modulename=' + encodeURIComponent (moduleName),
			'Ajax=true'
		];
		if ((recordId !== undefined) && (recordId !== null)) {
			arguments.push ('record=' + encodeURIComponent (recordId));
		}

		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'json',
			method:   'get'
		}).done (function (response) {
			var syncTemplate = jQuery ('#instances-data-sharing-syncs-modal-sync-template'),
				modalTemplate, i, sync;
			if ((!response) || (!jQuery.isPlainObject (response)) || (!response.hasOwnProperty ('received')) || (!response.hasOwnProperty ('sent'))) {
				alert ('Se ha recibido una respuesta inesperada. Intenta más tarde');
				return;
			}

			modalTemplate = jQuery ('#instances-data-sharing-syncs-modal-template');
			modal = jQuery (modalTemplate.html ());
			if ((jQuery.isArray (response [ 'sent' ])) && (response [ 'sent' ].length > 0)) {
				modal.find ('.record-identifier-name').text (response [ 'sent' ][0]['identifierlabel']);
				for (i = 0; i < response [ 'sent' ].length; i += 1) {
					sync = jQuery (syncTemplate.html ());
					sync.find ('.record-identifier-value').text (response [ 'sent' ][ i ][ 'identifiervalue' ]);
					sync.find ('.source-email-address').text (response [ 'sent' ][ i ][ 'sourceemailaddress' ]);
					sync.find ('.target-email-address').text (response [ 'sent' ][ i ][ 'targetemailaddress' ]);
					sync.find ('.rule-name').text (response [ 'sent' ][ i ][ 'rulename' ]);
					sync.find ('.actions input[name="record"]').val (response [ 'sent' ][ i ][ 'syncid' ]);
					modal.find ('#sent-syncs tbody').append (sync);
					modal.find ('#sent-syncs').show ();
				}
			}
			if ((jQuery.isArray (response [ 'received' ])) && (response [ 'received' ].length > 0)) {
				modal.find ('.record-identifier-name').text (response [ 'received' ][ 0 ][ 'identifierlabel' ]);
				for (i = 0; i < response [ 'received' ].length; i += 1) {
					sync = jQuery (syncTemplate.html ());
					sync.find ('.record-identifier-value').text (response [ 'received' ][ i ][ 'identifiervalue' ]);
					sync.find ('.source-email-address').text (response [ 'received' ][ i ][ 'sourceemailaddress' ]);
					sync.find ('.target-email-address').text (response [ 'received' ][ i ][ 'targetemailaddress' ]);
					sync.find ('.rule-name').text (response [ 'received' ][ i ][ 'rulename' ]);
					sync.find ('.actions input[name="record"]').val (response [ 'received' ][ i ][ 'syncid' ]);
					modal.find ('#received-syncs tbody').append (sync);
					modal.find ('#received-syncs').show ();
				}
			}
			modal.modal ({ backdrop: 'static' }).on ('hidden.bs.modal', destroyModal);
		}).fail (function (jQueryResponse) {
			alert ('Se ha presentado un error. Intenta más tarde');
			console.error (jQueryResponse.responseText);
		});
	};

	var setModuleName = function (selectElement) {
		var select     = jQuery (selectElement),
			moduleName = select.val (),
			arguments;

		if ((moduleName === undefined) || (moduleName === null) || (moduleName.trim () === '')) {

		} else {
			arguments = [
				'module=instancesdatasharing',
				'action=GetAvailableFieldsData',
				'modulename=' + encodeURIComponent (moduleName),
				'Ajax=true'
			];
			jQuery.ajax ('index.php', {
				data:     arguments.join ('&'),
				dataType: 'json',
				method:   'get'
			}).done (function (response) {
				setRecordOptions (response);
			}).fail (function (jQueryResponse) {
				alert (JSON.parse (jQueryResponse.responseText));
			});
		}
	};

	var setParameterType = function (selectElement) {
		var select        = jQuery (selectElement),
			parameterType = select.val (),
			fieldSection  = select.closest ('.rule-field'),
			uiType        = fieldSection.data ('uitype'),
			options, option, i, dataTypes, dataType;

		if ((parameterType === undefined) || (parameterType === null) || (parameterType.trim () === '')) {
			fieldSection.find ('.parameter-formula').prop ('disabled', true).hide ();
			fieldSection.find ('.action-type').prop ('disabled', true).hide ();
		} else {
			// Mostrar/ocultar las opciones de fórmula que aplican según el tipo de parámetro seleccionado
			options = fieldSection.find ('.parameter-formula');
			for (i = 0; i < options.length; i += 1) {
				option = jQuery (options [ i ]);
				dataTypes = option.data ('type') ? JSON.parse (option.data ('type').split ('\'').join ('"')) : [ 'TEXT' ];
				dataType = getDataType (uiType);
				if ((option.data ('parameter-type') === parameterType) && (jQuery.inArray (dataType, dataTypes) !== -1)) {
					option.prop ('disabled', false).show ();
				} else {
					option.prop ('disabled', true).hide ();
				}
			}
			fieldSection.find ('.action-type').prop ('disabled', false).show ();
		}
	};

	var setRecipientType = function (selectElement) {
		var select            = jQuery (selectElement),
			recipientsSection = select.closest ('.recipients'),
			recipientType     = select.val ();

		if (recipientType === 'LITERAL') {
			recipientsSection.find ('#recipient-value-contact').prop ('disabled', true).hide ().find ('.form-control').prop ('disabled', true).val ('');
			recipientsSection.find ('#recipient-value-customer').prop ('disabled', true).hide ().find ('.form-control').prop ('disabled', true).val ('');
			recipientsSection.find ('#recipient-value-literal').prop ('disabled', false).val ('').show ();
		} else if (recipientType === 'CONTACT') {
			recipientsSection.find ('#recipient-value-literal').prop ('disabled', true).val ('').hide ();
			recipientsSection.find ('#recipient-value-customer').prop ('disabled', true).hide ().find ('.form-control').prop ('disabled', true).val ('');
			recipientsSection.find ('#recipient-value-contact').prop ('disabled', false).show ().find ('.form-control').prop ('disabled', false).val ('');
		} else if (recipientType === 'CUSTOMER') {
			recipientsSection.find ('#recipient-value-literal').prop ('disabled', true).val ('').hide ();
			recipientsSection.find ('#recipient-value-contact').prop ('disabled', true).hide ().find ('.form-control').prop ('disabled', true).val ('');
			recipientsSection.find ('#recipient-value-customer').prop ('disabled', false).show ().find ('.form-control').prop ('disabled', false).val ('');
		} else {
			recipientsSection.find ('#recipient-value-literal').prop ('disabled', true).val ('').hide ();
			recipientsSection.find ('#recipient-value-contact').prop ('disabled', true).hide ().find ('.form-control').prop ('disabled', true).val ('');
			recipientsSection.find ('#recipient-value-customer').prop ('disabled', true).hide ().find ('.form-control').prop ('disabled', true).val ('');
		}
	};

	var sendRequest = function (buttonElement) {
		var button      = jQuery (buttonElement),
			dataSection = button.closest ('.data-sharing-content').find ('.data-sharing-section'),
			arguments, recordIds, recipientType, i, dummy;

		if (!validateSharing (dataSection)) {
			return;
		}

		recipientType = dataSection.find ('#recipient-type').val ();
		arguments = [
			'module=instancesdatasharing',
			'action=SendRequest',
			'modulename=' + encodeURIComponent (dataSection.find ('#module-name').val ()),
			'ruleid=' + encodeURIComponent (dataSection.find ('#rule-id').val ()),
			'recipienttype=' + encodeURIComponent (recipientType),
			'Ajax=true'
		];
		recordIds = dataSection.find ('.record-id');
		for (i = 0; i < recordIds.length; i += 1) {
			arguments.push ('recordids[]=' + jQuery (recordIds [ i ]).val ());
		}
		if (recipientType === 'LITERAL') {
			dummy = dataSection.find ('#recipient-value-literal').val ().split (',');
			for (i = 0; i < dummy.length; i += 1) {
				arguments.push ('emailaddresses[]=' + dummy [ i ]);
			}
		} else if (recipientType === 'CUSTOMER') {
			arguments.push ('customerid=' + dataSection.find ('#customer-id').val ());
		} else if (recipientType === 'CONTACT') {
			arguments.push ('contactid=' + dataSection.find ('#contact-id').val ());
		}

		dummy = dataSection.find ('#comments').val ();
		if ((dummy !== undefined) && (dummy !== null) && (dummy.trim () !== '')) {
			arguments.push ('comments=' + encodeURIComponent (dummy));
		}

		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'json',
			method:   'post'
		}).done (function (response) {
			alert (response);
			modal.hide ();
			window.location.reload ();
		}).fail (function (jQueryResponse) {
			var message;

			try {
				message = JSON.parse (jQueryResponse.responseText);
			} catch (e) {
				message = 'Se ha presentado un error inesperado. Intenta más tarde';
			}
			alert (message);
		});
	};

	var validateRequest = function (formElement) {
		var form = jQuery (formElement),
			field, password, repeatedPassword, value;

		field = form.find ('#password');
		password = field.val ();
		if ((password === undefined) || (password === null) || (password.trim () === '')) {
			alert ('Introduce tu contraseña');
			field.focus ();
			return false;
		}

		field = form.find ('#repeated-password');
		repeatedPassword = field.val ();
		if ((repeatedPassword === undefined) || (repeatedPassword === null) || (repeatedPassword.trim () === '')) {
			alert ('Repite tu contraseña');
			field.focus ();
			return false;
		} else if (password !== repeatedPassword) {
			alert ('Las contraseñas no coinciden');
			field.focus ();
			return false;
		}

		field = form.find ('#first-name');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Introduce tu nombre');
			field.focus ();
			return false;
		}

		field = form.find ('#last-name');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Introduce tu(s) apellido(s)');
			field.focus ();
			return false;
		}

		field = form.find ('#application-code');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Elige una aplicación para acceder a los contenidos');
			field.focus ();
			return false;
		}
		form.hide ();
		jQuery ('.message-container').show ();
		return true;
	};

	var validateRule = function (formElement) {
		var form = jQuery (formElement),
			field, value;

		field = form.find ('#rule-name');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Introduce el nombre');
			field.focus ();
			return false;
		}

		field = form.find ('#rule-status');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Selecciona el status');
			field.focus ();
			return false;
		}

		field = form.find ('#module-name');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Selecciona el módulo');
			field.focus ();
			return false;
		}

		return true;
	};

	window.DataSharingUtils = {
		clearSelection:       clearSelection,
		deleteSync:           deleteSync,
		openMassSharingModal: openMassSharingModal,
		openSharingModal:     openSharingModal,
		openSyncsModal:       openSyncsModal,
		sendRequest:          sendRequest,
		setModuleName:        setModuleName,
		setParameterType:     setParameterType,
		setRecipientType:     setRecipientType,
		validateRequest:      validateRequest,
		validateRule:         validateRule
	};
} (jQuery));
