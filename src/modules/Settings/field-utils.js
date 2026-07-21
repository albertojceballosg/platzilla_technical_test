(function (jQuery) {
	// Private variables

	var modal = null,
		maxLength = 255;

	// Private functions

	var destroyModal = function () {
		if (modal === null) {
			return;
		}

		jQuery (this).remove ();
		modal = null;
	};

	var getNormalizedText = function (value) {
		var from = 'àáäâèéëêìíïîòóöôùúüûñç·/-,:;',
			to   = 'aaaaeeeeiiiioooouuuunc______',
			i, l;

		value = value.toLowerCase ().replace (' ', '_');

		// remove accents, swap ñ for n, etc
		for (i = 0, l = from.length; i < l; i++) {
			value = value.replace (new RegExp (from.charAt (i), 'g'), to.charAt (i));
		}

		value = value.replace (/[^a-z0-9 _]/g, '').replace (/\s+/g, '_').replace (/-+/g, '_');
		return value;
	};

	var validateFieldForm = function (form) {
		var fieldDefinitions = form.find ('.field-definitions .field-definition'),
			dataType         = form.find ('#field-type').val (),
			fieldDefinition, i, field, value, label;
		if ((dataType === null) || (dataType === undefined) || (dataType.trim () === '')) {
			alert ('Selecciona el tipo de campo');
			return false;
		}

		dataType = parseInt (dataType);
		for (i = 0; i < fieldDefinitions.length; i += 1) {
			fieldDefinition = jQuery (fieldDefinitions [ i ]);
			if (jQuery.inArray (dataType, fieldDefinition.data ('types')) === -1) {
				continue;
			}

			field = fieldDefinition.find ('.form-control');
			value = field.val ();
			// El campo de fecha por defecto puede estar vacío
			if (field.attr('id') === 'field-default-date') {
				continue;
			}
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				label = fieldDefinition.find ('label').text ();
				alert (label + ' no puede ser vacío');
				field.focus ();
				return false;
			}
		}

		return true;
	};

	// Public functions

	var deleteField = function (moduleName, fieldName) {
		var arguments;

		if (!confirm ('¿Estás seguro que quieres eliminar el campo seleccionado?')) {
			return;
		}

		arguments = [
			'module=Settings',
			'action=DeleteField',
			'modulename=' + encodeURIComponent (moduleName),
			'fieldname=' + encodeURIComponent (fieldName),
			'Ajax=true'
		];
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'json',
			method:   'post'
		}).done (function () {
			alert ('El campo ha sido eliminado');
			window.location.reload ();
		}).fail (function (jQueryResponse) {
			var message;
			try {
				message = JSON.parse (jQueryResponse.responseText);
			} catch (e) {
				message = 'Se ha presentado un error. Intenta más tarde';
			}
			alert (message);
		});
	};

	var hideFieldLabelForm = function (formElement) {
		var form     = jQuery (formElement),
			oldLabel = form.closest ('.field-label').find ('.old-label'),
            fieldType = form.closest ('.field-label').find ('.label-default');
		form.addClass ('hidden').find ('.new-label').val (oldLabel.text ());
		oldLabel.removeClass ('hidden');
        fieldType.removeClass ('hidden');
	};

	var normalizeFieldAlphabets = function (fieldElement, e) {
        var field      = jQuery (fieldElement),
            key        = e.keyCode,
            maxLength  = 30;

        if (e.shiftKey || e.ctrlKey || e.altKey) {
            e.preventDefault();
        } else {
            console.log(key);
            if (!((key == 8) || (key == 32) || (key == 46) || (key >= 35 && key <= 40) || (key >= 65 && key <= 90))) {
                console.log(key);
                e.preventDefault();
            }
        }
    };

	var normalizeFieldContents = function (fieldElement, e) {
		var fieldLabel = jQuery (fieldElement),
			fieldName  = jQuery ('#field-name'),
			maxLength  = 29;

        if ((e.ctrlKey === true) ||
			(e.metaKey === true) ||
			(e.keyCode === 18) ||
			((e.shiftKey === true) && ((e.keyCode >= 47) && (e.keyCode <= 57))) ||
			(e.keyCode <= 47 && e.keyCode !== 32 && e.keyCode !== 8) ||
			(e.keyCode >= 91 && e.keyCode !== 173)
		) {
            e.preventDefault ();
        } else if (fieldLabel.val().length > maxLength){
            fieldLabel.parent().addClass ('has-error');
		} else {
            fieldLabel.parent().removeClass ('has-error');
		}
        fieldName.val (getNormalizedText (fieldLabel.val ()));
	};

	var normalizeFieldLength = function (fieldElement, e) {
        var fieldLength = jQuery (fieldElement),
			maxValue    = maxLength;

        if ((e.ctrlKey === true) ||
            (e.metaKey === true) ||
            (e.keyCode <= 47  && e.keyCode !== 8) ||
            (e.keyCode >= 58)
        ) {
            e.preventDefault ();
        }

        if (parseInt (fieldLength.val ()) > maxValue) {
            fieldLength.parent().addClass ('has-error');
		} else {
            fieldLength.val (parseInt (fieldLength.val ()).toString ());
            fieldLength.parent().removeClass ('has-error');
		}
    };

	var openModal = function (blockId) {
		var modalTemplate = jQuery ('#field-modal-template');

		modal = jQuery (modalTemplate.html ());
		modal.find ('input[name="blockid"]').val (blockId);
		modal.modal ({ backdrop: 'static' }).on ('hidden.bs.modal', destroyModal);
	};

	var saveField = function (formElement) {
		var form       = jQuery (formElement),
			btnSubmmit = jQuery ('#field-utils-submmit'),
			btnClose   = jQuery ('#field-utils-close'),
			field      = jQuery ('#field-label').val (),
			helpBlock  = jQuery ('#field-utils-help-block');

		if (!validateFieldForm (form)) {
            helpBlock.html ('');
			return;
		}
        helpBlock.html ('Estamos creando el campo: <b>' + field + '</b><br>Por favor espere un momento...');
		btnSubmmit.attr ('disabled', 'disabled');
		btnClose.attr  ('disabled', 'disabled');
		jQuery.ajax ('index.php', {
			data:     form.serialize (),
			dataType: 'json',
			method:   'post'
		}).done (function () {
			alert ('El campo ha sido creado');
			modal.modal ('hide');
			window.location.reload ();
		}).fail (function (jQueryResponse) {
			var message;
            helpBlock.html ('');
            btnSubmmit.attr ('disabled', false);
            btnClose.attr  ('disabled', false);
			try {
				message = JSON.parse (jQueryResponse.responseText);
			} catch (e) {
				message = 'Se ha presentado un error. Intenta más tarde';
			}
			alert (message);
		});
	};

	var saveFieldLabel = function (formElement) {
		var form     = jQuery (formElement),
			oldLabel = form.closest ('.field-label').find ('.old-label'),
            fieldType = form.closest ('.field-label').find ('.label-default'),
			newLabel = form.find ('.new-label');
		if (newLabel.val () === oldLabel.text ()) {
			hideFieldLabelForm (formElement);
			return;
		}

		jQuery.ajax ('index.php', {
			data:     form.serialize (),
			dataType: 'json',
			method:   'post'
		}).done (function () {
			oldLabel.text (newLabel.val ());
			form.addClass ('hidden');
			oldLabel.removeClass ('hidden');
            fieldType.removeClass ('hidden');
		}).fail (function (jQueryResponse) {
			var message;
			try {
				message = JSON.parse (jQueryResponse.responseText);
			} catch (e) {
				message = 'Se ha presentado un error. Intenta más tarde';
			}
			alert (message);
		});
		return false;
	};

	var setSelectedFieldType = function (fieldTypeItemElement, dataType) {
		var fieldTypeItem    = jQuery (fieldTypeItemElement),
			fieldTypesList   = fieldTypeItem.closest ('.field-types'),
			fieldDefinitions = fieldTypeItem.closest ('.field-container').find ('.field-definitions .field-definition'),
			fieldLength      = fieldTypeItem.closest ('.field-container').find('#field-length'),
        i, fieldDefinition;

		for (i = 0; i < fieldDefinitions.length; i += 1) {
			fieldDefinition = jQuery (fieldDefinitions [ i ]);
			if (jQuery.inArray (dataType, fieldDefinition.data ('types')) !== -1) {
				fieldDefinition.find ('.form-control').prop ('disabled', false);
				fieldDefinition.removeClass ('hidden');
                if (dataType === 7) {
                    fieldLength.attr ('max', '65');
                    maxLength = 65;
                    fieldLength.parent ().find('span').eq (0).html('Solo admite números, máximo valor 65')
                } else {
                    fieldLength.attr ('max', '255');
                    maxLength = 255;
                    fieldLength.parent ().find('span').eq (0).html('Solo admite números, máximo valor 255')
				}
				// Si es un campo de fecha (uitype 5), establecer expresión TODAY por defecto
				if (dataType === 5) {
					jQuery('#field-default-date').val('TODAY');
				}
			} else {
				fieldDefinition.find ('.form-control').prop ('disabled', true);
				fieldDefinition.addClass ('hidden');
			}
		}

		fieldTypesList.find ('.field-type').removeClass ('selected');
		fieldTypeItem.addClass ('selected');
		modal.find ('#field-type').val (dataType);
	};

	var showFieldLabelForm = function (labelElement) {
		var label = jQuery (labelElement),
			form  = label.closest ('.field-label').find ('.field-label-form'),
			fieldType = label.closest ('.field-label').find ('.label-default');
		label.addClass ('hidden');
		fieldType.addClass ('hidden');
		form.removeClass ('hidden');
	};

	var updateModal = function(id) {
		modal = jQuery('#help-add-field-' + id);
	};

	window.FieldUtils = {
		deleteField:             deleteField,
        normalizeFieldAlphabets: normalizeFieldAlphabets,
		hideFieldLabelForm:      hideFieldLabelForm,
		normalizeFieldContents:  normalizeFieldContents,
        normalizeFieldLength:    normalizeFieldLength,
		openModal:              openModal,
		saveField:              saveField,
		saveFieldLabel:         saveFieldLabel,
		setSelectedFieldType:   setSelectedFieldType,
		showFieldLabelForm:     showFieldLabelForm,
		updateModal:            updateModal
	};
} (jQuery));