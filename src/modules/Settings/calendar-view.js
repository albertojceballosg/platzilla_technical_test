(function (jQuery) {
	var ruleSequence = 0;

	var setColorPicker = function (field) {
		field.ColorPicker (
			{
				onSubmit:     function (hsb, hex, rgb, el) {
					jQuery (el)
						.css ({ backgroundColor: '#' + hex, color: '#' + hex })
						.val ('#' + hex)
						.ColorPickerHide ();
				},
				onBeforeShow: function () {
					jQuery (this).ColorPickerSetColor (this.value);
				}
			}
		).bind (
			'keyup',
			function () {
				jQuery (this).ColorPickerSetColor (this.value);
			}
		);
	};

	var addRule = function () {
		var body      = jQuery ('table.rules').find ('tbody'),
			totalRow  = body.find('tr').length,
            rowId     = Math.floor(Math.random() * 500) + 1,
			grupClass = (totalRow > 0) ? 'rule-group' : '',
			template  = jQuery ('#rule-template').html ()
				.replace(/__RULE_GROUP__/g, grupClass)
                .replace(/__ID__/g, ruleSequence)
				.replace(/__ID_ROW__/g, rowId),
			row       = jQuery (template);
		setColorPicker (row.find ('.color'));
		body.append (row);
        ruleSequence++;
	};

	var addGroupRule = function (obj, seq) {
        var btn      = jQuery (obj),
			thisRow  = btn.parent().parent(),
			body     = jQuery ('table.rules').find ('tbody'),
            rowId     = Math.floor(Math.random() * 1000) + 1,
            template = jQuery ('#rule-template').html ()
				.replace(/__RULE_GROUP__/g, '')
                .replace(/__ID__/g, seq)
				.replace(/__ID_ROW__/g, rowId),
            row      = jQuery (template);
        row.find ('.color').addClass ('hide');

        thisRow.find ('.glue').removeAttr ('disabled');
        btn.addClass ('hide');
        row.insertAfter(thisRow);
        ////body.append (row);
    };

	var deleteRule = function (button, id) {
		var buttonElement = jQuery (button),
			row           = jQuery ('#' + id),
			prevRow       = row.prev (),
			nextRow       = row.next (),
			rules         = buttonElement.closest ('table.rules'),
			lastRow       = rules.find('tr').last();
		if (!confirm ('¿Estás seguro de borrar la regla seleccionada?')) {
			return;
		}
		if ((nextRow.hasClass('rule-group')) || (nextRow.attr('id') === undefined || nextRow.attr('id') === 'undefined')) {
            prevRow.find ('button').eq(0).removeClass('hide');
            prevRow.find ('.glue').attr ('disabled', true);
		}

		row.remove ();
	};

	var deleteView = function (label) {
		return confirm ('¿Estás seguro de borrar la vista "' + label + '"?')
	};

	var reload = function (select) {
		var moduleElement = jQuery (select),
			moduleName    = moduleElement.val (),
			viewId        = moduleElement.closest ('form').find ('input[name="record"]').val (),
			arguments;
		if ((moduleName === undefined) || (moduleName === null) || (moduleName.trim () === '')) {
			return;
		}

		arguments = [
			'module=Settings',
			'action=CalendarViewEditView',
			'modulename=' + encodeURIComponent (moduleName)
		];
		if ((viewId !== undefined) && (viewId !== null) && (viewId.trim () !== '')) {
			arguments.push ('record=' + encodeURIComponent (viewId));
		}
		window.location.href = 'index.php?' + arguments.join ('&');
	};

    var deleteViewX = function (label) {
        return confirm ('¿Marcar la vista "' + label + '" como la vista por defecto?')
    };

	var setModuleNameField = function (select, id) {
		var fieldElement   = jQuery (select),
			selectedOption = fieldElement.find ('option:selected'),
			uiType         = parseInt (selectedOption.attr('data-uity')),
			flmodule       = selectedOption.attr ('data-modulename'),
			inputField     = jQuery ('#rule-input-value-' + id),
			selectField    = jQuery ('#rule-select-value-' + id);
        inputField.val ('');
        selectField.val ('');
		if ((selectedOption === undefined) || (selectedOption === null)) {
			inputField.removeClass('hide').prop ('disabled', false).datepicker ('remove');
			selectField.addClass('hide').prop ('disabled', 'disabled');
			return;
		} else {
            if ((jQuery.inArray (uiType, [5, 6, 70]) !== -1)) {
                inputField.removeClass('hide').prop ('disabled', false).datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
                selectField.addClass('hide').prop ('disabled', 'disabled');
            } else if ((jQuery.inArray (uiType, [15]) !== -1)) {
                inputField.addClass('hide').prop ('disabled', 'disabled').datepicker ('remove');
                selectField.removeClass('hide').prop ('disabled', false);
                var arguments = {
                    'module':    'Settings',
                    'action':    'CalendarAjaxUtils',
                    'fieldname': selectedOption.val (),
					'flmodule':  flmodule,
                    'function':  'FETCH-PICKLIST',
                    'Ajax':      'true'
                };
                jQuery.post('index.php', arguments, function (data) {
                    var message;
                    console.log(data);
                    try {
                        message = JSON.parse(JSON.stringify(data));
                        if (message.error !== 'OK') {
                            throw message.error;
                        } else {
                            selectField.empty();
                            selectField.append(message.html)
                        }
                    }
                    catch (e) {
                        alert(e);
                    }
                });
            } else if ((jQuery.inArray (uiType, [53]) !== -1)) {
                inputField.addClass('hide').prop ('disabled', 'disabled').datepicker ('remove');
                selectField.removeClass('hide').prop ('disabled', false);
                var arguments = {
                    'module':    'Settings',
                    'action':    'CalendarAjaxUtils',
                    'fieldname': selectedOption.val (),
                    'flmodule':  flmodule,
                    'function':  'FIELD_TYPE_OWNER',
                    'Ajax':      'true'
                };
                jQuery.post('index.php', arguments, function (data) {
                    var message;
                    try {
                        message = JSON.parse (JSON.stringify (data));
                        if (message.error !== 'OK') {
                            throw message.error;
                        } else {
                            selectField.empty();
                            selectField.append(message.html)
                        }
                    }
                    catch (e) {
                        alert (e);
                    }
                });
            } else if (jQuery.inArray (uiType, [ 56 ]) !== -1) {
                inputField.addClass('hide').prop ('disabled', 'disabled').datepicker ('remove');
                selectField.removeClass('hide').prop ('disabled', false);
                selectField.empty();
                selectField.append (
                    jQuery ('<option>', {
                            value:1,
                            text: 'Si'
                        }
                    )
                );
                selectField.append (
                    jQuery ('<option>', {
                            value:0,
                            text: 'No'
                        }
                    )
                );
			} else {
                inputField.removeClass('hide').prop ('disabled', false).datepicker ('remove');
                selectField.addClass('hide').prop ('disabled', 'disabled').empty();
			}
		}

		fieldElement.closest ('.field-container').find ('.fieldmodulename').val (flmodule);
	};

    var selectTitleView = function (obj) {
        var fieldTite      = jQuery (obj),
            selectedOption = fieldTite.find ('option:selected'),
            flmodule       = selectedOption.attr ('data-modulename'),
            subTitle       = jQuery ('#subtitlefieldname');
        subTitle.val ('');
        subTitle.find('option').each(function () {
            var thisOption = jQuery(this),
                theModule  = thisOption.attr('data-modulename');
            if (theModule === flmodule) {
                thisOption.removeAttr("disabled");
            } else {
                thisOption.attr("disabled", true);
            }
        });
        fieldTite.closest ('.field-container').find ('.fieldmodulename').val (flmodule);
    };

	var validateForm = function () {
		var form           = jQuery ('form[name="CalendarView"]'),
			showInCalendar = parseInt (form.find ('input[name="showincalendar"]:checked').val ()),
			rules          = jQuery ('.table.rules').find ('.rule'),
			n              = rules.length,
			rule, field, value, i;

		if (!showInCalendar) {
			return true;
		}

		field = form.find ('.from');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Selecciona el campo que contiene la fecha de inicio que se mostrará en el calendario');
			field.focus ();
			return false;
		}

		field = form.find ('.title');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Selecciona el campo que contiene el título mostrará en el calendario');
			field.focus ();
			return false;
		}

		field = form.find ('#applicationcodes');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Selecciona las aplicaciones para las cuales estará disponible la vista');
			field.focus ();
			return false;
		}

		if (rules.length === 0) {
			return true;
		}

		for (i = 0; i < n; i += 1) {
			rule = jQuery (rules [ i ]);

			field = rule.find ('.rule-field');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				alert ('Selecciona el campo a comparar');
				field.focus ();
				return false;
			}

			field = rule.find ('.rule-operator');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				alert ('Selecciona el operador de comparación');
				field.focus ();
				return false;
			}

			field = rule.find ('.rule-value');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				alert ('Introduce el valor a comparar');
				field.focus ();
				return false;
			}
		}

		return true;
	};

	window.CalendarUtils = {
		addRule:            addRule,
        addGroupRule:       addGroupRule,
    	deleteRule:         deleteRule,
		deleteView:         deleteView,
		reload:             reload,
		setModuleNameField: setModuleNameField,
        selectTitleView:    selectTitleView,
		validateForm:       validateForm
	};

	var onDocumentReadyHandler = function () {
		var field = (jQuery ('.color'));
		if (field.length === 0) {
			return;
		}
		setColorPicker (field);
		ruleSequence = parseInt (jQuery ('table.rules').attr ('data-ruler'));
	};

	jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));
