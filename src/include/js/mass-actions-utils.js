(function (jQuery) {
	// Private constants
	var MODAL_TYPE_FIELDS    = 0,
		MODAL_TYPE_VARIABLES = 1;

	// Private variables
	var modal                       = null,
		auxiliaryModal              = null,
		auxiliaryModalTriggerButton = null,
		fields                      = null,
		systemVariables             = null,
		templates                   = null,
		idTab                       = '';

	// Private methods
	var destroyModal = function () {
		if (modal === null) {
			return;
		}

		jQuery (this).remove ();
		modal = null;
	};

    var parseQuery = function ( scripts ) {
    	var scriptName = 'mass-actions-utils';
        // Look through them trying to find ourselves

        for(var i=0; i<scripts.length; i++) {
            if(scripts[i].src.indexOf("/" + scriptName) > -1) {
                var pa = scripts[i].src.split("?").pop().split("&");
                // Split each key=value into array, the construct js object
                var p = {};
                for(var j=0; j<pa.length; j++) {
                    var kv = pa[j].split("=");
                    p[kv[0]] = kv[1];
                }
            }
        }

        // No scripts match

        return {};
    };

	var onFailureHandler = function (jQueryResponse) {
		alert ('Se ha presentado un error: ' + jQueryResponse.responseText);
	};

	var onGetMassEditFieldsSuccessHandler = function (response) {
		var modalTemplate     = jQuery ('#mass-edit-modal-template'),
			selectedRecordIds = jQuery ('form#massdelete' + idTab).find ('#allselectedboxes' + idTab).val ().split (';'),
			i, field, fields;

		if (selectedRecordIds.length > 0) {
			fields = [];
			for (i = 0; i < selectedRecordIds.length; i += 1) {
				if (selectedRecordIds [ i ] === '') {
					continue;
				}

				field = jQuery ('<input>').attr ('type', 'text').attr ('name', 'recordids[]').attr ('value', selectedRecordIds [ i ]);
				fields.push (field);
			}
		} else {
			fields = null;
		}
		modal = jQuery (modalTemplate.html ());
		modal.find ('form').append (fields);
		modal.find ('.modal-title').text ('Edición masiva');
		modal.find ('.modal-body').append (response);
		modal.modal ({ backdrop: 'static' }).on ('hidden.bs.modal', destroyModal);
	};

	var onGetMassMailDataSuccessHandler = function (response) {
		var modalTemplate     = jQuery ('#mass-mail-modal-template'),
			selectedRecordIds = jQuery ('form#massdelete'+ idTab).find ('#allselectedboxes' + idTab).val ().split (';'),
			i, field, recordIdsFields, language, languageOptions, templateOptions, fieldsOptions, fieldName, dummy;

		if ((response === null) || (response === undefined) || (!response.hasOwnProperty ('templates'))) {
			alert ('Se ha recibido una respuesta inesperada del servidor. Por favor intenta más tarde');
			return;
		} else if ((response.templates === null) || (Object.keys (response.templates).length === 0)) {
			alert ('No se encuentran plantillas de correo registradas');
			return;
		}

		templates = response.templates;
		fields = response.fields;
		systemVariables = response [ 'systemvariables' ];

		languageOptions = [ jQuery ('<option></option>').text ('').val ('') ];
		dummy = Object.keys (templates);
		for (i = 0; i < dummy.length; i += 1) {
			language = dummy [ i ];
			if ((jQuery.isArray (templates [ language ])) && (templates [ language ].length > 0)) {
				if (language === 'en') {
					languageOptions.push (jQuery ('<option></option>').text ('Inglés').val (language));
				} else if (language === 'pt') {
					languageOptions.push (jQuery ('<option></option>').text ('Portugués').val (language));
				} else {
					languageOptions.push (jQuery ('<option></option>').text ('Español').val (language));
				}
			}
		}

		templateOptions = [ jQuery ('<option></option>').text ('').val ('') ];
		for (language in templates) {
			if (!templates.hasOwnProperty (language)) {
				continue;
			}

			for (i = 0; i < templates [ language ].length; i += 1) {
				templateOptions.push (
					jQuery ('<option></option>')
						.text (templates [ language ][ i ][ 'templatename' ])
						.val (templates [ language ][ i ][ 'templatename' ])
						.attr ('data-language', language)
						.addClass ('template-name')
						.hide ()
				);
			}
		}

		fieldsOptions = [];
		for (fieldName in fields) {
			if (!fields.hasOwnProperty (fieldName)) {
				continue;
			}

			fieldsOptions.push (
				jQuery ('<option></option>')
					.text (fields [ fieldName ])
					.val (fieldName)
			);
		}

		if (selectedRecordIds.length > 0) {
			recordIdsFields = [];
			for (i = 0; i < selectedRecordIds.length; i += 1) {
				if (selectedRecordIds [ i ] === '') {
					continue;
				}

				field = jQuery ('<input>').attr ('type', 'hidden').attr ('name', 'recordids[]').attr ('value', encodeURIComponent (selectedRecordIds [ i ]));
				recordIdsFields.push (field);
			}
		} else {
			recordIdsFields = null;
		}

		modal = jQuery (modalTemplate.html ());
		modal.find ('#mass-mail-language').append (languageOptions);
		modal.find ('#mass-mail-template-name').append (templateOptions);
		modal.find ('#mass-mail-recipients-source-fields').append (fieldsOptions);
		modal.find ('form').append (recordIdsFields);
		modal.modal ({ backdrop: 'static' }).on ('hidden.bs.modal', destroyModal);
	};

	var openAuxiliaryModal = function (buttonElement, elementType) {
		var button                 = jQuery (buttonElement),
			auxiliaryModalTemplate;

		if (elementType === MODAL_TYPE_FIELDS) {
			auxiliaryModalTemplate = jQuery ('#mass-mail-fields-modal-template');
		} else {
			auxiliaryModalTemplate = jQuery ('#mass-mail-system-modal-template');
		}
		auxiliaryModal = jQuery (auxiliaryModalTemplate.html ());
		auxiliaryModalTriggerButton = button;
		auxiliaryModal.modal ({ backdrop: 'static' }).on ('hidden.bs.modal', function () {
			if (auxiliaryModal === null) {
				return;
			}

			jQuery (this).remove ();
			auxiliaryModal = null;
		});
	};

	var validateForm = function (form, id) {
		var idValidate = true,
			field, value, parameters, parameter, i;

		field = form.find ('#mass-mail-language-' + id);
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			field.parent().addClass('has-error');
			field.parent().find('.help-block').html('Selecciona el idioma de las plantillas');
			field.focus ();
			idValidate = false;
		} else {
			field.parent().removeClass('has-error');
			field.parent().find('.help-block').html('');
		}

		field = form.find ('#mass-mail-template-name-' + id);
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			field.parent().addClass('has-error');
			field.parent().find('.help-block').html('Selecciona la plantilla de correo');
			field.focus ();
			idValidate = false;
		} else {
			field.parent().removeClass('has-error');
			field.parent().find('.help-block').html('');
		}
		field = form.find ('#mass-mail-recipients-type-' + id);
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			field.parent().addClass('has-error');
			field.parent().find('.help-block').html('Selecciona el tipo de destinatarios');
			field.focus ();
			idValidate = false;
		} else {
			field.parent().removeClass('has-error');
			field.parent().find('.help-block').html('');
		}

		parameters = form.find ('.parameter');
		parameters.removeClass('has-error')
		if (parameters.length === 0 && idValidate) {
			return true;
		}
		for (i = 0; i < parameters.length; i += 1) {
			parameter = jQuery (parameters [ i ]);

			field = parameter.find ('.parameter-type');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				//alert ('Selecciona el tipo de parámetro');
				jQuery (parameters [ i ]).addClass('has-error')
				field.focus ();
				idValidate = false;
			}

			field = parameter.find ('.parameter-value[disabled!="disabled"]');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				//alert ('Selecciona la fórmula del valor del parámetro');
				jQuery (parameters [ i ]).addClass('has-error')
				field.focus ();
				idValidate = false;
			}
		}

		return idValidate;
	};

	// Public methods

	var createActivity = function (moduleName, viewId) {
		var selectedRecords = jQuery ('form#massdelete'+ idTab).find ('.view-item:checked'),
			arguments, i, selectedRecordIds;

		if ((selectedRecords.val () === null) || (selectedRecords.val () === undefined) || (selectedRecords.val ().trim () === '')) {
			alert ('Debes seleccionar algún registro para crearle una tarea asociada');
			return;
		}

		arguments = [
			'module=Calendar',
			'action=EditView',
			'return_module=' + encodeURIComponent (moduleName),
			'return_action=ListView',
			'return_viewname=' + encodeURIComponent (viewId)
		];

		selectedRecordIds = [];
		for (i = 0; i < selectedRecords.length; i += 1) {

			selectedRecordIds.push (jQuery (selectedRecords [ i ]).val ());
		}
		arguments.push ('idlist=' + selectedRecordIds.join (';'));
		window.location.href = 'index.php?' + arguments.join ('&');
	};

	var openFieldsModal = function (buttonElement) {
		openAuxiliaryModal (buttonElement, MODAL_TYPE_FIELDS);
	};

	var openMassEditModal = function (moduleName, id) {
        idTab = (id !== undefined) ? '-' + id : '';
		var selectedRecords = jQuery ('form#massdelete'+ idTab).find ('#allselectedboxes'+ idTab),
			arguments;

		if ((selectedRecords.val () === null) || (selectedRecords.val () === undefined) || (selectedRecords.val ().trim () === '')) {
			alert ('Debes seleccionar algún registro para editar');
			return;
		}

		arguments = [
			'module=' + encodeURIComponent (moduleName),
			'action=MassEdit',
			'Popup=true'
		];
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'html',
			method:   'get'
		}).done (onGetMassEditFieldsSuccessHandler).fail (onFailureHandler);
	};

	var openMassMailModal = function (moduleName, id) {
        idTab = (id !== undefined) ? '-' + id : '';
		var selectedRecords = jQuery ('form#massdelete'+ idTab).find ('#allselectedboxes'+ idTab),
			arguments = {};

		if ((selectedRecords.val () === null) || (selectedRecords.val () === undefined) || (selectedRecords.val ().trim () === '')) {
			alert ('Debes seleccionar algún registro para enviar correo masivo');
			return;
		}
		arguments = {
			'module':     encodeURIComponent (moduleName),
			'action':     'MassMail',
			'function':   'OPEN_MODAL',
			'record_ids': selectedRecords.val ().trim (),
			'Ajax':       true
		}

		jQuery.post ('index.php', arguments, function (data) {
			var message;
			try {
				message = JSON.parse (JSON.stringify (data));
				if(message.error !== 'OK') {
				throw message.error;
				} else {
					modal = jQuery (message.html);
					modal.modal ({ backdrop: 'static' }).on ('hidden.bs.modal', destroyModal);
				}
			} catch (e) {
				alert(e);
			}
		});
	};

	var openVariablesModal = function (buttonElement) {
		openAuxiliaryModal (buttonElement, MODAL_TYPE_VARIABLES);
	};

	var sendEmail = function (formElement, id) {
		var form = jQuery (formElement);
		if (!validateForm (form, id)) {
			return;
		}

		jQuery.ajax ('index.php', {
			data:     form.serialize (),
			dataType: 'json',
			method:   'post'
		}).done (function (response) {
			var recordId, totalSent, totalProcessed, errors, message;
			if (!response) {
				alert ('No ha recibido una respuesta del servidor. Intenta más tarde');
				return;
			}

			totalProcessed = 0;
			totalSent = 0;
			errors = [];
			for (recordId in response) {
				if (!response.hasOwnProperty (recordId)) {
					continue;
				}

				if (response [ recordId ] === 'OK') {
					totalSent += 1;
				} else {
					errors.push (response [ recordId ]);
				}
				totalProcessed += 1;
			}
			message = 'Resultados de la operación: ' + totalProcessed + ' registros procesados, ' + totalSent + ' correos enviados, ' + errors.length + ' errores';
			if (errors.length > 0) {
				message += ("\nErrores:\n" + errors.join ("\n"));
			}

			alert (message);
			modal.modal ('hide');
		}).fail (onFailureHandler);
	};

	var setParameterValue = function (selectElement) {
		var element   = jQuery (selectElement),
			type      = element.val (),
			parameter = element.closest ('.parameter');
		parameter.find ('.parameter-value[data-type!="' + type + '"]').hide ().attr ('disabled', 'disabled').closest ('.variable').hide ();
		parameter.find ('.parameter-value[data-type="' + type + '"]').show ().removeAttr ('disabled').closest ('.variable').show ();
	};

	var setModuleOrigen = function (obj, id) {
		var module        = jQuery (obj),
			currentModule = module.attr('data-current-module'),
			record        = jQuery ('#module_related_record' + id),
			dummy,label;
		if (module.val() !== '') {
			dummy = module.val ().split('@');
			module.attr('data-referenced-module', dummy[0]);
			label = module.find('option:selected').text();
			module.attr('data-title', label);
			record.val('');
			RelatedModuleModalUtils.openModal (obj)
			arguments = {
				'module':     encodeURIComponent (currentModule),
				'flmodule':   encodeURIComponent (dummy[0]),
				'action':     'MassMail',
				'function':   'FETCH_FIELDS',
				'Ajax':       true
			}
			jQuery.post ('index.php', arguments, function (data) {
				var message;
				try {
					message = JSON.parse (JSON.stringify (data));
					if(message.error !== 'OK') {
						throw message.error;
					} else {
						jQuery ('.source-module-' + id).html(message.html);
					}
				} catch (e) {
					alert(e);
				}
			});
		} else {
			module.attr('data-referenced-module', '');
			module.attr('data-title', '');
			record.val('')
		}
	}

	var setTemplateOptions = function (selectElement, id) {
		var language         = jQuery (selectElement).val (),
			variablesSection = jQuery ('#mass-mail-variables-section-' + id),
			variablesDiv	 = jQuery ('#mass-mail-variables-' + id),
			templateOption   = jQuery ('#mass-mail-template-name-' + id);

		if ((language === null) || (language === undefined) || (language.trim () === '')) {
			templateOption.find('option').each(function() {
				var theLanguage = jQuery (this).attr('data-language');
				if (theLanguage !== '') {
					jQuery (this).addClass ('hide');
				}
				variablesSection.addClass ('hide');
				variablesDiv.empty ();
			});
		} else {
			templateOption.find('option').each(function() {
				var theLanguage = jQuery (this).attr('data-language');
				if ((theLanguage === language) || (theLanguage === '')) {
					jQuery (this).removeClass('hide')
				} else {
					jQuery (this).addClass('hide')
				}
			});
		}
	};

	var setVariableOptions = function (selectElement, id) {
		var templateElement      = jQuery (selectElement),
			templateVars         = templateElement.find ('option:selected').attr ('data-variables'),
			templateName         = templateElement.val (),
			language             = jQuery ('#mass-mail-language-' + id).val (),
			relatedModule        = jQuery ('#module_related-' + id),
			relatedRecordDisplay = jQuery ('#module_related_record' + id + '_display'),
			variablesSection     = jQuery ('#mass-mail-variables-section-' + id),
			variablesDiv		 = jQuery ('#mass-mail-variables-' + id),
			variableTemplateHtml = jQuery ('#mass-mail-modal-template-variable-' + id).html (),
			fieldsOptions, fieldName, variables, variableTemplate, variableOptions, i;

		variablesDiv.empty ();
		relatedModule.val('');
		relatedRecordDisplay.val('');
		if (
			(templateVars === null) || (templateVars === undefined) || (templateVars.trim () === '') ||
			(language === null) || (language === undefined) || (language.trim () === '') ||
			(templateName === null) || (templateName === undefined) || (templateName.trim () === '')
		) {
			variablesSection.addClass('hide');
			return;
		}

		variables = templateVars.split (';');
		if (variables === null) {
			return;
		}

		variableOptions = [];
		for (i = 0; i < variables.length; i += 1) {
			fieldsOptions = [];
			for (fieldName in fields) {
				if (!fields.hasOwnProperty (fieldName)) {
					continue;
				}

				fieldsOptions.push (
					jQuery ('<option></option>')
						.text (fields [ fieldName ])
						.val (fieldName)
				);
			}

			variableTemplate = jQuery (variableTemplateHtml);
			variableTemplate.find ('.variable-name').val (variables [ i ]);
			variableTemplate.find ('.parameter-type').val ('').attr ('name', 'variables[' + variables [ i ] + '][type]');
			variableTemplate.find ('.parameter-value[data-type="SOURCE FIELD"]').append (fieldsOptions);
			variableTemplate.find ('.parameter-value').val ('').attr ('name', 'variables[' + variables [ i ] + '][value]').prop ('disabled', true).hide ();
			variableTemplate.find ('.variable').hide ();
			variableOptions.push (variableTemplate);
		}
		variablesDiv.append (variableOptions);
		variablesSection.removeClass('hide');
	};

	var setVariableValue = function (value) {
		var field;

		if (auxiliaryModalTriggerButton === null) {
			return;
		}

		field = auxiliaryModalTriggerButton.closest ('.variable').find ('.parameter-value');
		field.val (field.val () + value);
		auxiliaryModal.modal ('hide');
	};

	window.MassActionsUtils = {
		createActivity:     createActivity,
		openFieldsModal:    openFieldsModal,
		openMassEditModal:  openMassEditModal,
		openMassMailModal:  openMassMailModal,
		openVariablesModal: openVariablesModal,
		sendEmail:          sendEmail,
		setParameterValue:  setParameterValue,
		setModuleOrigen:    setModuleOrigen,
		setTemplateOptions: setTemplateOptions,
		setVariableOptions: setVariableOptions,
		setVariableValue:   setVariableValue
	};

    var onDocumentReadyHandler = function () {
       // var scripts     = document.getElementsByTagName ('script');
       // parseQuery (scripts);
    };
    jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));
