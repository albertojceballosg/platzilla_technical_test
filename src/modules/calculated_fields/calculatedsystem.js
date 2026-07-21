(function (jQuery) {
	// Protección contra carga múltiple
	if (window.CSUtils) {
		console.warn('calculatedsystem.js ya está cargado, evitando duplicación');
		return;
	}

	// Private variables
	var arrayClone     = [],
		lastTemplate   = 0,
		labels         = 'abcdefghijklmnopqrstuvwxyz',
		labelIndex     = 0,
		equation       = "",
		arrayLabel     = [],
		arrayFields    = [],
		relatedModules = [],
		lastModule     = jQuery ('#module-name').val ();

	// Private methods
	var destroyModal = function () {
		if (modal === null) {
			return;
		}

		jQuery (this).remove ();
		modal = null;
		modalTriggerButton = null;
	};

	var setFieldsOptions = function (group) {
		var firstOptionField  = '',
			secondOptionFiled = '',
			lastGroupLabel    = '',
			firstOptGroup     = '',
            secondOptGroup    = '',
			searchGroup;
		if (!Array.isArray (arrayFields) || arrayFields.length < 1) {
			return
		}
		if (group === '') {
			searchGroup = 'div[id ^= Group-]';
		} else {
			searchGroup = '#Group-' + group
		}

		jQuery (searchGroup).each (function (index, item) {
			firstOptionField = jQuery (item).find ('select').eq (1);
			secondOptionFiled = jQuery (item).find ('select').eq (6);
			firstOptionField.empty ();
			secondOptionFiled.empty ();
			firstOptionField.append (
				jQuery (
					'<option>',
					{
						value: '',
						text:  'Seleccione un campo'
					}
				)
			);
			secondOptionFiled.append (
				jQuery (
					'<option>',
					{
						value: '',
						text:  'Seleccione un campo'
					}
				)
			);
			jQuery.each (arrayFields,
				function (i, field) {
					if ((field === null) || (field === undefined) || (!(field instanceof Object)) || (jQuery.isEmptyObject (field))) {
						return;
					}

					if (lastGroupLabel !== field.module) {
                        // Usar module_label para mostrar etiqueta traducida, fallback a module si no existe
                        var moduleDisplayLabel = field.module_label ? field.module_label : field.module;
                        firstOptGroup = jQuery('<optgroup>', {label: moduleDisplayLabel});
                        firstOptionField.append (firstOptGroup);

                        secondOptGroup = jQuery('<optgroup>', {label: moduleDisplayLabel});
                        secondOptionFiled.append (secondOptGroup);

                        lastGroupLabel = field.module;
                        if (field.module !== lastModule) {
                            relatedModules.push(field.module);
                        }
                    }

					if (firstOptionField != '') {
                        firstOptGroup.append (
							jQuery (
								'<option>',
								{
									value: field.tablename + '.' + field.fieldname + '@' + field.uitype,
									text:  field.label
								}
							).attr ('data-uitype', field.uitype)
						);
					}
					if (secondOptionFiled != '') {
                        secondOptGroup.append (
							jQuery (
								'<option>',
								{
									value: field.tablename + '.' + field.fieldname + '@' + field.uitype,
									text:  field.label
								}
							).attr ('data-uitype', field.uitype)
						);
					}
				}
			);
			secondOptionFiled = null;
			firstOptionField = null;
		});
		console.log(relatedModules);
	};

	var isValidAllData = function (form) {
		jQuery ('span[id ^= cs-]').html ('');
		jQuery ('div[id ^= cs-dv-]').removeClass ('has-error');
		var field, isValidate = true;

		if (jQuery ('#title').val () === '') {
			jQuery ('#cs-title').html ('El título del cálculo es requerido');
			jQuery ('#cs-div-title').addClass ('has-error');
			isValidate = false;
		}

		if (jQuery ('#description').val () === '') {
			jQuery ('#cs-description').html ('La descripción del cálculo es requerida');
			jQuery ('#cs-div-description').addClass ('has-error');
			isValidate = false;
		}

		if (jQuery ('#module-name').val () === '') {
			jQuery ('#cs-modulename').html ('Seleccione un módulo');
			jQuery ('#cs-div-modulename').addClass ('has-error');
			isValidate = false;
		}

		jQuery ('div[id ^= Group-]').each (function (index, item) {

			if (!jQuery (item).find ('select').eq (1).parent ().hasClass ('hide')) {

				if (jQuery (item).find ('select').eq (1).val () == '') {
					jQuery (item).find ('select').eq (1).parent ().addClass ('has-error');
					jQuery (item).find ('select').eq (1).parent ().find ('span').eq (0).html ('Seleccione un campo de módulo');
					isValidate = false;
				} else {
					jQuery (item).find ('select').eq (1).parent ().removeClass ('has-error');
					jQuery (item).find ('select').eq (1).parent ().find ('span').eq (0).html ('')
				}

			}

			if (!jQuery (item).find ('select').eq (2).parent ().hasClass ('hide')) {

				if (jQuery (item).find ('select').eq (2).val () == '') {
					jQuery (item).find ('select').eq (2).parent ().addClass ('has-error');
					jQuery (item).find ('select').eq (2).parent ().find ('.help-block').html ('Seleccione un elemento calculado');
					isValidate = false;
				} else {
					jQuery (item).find ('select').eq (2).parent ().removeClass ('has-error');
					jQuery (item).find ('select').eq (2).parent ().find ('.help-block').html ('');
				}

			}

			if (!jQuery (item).find ('select').eq (3).parent ().hasClass ('hide')) {

				if (jQuery (item).find ('select').eq (3).val () == '') {
					jQuery (item).find ('select').eq (3).parent ().addClass ('has-error');
					jQuery (item).find ('select').eq (3).parent ().find ('span').eq (0).html ('Seleccione un cálculo previo');
					isValidate = false;
				} else {
					jQuery (item).find ('select').eq (3).parent ().removeClass ('has-error');
					jQuery (item).find ('select').eq (3).parent ().find ('span').eq (0).html ('')
				}
			}

			if (!jQuery (item).find ('select').eq (6).parent ().hasClass ('hide')) {

				if (jQuery (item).find ('select').eq (6).val () == '') {
					jQuery (item).find ('select').eq (6).parent ().addClass ('has-error');
					jQuery (item).find ('select').eq (6).parent ().find ('span').eq (0).html ('Seleccione un campo de módulo');
					isValidate = false;
				} else {
					jQuery (item).find ('select').eq (6).parent ().removeClass ('has-error');
					jQuery (item).find ('select').eq (6).parent ().find ('span').eq (0).html ('')
				}
			}

			if (!jQuery (item).find ('select').eq (7).parent ().hasClass ('hide')) {

				if (jQuery (item).find ('select').eq (7).val () == '') {
					jQuery (item).find ('select').eq (7).parent ().addClass ('has-error');
					jQuery (item).find ('select').eq (7).parent ().find ('.help-block').html ('Seleccione un elemento calculado');
					isValidate = false;
				} else {
					jQuery (item).find ('select').eq (7).parent ().removeClass ('has-error');
					jQuery (item).find ('select').eq (7).parent ().find ('.help-block').html ('');
				}

			}

			if (!jQuery (item).find ('select').eq (8).parent ().hasClass ('hide')) {

				if (jQuery (item).find ('select').eq (8).val () == '') {
					jQuery (item).find ('select').eq (8).parent ().addClass ('has-error');
					jQuery (item).find ('select').eq (8).parent ().find ('span').eq (0).html ('Seleccione un cálculo previo');
					isValidate = false;
				} else {
					jQuery (item).find ('select').eq (8).parent ().removeClass ('has-error');
					jQuery (item).find ('select').eq (8).parent ().find ('span').eq (0).html ('')
				}

			}

			if (!jQuery (item).find ('input').eq (0).parent ().hasClass ('hide')) {

				if (jQuery (item).find ('input').eq (0).val () == '') {
					jQuery (item).find ('input').eq (0).parent ().addClass ('has-error');
					jQuery (item).find ('input').eq (0).parent ().find ('span').eq (0).html ('El valor es requerido');
					isValidate = false;
				} else {
					jQuery (item).find ('input').eq (0).parent ().removeClass ('has-error');
					jQuery (item).find ('input').eq (0).parent ().find ('span').eq (0).html ('')
				}

			}

			if (!jQuery (item).find ('input').eq (1).parent ().hasClass ('hide')) {

				if (jQuery (item).find ('input').eq (1).val () == '') {
					jQuery (item).find ('input').eq (1).parent ().addClass ('has-error');
					jQuery (item).find ('input').eq (1).parent ().find ('span').eq (0).html ('El valor es requerido');
					isValidate = false;
				} else {
					jQuery (item).find ('input').eq (1).parent ().removeClass ('has-error');
					jQuery (item).find ('input').eq (1).parent ().find ('span').eq (0).html ('')
				}
			}
		});
		return isValidate;
	};

	var setGroup = function (obj) {
		var selectValue = '';
		selectValue = obj.val ();
		obj.find ('option')
		   .remove ()
		   .end ()
		   .append ('<option value="">Seleccionar</option>')
		   .val ('');
		for (i = 0; i < arrayLabel.length - 1; i++) {
			obj.append ('<option value="' + arrayLabel[ i ] + '">' + 'grupo ' + arrayLabel[ i ] + '</option>');

		}
		obj.val (selectValue);
	};

	// Public methods
	var addGroup = function (obj) {
		var nextGroup,
			cloneGroup,
			prevGroup              = jQuery (obj).parent ().prev (),
			label                  = labels [ labelIndex++ % labels.length ],
			conditionGroupTemplate = jQuery (jQuery ('#condition-group').html ()),
			arrayId                = prevGroup.attr ('id').split ('-');

		nextGroup = parseInt (arrayId[ 1 ]) + 1;
		conditionGroupTemplate.attr ('id', 'Group-' + nextGroup).find ('span').eq (0).html (label + ' = (');
		// Verificar si select2 está disponible antes de inicializarlo
		if (window.CSUtilsSelect2Ready === true && jQuery.fn.select2) {
			conditionGroupTemplate.find ('.search-element').select2 ({
				placeholder: 'Seleccione un elemento calculado'
			});
		}
		conditionGroupTemplate.insertAfter (prevGroup);
		prevGroup.find ('.join-condition').removeClass ('hide');
		arrayLabel.push (label);

		setFieldsOptions (nextGroup);
		upDateCalculatedGroup ();
	};

	var checkValue = function (e) {
		if (jQuery.inArray (e.keyCode, [ 46, 8, 9, 27, 13, 110, 190 ]) !== -1 ||
			(e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
			(e.keyCode >= 35 && e.keyCode <= 40 && e.keyCode === 188 )) {
			return;
		}
		if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
			e.preventDefault ();
		}
	};

	var eraseGroup = function (obj) {
		var myGroup   = jQuery (obj).parent ().parent ().parent (),
			myGroupId = myGroup.attr ('id'),
			lastGroup,
			groupName = myGroup.next ().find ('span').eq (0).html ().split (' = ') [ 0 ],
			question  = '¡Esta operación reiniciará las seleccion de Cálculos Previos! ¿Continuar?';

		if (confirm (question) == true) {
			jQuery.each (arrayLabel, function (i) {
				if (arrayLabel[ i ] === groupName) {
					arrayLabel.splice (i, 1);
					return false;
				}
			});
			myGroup.next ().remove ();
			lastGroup = jQuery ('div.cs-condicion-group').last ().attr ('id');

			if (myGroupId == lastGroup) {
				myGroup.find ('.join-condition').addClass ('hide');
			}
			jQuery ('div[id ^= Group-]').each (function (index, item) {
				jQuery (item).attr ('id', 'Group-' + (index + 1))
			});
			upDateCalculatedGroup ();
			jQuery ("html, body").animate ({ scrollTop: 0 }, 800);
		} else {
			return false;
		}
	};

	var selectOperator = function (obj) {
		console.log (obj);
		var element        = jQuery (obj).attr ('data-template'),
			selection      = jQuery (obj).val (),
			sectionOfGroup = jQuery (obj).parent ().parent (),
			elementName    = sectionOfGroup.find ('select').eq (2),
			valueName      = sectionOfGroup.find ('input').eq (0),
			referenceName  = sectionOfGroup.find ('select').eq (3),
			fieldName      = sectionOfGroup.find ('select').eq (1);

		sectionOfGroup.find ('.cs-dv-' + element + 'Element').addClass ('hide');
		sectionOfGroup.find ('.cs-dv-' + element + 'Value').addClass ('hide');
		sectionOfGroup.find ('.cs-dv-' + element + 'Reference').addClass ('hide');
		sectionOfGroup.find ('.cs-dv-' + element + 'Field').addClass ('hide');
		if (selection === '') {
			sectionOfGroup.find ('.cs-dv-' + element + 'Field').removeClass ('hide');
			return false
		} else if (selection == 'e') {
			sectionOfGroup.find ('.cs-dv-' + element + 'Element').removeClass ('hide');
			valueName.val ('');
			referenceName.val ('');
			fieldName.val ('');
		} else if (selection == 'v') {
			sectionOfGroup.find ('.cs-dv-' + element + 'Value').removeClass ('hide');
			referenceName.val ('');
			elementName.val ('');
			fieldName.val ('');
		} else if (selection == 'r') {
			sectionOfGroup.find ('.cs-dv-' + element + 'Reference').removeClass ('hide');
			elementName.val ('');
			fieldName.val ('');
			valueName.val ('')
		} else {
			sectionOfGroup.find ('.cs-dv-' + element + 'Field').removeClass ('hide');
			referenceName.val ('');
			elementName.val ('');
			valueName.val ('')
		}
	};

	var searchCalculated = function (obj) {
		var filter = jQuery (obj).val (),
			list   = jQuery ('.calculated-list');

		if (filter != '') {
			jQuery.expr[ ':' ].Contains = function (a, i, m) {
				return (a.textContent || a.innerText || "").toUpperCase ().indexOf (m[ 3 ].toUpperCase ()) >= 0;
			};

			list.find ("a:not(:Contains('" + filter + "'))").slideUp ();
			list.find ("a:Contains('" + filter + "')").slideDown ();
		} else {
			list.find ('a').slideDown ();
		}
		return false;
	};

	var setCalculatedSystem = function (obj) {
		var mySelection = jQuery (obj),
			selectionValues;
		mySelection.parent ().each (function (index, item) {
			jQuery (item).find ('a').removeClass ('active');
		});
		mySelection.addClass ('active');
		selectionValues = mySelection.attr ('rel');
		jQuery ('#calculatedSystemId').val (selectionValues);
	};

	var setOperator = function (obj) {
		var operator      = jQuery (obj),
			group         = jQuery (obj).parent ().parent ().next (),
			elementDiv    = group.find ('.cs-dv-secondElement'),
			valueDiv      = group.find ('.cs-dv-secondValue'),
			referenceDiv  = group.find ('.cs-dv-secondReference'),
			fieldDiv      = group.find ('.cs-dv-secondField'),
			type          = group.find ('select').eq (0),
			actualType    = type.val (),
			elementName   = group.find ('select').eq (2),
			valueName     = group.find ('input').eq (0),
			referenceName = group.find ('select').eq (3),
			fieldName     = group.find ('select').eq (1);
		if (operator.val () === 'x') {
			elementDiv.addClass ('hide');
			valueDiv.removeClass ('hide');
			referenceDiv.addClass ('hide');
			fieldDiv.addClass ('hide');
			referenceName.val ('');
			elementName.val ('');
			fieldName.val ('');
			group.find ('select').eq (6).val ('');
			group.find ('select').eq (7).val ('');
			group.find ('select').eq (8).val ('');
			valueName.val (1).attr ('readonly', true);
			type.val ('v');
			type.find ('option:not(:selected)').attr ('disabled', true);
		} else {
			valueName.val ('').attr ('readonly', false);
			type.val (actualType);
			type.find ('option:not(:selected)').attr ('disabled', false);
		}
	};

	var getCalculatedField = function (obj) {
		var selectedModule = jQuery (obj).val (),
			arguments      = [
				'module=calculated_fields',
				'action=calculated_fieldsAjax',
				'file=ajaxOption',
				'method=get_fields',
				'ajax=true',
				'modulename=' + encodeURIComponent (selectedModule)
			];
		if (selectedModule === '') {
			return
		}
		if (arrayFields.length > 0 || lastModule != '') {
			var r = confirm ('Con esta operación se reiniciaran los cálculos; ¿Continuar?');
			if (r == false) {
				jQuery (obj).val (lastModule);
				return;
			} else {
				relatedModules = [];
				jQuery ('div[id ^= Group-]').each (function (index, item) {
					if (index > 0) {
						jQuery (item).remove ()
					} else {
						var sectionOfGroup = jQuery (item),
							element        = [ 'first', 'second' ];
						sectionOfGroup.find ('select').eq (0).val ('c');
						sectionOfGroup.find ('select').eq (1).val ('');
						sectionOfGroup.find ('select').eq (2).val ('');
						sectionOfGroup.find ('select').eq (3).val ('');
						sectionOfGroup.find ('select').eq (5).val ('c');
						sectionOfGroup.find ('select').eq (6).val ('');
						sectionOfGroup.find ('select').eq (7).val ('');
						sectionOfGroup.find ('select').eq (8).val ('');
						sectionOfGroup.find ('input').eq (0).val ('');
						sectionOfGroup.find ('input').eq (1).val ('');
						for (var e = 0; e < element.length; e++) {
							sectionOfGroup.find ('.cs-dv-' + element [ e ] + 'Element').addClass ('hide');
							sectionOfGroup.find ('.cs-dv-' + element [ e ] + 'Value').addClass ('hide');
							sectionOfGroup.find ('.cs-dv-' + element [ e ] + 'Reference').addClass ('hide');
							sectionOfGroup.find ('.cs-dv-' + element [ e ] + 'Field').removeClass ('hide');
						}
					}
				});
			}
		}

		jQuery.ajax (
			'index.php',
			{
				data:     arguments.join ('&'),
				dataType: 'text',
				method:   'post'
			}
		).done (function (responseText) {
			if (responseText != 'null') {
                lastModule = selectedModule;
                arrayFields = JSON.parse(responseText);
                setFieldsOptions('');
                upDateCalculatedGroup()
            } else {
				alert ('¡No se encontraron campos numéricos!')
			}
		})

	};

	var lookEquation = function () {
		upDateCalculatedGroup ();
		jQuery ("html, body").animate ({ scrollTop: 0 }, 800);
	};

	var setCalculatedPattern = function (obj) {
		var action = jQuery (obj).attr ('id');

		if (action == 'action-duplicate') {
			jQuery ('#calculated-pattern').removeClass ('hide');
		} else {
			jQuery ('#calculated-pattern').addClass ('hide');
			jQuery ('#calculatedSystemId').val ('');
			jQuery ('.list-group-item').removeClass ('active');
		}
	};

	var openCreateCalculationModal = function (obj) {
		var button        = jQuery (obj),
			modalTemplate = jQuery ('#new-calculate-modal-template'),
			tasks         = button.closest ('.tab-pane').find ('.task-row'),
			i, n, options, option, task;

		modal = jQuery (modalTemplate.html ());

		modal.modal ({ backdrop: 'static' }).on ('hidden.bs.modal', destroyModal);
	};

	var upDateCalculatedGroup = function () {
		var equation      = "Cálculo =",
			selectValue   = '',
			operator      = "",
			nameGroup     = ' grupo ',
			dataGroup     = '',
			equationGroup = '',
			itemOne       = '',
			itemTwo       = '',
			operatorIn    = '',
			clone         = '';
		jQuery ('.cs-myGroup').remove ();
		jQuery ('#legend').addClass ('hide');

		var allGroups = jQuery ('div[id ^= Group-]');

		allGroups.each (function (index, item) {
			dataGroup = jQuery (item).children ();
			operatorIn = jQuery (dataGroup [ 1 ]).find ('select').eq (0).val ();
			itemOne = jQuery (dataGroup [ 0 ]).find ('div').not ('.hide').eq (1).find ('select').eq (0).find ('option:selected').text ();
			itemTwo = jQuery (dataGroup [ 2 ]).find ('div').not ('.hide').eq (1).find ('select').eq (0).find ('option:selected').text ();

			if (itemOne === '') {
				itemOne = jQuery (dataGroup[ 0 ]).find ('div').not ('.hide').eq (1).find ('input').eq (0).val ();
			}
			if (itemTwo === '') {
				itemTwo = jQuery (dataGroup[ 2 ]).find ('div').not ('.hide').eq (1).find ('input').eq (0).val ();

			}

			setGroup (jQuery (item).find ('select').eq (3));
			setGroup (jQuery (item).find ('select').eq (8));

			// Robust label resolution to avoid 'undefined' on save/display
			var currentLabel = arrayLabel[index];
			if (currentLabel === undefined || currentLabel === null || currentLabel === '') {
				currentLabel = labels[ index % labels.length ];
				arrayLabel[index] = currentLabel; // persist so calculatedGroup gets the proper letters
			}

			// Add group to equation
			equation += nameGroup + '<span style="color: red;">' + currentLabel + '</span>';
			
			// Add operator between groups 
			if (index < (allGroups.length - 1)) {
				// Try to get operator from current group first
				operator = jQuery (item).find ('select[name="operatorGroup[]"]').val ();
				
				// If not found, try to get from all operatorGroup selects by index
				if (!operator || operator === '') {
					var allOperators = jQuery ('select[name="operatorGroup[]"]');
					if (allOperators.length > index) {
						operator = jQuery(allOperators[index]).val();
					}
				}
				
				if (operator && operator !== '') {
					equation += ' ' + operator + ' ';
				} else {
					equation += ' + '; // default to + if no operator found
				}
			}

			if ((itemOne !== '') && (itemTwo !== '') && (itemOne !== 'Seleccionar') && (itemTwo !== 'Seleccionar')) {
				jQuery ('#legend').removeClass ('hide');
				equationGroup = itemOne + ' ' + operatorIn + ' ' + itemTwo;
				clone = jQuery ('#contentGroup').clone ().addClass ('cs-myGroup').attr ('id', '').html ('grupo ' + '<span style="color: red;">' + (arrayLabel[index] || currentLabel) + '</span>' + ' = ' + equationGroup);
				clone.insertBefore ('#contentGroup')
			}
		});

		jQuery ('#equation').html (equation);

	};

	var validateForm = function () {
		var form = jQuery ('#formElement');
		if (isValidAllData (form)) {
			upDateCalculatedGroup ();
			jQuery ('#calculatedGroup').val (arrayLabel.join (';'));
			jQuery ('#calculatedEquation').val (encodeURIComponent (jQuery ('#myEquation').html ()));
            jQuery ('#relatedModule').val (relatedModules.join (';'));
			form.submit ();
		} else {
			jQuery ("html, body").animate ({ scrollTop: 0 }, 800);
			return false;
		}
	};

	window.CSUtils = {
		addGroup:                   addGroup,
		checkValue:                 checkValue,
		eraseGroup:                 eraseGroup,
		selectOperator:             selectOperator,
		searchCalculated:           searchCalculated,
		setCalculatedPattern:       setCalculatedPattern,
		setCalculatedSystem:        setCalculatedSystem,
		setOperator:                setOperator,
		getCalculatedField:         getCalculatedField,
		lookEquation:               lookEquation,
		openCreateCalculationModal: openCreateCalculationModal,
		upDateCalculatedGroup:      upDateCalculatedGroup,
		validateForm:               validateForm
	};

	var initializeSelect2 = function() {
		// Verificar si select2 esta disponible antes de usar defaults.reset()
		if (jQuery.fn.select2 && jQuery.fn.select2.defaults && typeof jQuery.fn.select2.defaults.reset === 'function') {
			jQuery.fn.select2.defaults.reset ();
		}
		
		// Verificar si select2 está disponible antes de inicializarlo
		if (jQuery.fn.select2) {
			jQuery ('.search-element').select2 ({
				placeholder: 'Seleccione un elemento calculado'
			});
		} else {
			console.warn('Select2 plugin no está disponible. Usando select normal.');
		}
	};

	var onDocumentReadyHandler = function () {
		// Usar el estado del archivo de inicialización
		if (window.CSUtilsSelect2Ready === true) {
			initializeSelect2();
		} else if (window.CSUtilsSelect2Ready === false) {
			console.warn('Select2 no está disponible, continuando sin él');
		} else {
			// Esperar a que se complete la inicialización
			setTimeout(onDocumentReadyHandler, 200);
			return;
		}
		labelIndex = 0;
		jQuery ('div[id ^= Group-]').each (function (index, item) {
			label = labels[ labelIndex++ % labels.length ];
			jQuery (item).find ('span').eq (0).html (label + ' = (');
			arrayLabel.push (label);
		});
		upDateCalculatedGroup ();
	};

	jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));