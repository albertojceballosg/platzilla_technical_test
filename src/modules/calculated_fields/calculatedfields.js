(function (jQuery) {
	// Private variables
	var fLabels = [];
	var hfLabels = [];
	var typeofdata = [];
	var cloneGroup = '';
	var moduleData = '';

	// Private methods
	var validateForm = function (step) {
		jQuery ('span[id ^= cf-]').html ('');
		jQuery ('div[id ^= cf-dv-]').removeClass ('has-error');
		var field, isValidate = true;
		switch (step) {
			case "1":
				if (!jQuery ('#descrition').val ()) {
					jQuery ('#cf-description').html ('Escriba una descripción para el elemento calculado');
					jQuery ('#cf-dv-descrip').addClass ('has-error');
					isValidate = false;
				}

				if (!jQuery ('#title').val ()) {
					jQuery ('#cf-title').html ('Especifique el título para el elemento calculado');
					jQuery ('#cf-dv-title').addClass ('has-error');
					isValidate = false;
				}

				break;
			case "2":
				jQuery ('li[id ^= row-]').each (function (index, item) {

					jQuery (item).find ('span').eq (0).html ('');
					jQuery (item).find ('span').eq (1).html ('');
					jQuery (item).find ('span').eq (2).html ('');

					if (jQuery (item).find ('select').eq (0).val () == '') {
						jQuery (item).find ('select').eq (0).parent ().addClass ('has-error');
						jQuery (item).find ('span').eq (0).html ('La variable es requerida');
						isValidate = false;
					} else {
						jQuery (item).find ('select').eq (0).parent ().removeClass ('has-error');
					}

					if (jQuery (item).find ('select').eq (1).val () == '') {
						jQuery (item).find ('select').eq (1).parent ().addClass ('has-error');
						jQuery (item).find ('span').eq (1).html ('El operador es requerido');
						isValidate = false;
					} else {
						jQuery (item).find ('select').eq (1).parent ().removeClass ('has-error');
					}

					if (jQuery (item).find ('input').eq (0).val () == '') {
						idfield = jQuery (item).find ('input').eq (0).attr ('id');
						jQuery (item).find ('input').eq (0).parent ().addClass ('has-error');
						jQuery (item).find ('span').eq (2).html ('El valor es requerido');
						isValidate = false;
					} else {
						jQuery (item).find ('input').eq (0).parent ().removeClass ('has-error');
					}
				});
				break;
            case "3":
                isValidate = false;
                break;
			default:
				isValidate = false;
		}
		return isValidate;
	};

	// Public methods
	var addFilterGroup = function (obj) {
		var conditionGroups        = jQuery ('.action-bar'),
			conditionGroupTemplate = jQuery (jQuery ('#condition-group-template').html ().replace (/__GROUP_ID__/g, totalFilterGroup)),
			conditionTemplate      = jQuery ('#condition-template').html ().replace (/__GROUP_ID__/g, totalFilterGroup); //.replace(/__CONDITION_ID__/g, -1)
		conditionGroupTemplate.find ('.conditions').append (conditionTemplate);
		conditionGroups.before (conditionGroupTemplate);
		totalFilterGroup += 1;
		totalFilterRow += 1;
		jQuery (obj).attr ('data-group', totalFilterGroup);
		jQuery ('#group-' + (totalFilterGroup - 2)).find ('.operator').removeClass ('hidden').removeAttr ('disabled');
	};

	var setFilterRecord = function (obj) {
		var elementRow   = jQuery (obj).parent (),
			elementGroup = jQuery (obj).parent ().parent ().parent (),
			lastInRecord = jQuery ('#inRecord'),
			selectData   = '',
			module       = '',
			field        = '';
        selectData = elementGroup.find('select').eq (0);
        field      = selectData.children ().children ('option:selected').val ();
        module     = selectData.children ().children ('option:selected').closest('optgroup').attr ('label');;
        console.log(module);
        
        // Corregir para campos grid: si module está vacío, usar el módulo del formulario
        if (!module || module === '') {
            // Para campos grid, obtener el módulo del campo hidden moduleId
            var moduleId = jQuery('#moduleId').val();
            if (!moduleId) {
                // Si no existe moduleId, buscar en los datos del formulario
                moduleId = jQuery('input[name="moduleId"]').val();
            }
            module = moduleId || 'orden_de_venta'; // fallback al módulo por defecto
            console.log('Campo grid detectado, usando módulo: ' + module);
        }
        
        if (lastInRecord.val () !== '') {
            if (confirm ('Esta operación eliminará la selección anterior, ¿desea continuar?')) {
                jQuery ('li[id ^= row-]').each (function (index, item) {
                    if (jQuery(item).find('input').eq(0).val() == '__RECORD__') {
                        jQuery(item).find('input').eq(0).removeAttr ('readonly').val ('');
                        jQuery(item).find('input').eq(0).parent().addClass('has-error');
                        jQuery(item).find('span').eq(2).html('El valor es requerido');
                    } else {
                        jQuery (item).find ('input').eq (0).parent ().removeClass ('has-error');
                        jQuery(item).find('span').eq(2).html('');
                    }
                });
                elementRow.find ('input').eq (0).val ('__RECORD__').attr('readonly', 'readonly');
                lastInRecord.val (module + '.' + field)

            }
        } else {
            elementRow.find ('input').eq (0).val ('__RECORD__').attr('readonly', 'readonly');
            lastInRecord.val (module + '.' + field)
		}

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

	var eraseFilterGroup = function (obj) {
		var elementGroup, thisGroup, idGroup, lastGroup,
			infoTexto = '¿Esás seguro de borrar el grupo de condiciones seleccionado?';
		thisGroup = jQuery (obj).parent ().parent ().parent ().parent ();
		idGroup = thisGroup.attr ('id');
		var r = confirm (infoTexto);
		if (r == true) {
			lastGroup = jQuery ('div.filter_goup').last ().attr ('id');
			if (idGroup == lastGroup) {
				thisGroup.prev ().find ('.operator').addClass ('hidden').removeAttr ('disabled');
				totalFilterGroup -= 1;
			}
			thisGroup.remove ();
		}
	};

	var eraseFilterValue = function (obj) {
			var elementRow = jQuery (obj).parent (),
				rowValue   = elementRow.find ('input').eq (0).val ();

			if (rowValue === '__RECORD__') {
				elementRow.find ('input').eq (0).removeAttr ('readonly');
			}
			elementRow.find ('input').eq (0).val ('');
			return false;
	};

	var goPrevStep = function (obj) {
		var step       = jQuery (obj).attr ('data-step'),
			formAction = jQuery ('#action'),
			form       = jQuery ('#calculate-field-form'),
			infoAction = 'Con esta operación perderá la configuración del filtro, ¿Desea continuar?';
		switch (step) {
			case "4":
				formAction.val ('addOperationCalculatedFields');
				break;
			case "3":
				formAction.val ('addFiltroCalculatedFields');
				break;
			case "2":
				formAction.val ('addCalculatedFields');
				break;
			default:
				return false;
		}
		form.submit ();
	};

	var setHelpToField = function (obj) {
		var elementRow       = '',
			selectedOperator = '',
			inputTextRow     = '';

		selectedOperator = jQuery (obj).val ();
		elementRow       = jQuery (obj).parent ().parent ();
        inputTextRow     = elementRow.find ('input').eq (0);

		if (inputTextRow.val () !== '__RECORD__') {
            inputTextRow.eq(0).val ('');
            inputTextRow.attr ('placeholder', hfLabels[selectedOperator]);
        }
	};

	var setCalculatedModule = function (obj) {
		var module       = jQuery (obj).val (),
			customFilter = jQuery ('#custonFilter').val ();
		infoAction = 'Con esta operación perderá la configuración del filtro, ¿Desea continuar?';
		if (customFilter != '') {
			var r = confirm (infoAction);
			if (r == true) {
				lastModuleId = module;
				jQuery ('#custonFilter').val ('');
			} else {
				jQuery (obj).val (lastModuleId);
			}
		} else {
			lastModuleId = module;
		}
	};

	var setFilterOperators = function (obj) {
		var filterRow    = '',
			selectedType = '',
			thisOperator = '';
		selectedType = jQuery (obj).children ().children ('option:selected').attr ('data-type');
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

	var setFilterRow = function (obj) {
		var elementRow, newElementRow, numRow, fieldSelect, totalRow;
		elementRow = jQuery (obj).parent ().parent ().parent ().find ('li:last-child');
		newElementRow = elementRow.clone ().attr ('id', 'row-' + totalFilterRow);
		elementRow.find ('select').eq (2).removeClass ('hidden').removeAttr ('disabled');
		newElementRow.find ('button').eq (0).removeClass ('hidden');
        newElementRow.find ('input').eq (0).removeAttr ('readonly').val ('');
		newElementRow.appendTo (elementRow.parent ());
		totalFilterRow += 1;
	};

	var validateRepeatData = function (obj) {
		var step          = jQuery (obj).attr ('data-step'),
			selectedField = jQuery ('#operationfieldId option:selected').text ();
        jQuery ('#operationfieldLabel').val (selectedField);

		if (!validateForm (step)) {
			jQuery ("html, body").animate ({ scrollTop: 0 }, 800);
			return false;
		} else {
			jQuery ('#calculate-field-form').submit ();
		}

	};

	window.CFUtils = {
		addFilterGroup:      addFilterGroup,
		eraseFilterGroup:    eraseFilterGroup,
		setFilterRecord:    setFilterRecord,
		eraseFilterRow:      eraseFilterRow,
		eraseFilterValue:    eraseFilterValue,
		goPrevStep:          goPrevStep,
		setHelpToField:      setHelpToField,
		setFilterOperators:  setFilterOperators,
		setCalculatedModule: setCalculatedModule,
		setFilterRow:        setFilterRow,
		validateRepeatData:  validateRepeatData
	};
	fLabels[ 'l' ] = alert_arr.LESS_THAN;
	fLabels[ 'g' ] = alert_arr.GREATER_THAN;
	fLabels[ 'm' ] = alert_arr.LESS_OR_EQUALS;

	var onDocumentReadyHandler = function () {
		if (typeof alert_arr !== "undefined") {
			fLabels[ 'e' ] = alert_arr.EQUALS;
			fLabels[ 'n' ] = alert_arr.NOT_EQUALS_TO;
			fLabels[ 's' ] = alert_arr.STARTS_WITH;
			fLabels[ 'ew' ] = alert_arr.ENDS_WITH;
			fLabels[ 'c' ] = alert_arr.CONTAINS;
			fLabels[ 'k' ] = alert_arr.DOES_NOT_CONTAINS;
			fLabels[ 'h' ] = alert_arr.GREATER_OR_EQUALS;
			fLabels[ 'bw' ] = alert_arr.BETWEEN;
			fLabels[ 'b' ] = alert_arr.BEFORE;
			fLabels[ 'a' ] = alert_arr.AFTER;
			hfLabels[ 'e' ] = 'texto o valor para comparar';
			hfLabels[ 'n' ] = 'texto o valor para comparar';
			hfLabels[ 's' ] = 'Comienza con el texto?';
			hfLabels[ 'ew' ] = 'Termina con el texto?';
			hfLabels[ 'c' ] = 'Contiene el texto?';
			hfLabels[ 'k' ] = 'No contiene el texto?';
			hfLabels[ 'l' ] = 'Valor o aaaa-mm-dd si es fecha';
			hfLabels[ 'g' ] = 'Valor o aaaa-mm-dd si es fecha';
			hfLabels[ 'm' ] = 'Valor o aaaa-mm-dd si es fecha';
			hfLabels[ 'h' ] = 'Valor o aaaa-mm-dd si es fecha';
			hfLabels[ 'bw' ] = 'inferior,superior o fechas: aaaa-mm-dd,aaaa-mm-dd';
			hfLabels[ 'b' ] = 'antes de aaaa-mm-dd';
			hfLabels[ 'a' ] = 'despues de aaaa-mm-dd';
		}

		typeofdata[ 'V' ] = [ 'e', 'n', 's', 'ew', 'c', 'k' ];
		typeofdata[ 'N' ] = [ 'e', 'n', 'l', 'g', 'm', 'h' ];
		typeofdata[ 'T' ] = [ 'e', 'n', 'l', 'g', 'm', 'h', 'bw', 'b', 'a' ];
		typeofdata[ 'I' ] = [ 'e', 'n', 'l', 'g', 'm', 'h' ];
		typeofdata[ 'C' ] = [ 'e', 'n' ];
		typeofdata[ 'D' ] = [ 'e', 'n', 'l', 'g', 'm', 'h', 'bw', 'b', 'a' ];
		typeofdata[ 'DT' ] = [ 'e', 'n', 'l', 'g', 'm', 'h', 'bw', 'b', 'a' ];
		typeofdata[ 'NN' ] = [ 'e', 'n', 'l', 'g', 'm', 'h' ];
		typeofdata[ 'E' ] = [ 'e', 'n', 's', 'ew', 'c', 'k' ];

	};
	jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));
