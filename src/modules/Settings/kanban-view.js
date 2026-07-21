(function (jQuery) {
    // Private var
    var cardFieldElement     = jQuery ('#cardFieldElement'),
        codeElement          = jQuery ('#codeElement'),
        codeElementField     = jQuery ('#codeElementField'),
        firstAction          = jQuery ('#firstaccion'),
        isDefault            = jQuery ('#isDefault'),
        isIncludeLabel       = jQuery ('#is_Included'),
        isIncludeInput       = jQuery ('#isIncluded'),
        isInstance           = jQuery ('#isInstance').val(),
        moduleName           = jQuery ('#modulename'),
        prevDefaultView      = jQuery ('#prevDefaultView'),
        cardFields           = '',
        calculationOperators = [
            {
                operator:'AVG',
                label:   'Promedio',
                typeOfData: ['N', 'NN']
            },
            {
                operator:'COUNT',
                label:   'Contar el número de registros',
                typeOfData: ['V','DT', 'T', 'N', 'NN']
            },
            {
                operator:'MAX',
                label:   'Máximo',
                typeOfData: ['N', 'NN']
            },
            {
                operator:'MIN',
                label:   'Mínimo',
                typeOfData: ['N', 'NN']
            },
            {
                operator:'SUM',
                label:   'Suma',
                typeOfData: ['N', 'NN']
            },
            {
                operator:'STD',
                label:   'Desviación estándar',
                typeOfData: ['N', 'NN']
            },
            {
                operator:'VAR_POP',
                label:   'Varianza',
                typeOfData: ['N', 'NN']
            }


        ],
        lastModule           = '',
        lastApp              = '';

	//Private function
    var addFieldsCard = function (tab) {
        cardFieldElement.html ('');
        cardFieldElement.html ('<option value="">Seleccione ...</option>');
        cardFields = '<option value="">Seleccione ...</option>';
        new Ajax.Request (
            'index.php',
            {
                queue:      { position: 'end', scope: 'command' },
                method:     'post',
                postBody:   'module=Settings&action=SettingsAjax&file=LoadElementsKanban&function=cardElementField&tabname=' + tab,
                onComplete: function (response) {
                    var data = JSON.parse (response.responseText);
                    cardFieldElement.html ('');
                    var htmlElement = '<option value="">Seleccione ...</option>';
                    if (data != null && data.length > 0) {
                        for (var j = 0; j < data.length; j++) {
                            htmlElement += '<option value="' + data[ j ].fieldid + '" fieldname="' + data[ j ].fieldname + '" typeofdata="' + data[ j ].typeofdata + '"';
                            htmlElement += '>' + data[ j ].fieldlabel + '</option>';
                            // for calculatión data
                            cardFields += '<option value="' + data[ j ].tablename + '.' + data[ j ].fieldname + '" typeofdata="' + data[ j ].typeofdata + '"';
                            cardFields += '>' + data[ j ].fieldlabel + '</option>';
                        }
                        cardFieldElement.html (htmlElement);
                    } else {
                        cardFieldElement.html ('<option value="">Seleccione ...</option>');
                    }
                }
            }
        );

    };

    var getCustomView = function (moduleName) {
		var tabId      = codeElement.val (),
            advancedFilter = jQuery ('#kanban-advanced-filter'),
            recordId       = jQuery ('#record').val ();
		if (!advancedFilter.is(':empty')) {
            advancedFilter.children().each(function(element) {
                jQuery(this).remove();
            })
        }

        new Ajax.Request (
            'index.php',
            {
                queue:      { position: 'end', scope: 'command' },
                method:     'post',
                postBody:   'module=Settings&action=SettingsAjax&file=LoadElementsKanban&function=getCustomView&tabname=' + moduleName,
                onComplete: function (response) {
                   var data = JSON.parse (response.responseText);
                    if (data !== null && !jQuery.isEmptyObject (data)) {
                        getDateColumns (moduleName);
                        CustomViewUtils.init (data);
                    } else {
                        alert ('El módulo seleccionado no tiene campos para filtros, imprescindibles para crear una vista kanban');
                    }
                }
            }
        );
	};

    var getDateColumns = function (moduleName) {
        var form             = jQuery ('#KambanViewForm'),
            dateFilterColumn = jQuery ('#standard-filter-column'),
            filterPeriod     = jQuery ('#standard-filter-period'),
            startDateField   = form.find ('#standard-filter-start-date'),
            endDateField     = form.find ('#standard-filter-end-date');
        new Ajax.Request (
            'index.php',
            {
                queue:      { position: 'end', scope: 'command' },
                method:     'post',
                postBody:   'module=Settings&action=SettingsAjax&file=LoadElementsKanban&function=getDateColumnList&tabname=' + moduleName,
                onComplete: function (response) {
                    var data = JSON.parse (response.responseText),
                        keys, option, values;
                    dateFilterColumn.empty ();
                    filterPeriod.val ('');
                    CustomViewUtils.setPeriod (filterPeriod);
                    option = jQuery ('<option></option>')
                        .val ('')
                        .text ('Seleccione...');
                    dateFilterColumn.append (option);
                    if (data != null && !jQuery.isEmptyObject (data)) {
                        keys = Object.keys(data);
                        values = Object.values(data);
                        for (var j = 0; j < keys.length; j++) {
                            option = jQuery ('<option></option>')
                                .val (keys[ j ])
                                .text (values[ j ]);
                            dateFilterColumn.append (option);
                        }
                        startDateField.datepicker ({ format: 'yyyy/mm/dd', language: 'es', weekStart: 1 });
                        startDateField.mask ("9999/99/99");
                        endDateField.datepicker ({ format: 'yyyy/mm/dd', language: 'es', weekStart: 1 });
                        endDateField.mask ("9999/99/99");
                    } else {
                        alert ('El módulo seleccionado no tiene campos para filtros, imprescindibles para crear una vista kanban');
                    }
                }
            }
        );
    };

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

	var getModuleFields = function (tab) {
        cardFields = '<option value="">Seleccione ...</option>';
        new Ajax.Request (
            'index.php',
            {
                queue:      { position: 'end', scope: 'command' },
                method:     'post',
                postBody:   'module=Settings&action=SettingsAjax&file=LoadElementsKanban&function=cardElementField&tabname=' + tab,
                onComplete: function (response) {
                    var data = JSON.parse (response.responseText);
                    if (data != null && data.length > 0) {
                        for (var j = 0; j < data.length; j++) {
                            // for calculatión data
                            cardFields += '<option value="' + data[ j ].tablename + '.' + data[ j ].fieldname + '" typeofdata="' + data[ j ].typeofdata + '"';
                            cardFields += '>' + data[ j ].fieldlabel + '</option>';
                        }
                    }
                }
            }
        );
    };

    var setLoading = function (obj){
        obj.empty ();
        obj.append (
            jQuery (
                '<option>',
                {
                    value: '',
                    text:  '    Cargando...   '
                }
            )
        );
    };

	//Public Function
    var addTableCardField = function () {
        var cardFieldSelected = jQuery ('#cardFieldElement option:selected'),
            calculationSelect = '',
            body              = jQuery ('table.cardField').find ('tbody'),
            dup               = false,
            rowTable           = '';
        jQuery ('.hiddenField').each (function () {
            if (jQuery (this).val () === cardFieldElement.val ()) {
                alert ('El campo ' + cardFieldSelected.text () + ' ya esta agregado a la lista');
                cardFieldElement.val ('');
                dup = true;
                return false;
            }
        });
        if (!dup && cardFieldElement.val () !== '') {
            rowTable = jQuery (jQuery ('#row-card-template').html());
            rowTable.find ('input').eq (0).val (cardFieldElement.val ());
            rowTable.find ('input').eq (1).val (cardFieldElement.val ());
            rowTable.find ('input').eq (2).attr ('name', 'fieldId_' + cardFieldElement.val ()).val (cardFieldElement.val ());
            rowTable.find ('span').eq (0).html (cardFieldSelected.text ());
            body.append (rowTable);
            cardFieldElement.val ('');
        } else {
            return false;
        }
    };

    var deleteField = function (button) {
        var buttonElement = jQuery (button),
            row           = buttonElement.closest ('.card'),
            rules         = buttonElement.closest ('table.cardField');
        if (!confirm ('¿Estás seguro de eliminar el registro seleccionado?')) {
            return;
        }

        row.remove ();
    };

    var deleteRole = function (element) {
      var rowTable = jQuery (element).parent().parent ();
        if (!confirm ('¿Estás seguro de eliminar la regla seleccionada?')) {
            return;
        }
        rowTable.remove ();
    };

    var getCalculationOperators = function (element) {
        var fieldType = jQuery ('option:selected', element).attr('typeofdata'),
            fieldId   = jQuery (element).val (),
            operators = jQuery (element).parent ().parent ().find ('select').eq(1),
            swDisable = false;
        operators.empty ();
        operators.append (
            jQuery (
                '<option>',
                {
                    value: '',
                    text: 'Seleccione..'
                }
            )
        );
        if (fieldId !== '') {
            for (var j = 0; j < calculationOperators.length; j++) {
                swDisable = (jQuery.inArray(fieldType, calculationOperators[j].typeOfData) !== -1) ? false : true;
                operators.append (
                    jQuery (
                        '<option>',
                        {
                            value: calculationOperators[j].operator,
                            text: calculationOperators[j].label
                        }
                    ).attr('disabled', swDisable)
                );
            }
        }
    };

    var rowDown = function(btn){
        var rowToMove = jQuery(btn).parents('tr.rule');

        var next = rowToMove.next('tr.rule');
        next.after(rowToMove);
    };

    /**
     * Sincroniza la tabla de reglas con los valores disponibles del campo base del Kanban.
     *
     * Objetivo: cuando los valores del picklist/pipeline han cambiado despues de crear la vista
     * (se agregaron nuevos valores), esta funcion detecta los valores que aun no tienen una
     * regla y anade filas vacias (color por defecto) para que el administrador pueda
     * completarlas y guardarlas. No elimina reglas existentes aunque su valor ya no exista:
     * la eliminacion se mantiene manual para evitar perdida accidental de configuracion.
     */
    var syncRulesWithFieldValues = function () {
        var dataElement = document.getElementById ('available-field-values'),
            body        = jQuery ('table.rules').find ('tbody'),
            available, existingIds, added, rowTable, i, item, key;
        if (!dataElement) {
            alert ('No hay informacion de valores disponibles para este campo.');
            return;
        }
        try {
            available = JSON.parse (dataElement.textContent || dataElement.innerText || '[]');
        } catch (e) {
            alert ('No se pudo leer la lista de valores disponibles: ' + e.message);
            return;
        }
        if (!available || available.length === 0) {
            alert ('El campo no tiene valores disponibles actualmente.');
            return;
        }

        // Construir set de pickfieldid ya presentes en las reglas actuales.
        existingIds = {};
        body.find ('tr.rule input[name="pickfieldid[]"]').each (function () {
            existingIds [String (jQuery (this).val ())] = true;
        });

        added = 0;
        for (i = 0; i < available.length; i++) {
            item = available [i];
            key  = String (item.pickfieldid);
            if (existingIds [key]) {
                continue;
            }
            rowTable = jQuery (jQuery ('#row-rule-template').html ());
            // ruleids=0 (nueva regla), pickfieldid, pickfieldLabel, span con el label
            rowTable.find ('input').eq (0).val (0);
            rowTable.find ('input').eq (1).val (item.pickfieldid);
            rowTable.find ('input').eq (2).val (item.picklabel);
            rowTable.find ('span').eq (0).html (item.picklabel);
            // Poblar el select de campos de calculo con los campos ya cargados
            if (cardFields && cardFields !== '') {
                rowTable.find ('select').eq (0).html (cardFields);
            }
            setColorPicker (rowTable.find ('.color'));
            body.append (rowTable);
            added++;
        }

        if (added === 0) {
            alert ('Todas las opciones del campo ya tienen una regla configurada.');
        } else {
            alert ('Se agregaron ' + added + ' regla(s) nueva(s). Configurelas y guarde la vista.');
        }
    };

    var rowUp = function(btn){
        var rowToMove = jQuery(btn).parents('tr.rule');

        var prev = rowToMove.prev('tr.rule');
        prev.before(rowToMove);
    };

    var setDefaultView = function () {
        var checked        = isDefault,
            selectedModule = jQuery ('#codeElement option:selected').attr ('tabname'),
            moduleLabel    = jQuery ('#codeElement option:selected').attr ('tablabel'),
			defaultView    = jQuery ('#isDefaultView').val (),
			idKanban       = jQuery ('#record').val (),
            prevDefault    = prevDefaultView,
            arguments      = [
                'module=Settings',
                'action=SettingsAjax',
                'file=LoadElementsKanban',
                'function=find_default_view',
                'ajax=true',
                'modulename=' + encodeURIComponent (selectedModule)
            ];
        if (jQuery (checked).attr ('checked') === 'checked' ) {
            jQuery (checked).removeAttr ('checked');
            prevDefault. val ('');
        } else {
            jQuery.ajax (
                'index.php',
                {
                    data:     arguments.join ('&'),
                    dataType: 'text',
                    method:   'post'
                }
            ).done (function (responseText) {
                if (responseText != 'null') {
                    kanbanData = JSON.parse (responseText);
                    if (defaultView == 0 || (idKanban != kanbanData.kanbanviewid)) {
                        if (confirm('Ya existe una vista kanba por defecto para ' + moduleLabel + ' ¿Desea cambiarla?')) {
                            prevDefault.val(kanbanData.kanbanviewid);
                            jQuery(checked).attr('checked', 'checked');
                        } else {
                            jQuery(checked).removeAttr('checked');
                            prevDefault.val('');
                        }
                    } else {
                        jQuery(checked).attr('checked', 'checked');
					}
				} else {
                    prevDefaultView. val ('');
                    jQuery (checked).attr ('checked', 'checked');
				}
            });
        }
	};

    var setViewIncluded = function () {
        var checked = isIncludeInput,
            defaultView       = jQuery('#isDefault-row'),
            defaultViewSelect = isDefault;
        if (jQuery (checked).attr ('checked') === 'checked' ) {
            jQuery (checked).removeAttr ('checked');
            jQuery (defaultViewSelect).removeAttr ('checked');
            jQuery (defaultView).addClass('hide');
        } else {
            jQuery (checked).attr ('checked', 'checked');
            jQuery (defaultView).removeClass('hide');
        }
    };

	var deleteView = function (label) {
		return confirm ('¿Estás seguro de borrar la vista "' + label + '"?')
	};

    var selectApp = function  (tabid, mode) {
        var codeApp     = jQuery ('#codeApp'),
            fromfieldId = jQuery ('#fromfieldid');

        if (codeApp.length > 0 && codeApp.val () != '') {
            var appSelect = codeApp.val ();
            if ((lastApp === '') || (lastModule === '')) {
                lastApp = appSelect;
            } else if((lastApp !== appSelect)) {
                if (confirm ('Esta operación eliminará todaas las condiciones previas ¿Continuar?')) {
                    lastApp    = appSelect;
                    lastModule = ''
                } else {
                    codeApp.val (lastApp);
                    return;
                }
            }
        } else {
            setLoading (codeElement);
            setLoading (codeElementField);
            setLoading (cardFieldElement);
            alert ('Seleccione una aplicación');
            return false;
        }
        setLoading (codeElement);
        setLoading (codeElementField);
        setLoading (cardFieldElement);

        if (firstAction.val () == '0') {
            var body     = jQuery ('table.rules').find ('tbody'),
                template = '',
                row      = jQuery (template);
            body.html (row);

            var bodyCard = jQuery ('table.cardField').find ('tbody'),
                template = '',
                row      = jQuery (template);
            bodyCard.html (row);

        }

        new Ajax.Request (
            'index.php',
            {
                queue:      { position: 'end', scope: 'command' },
                method:     'post',
                postBody:   'module=Settings&action=SettingsAjax&file=LoadElementsKanban&function=paramFieldElements&appSelect=' + appSelect,
                onComplete: function (response) {
                    var data = JSON.parse (response.responseText);
                    codeElement.html ('');
                    var htmlElement = '<option value="">Seleccione ...</option>';
                    var selected = '';
                    for (var j = 0; j < data.length; j++) {
                        htmlElement += '<option value="' + data[ j ].tabid + '" tabname="' + data[ j ].name + '" tablabel="' + data[ j ].tablabel + '"';
                        if (mode == 'edit' && data[ j ].tabid == tabid && firstAction.val () == '1') {
                            htmlElement += ' selected="selected" >' + data[ j ].tablabel + '</option>';
                        } else {
                            htmlElement += '>' + data[ j ].tablabel + '</option>';
                        }
                    }
                    codeElement.html (htmlElement);

                    if (firstAction.val () == '1') {
                        selectModule (codeElement, fromfieldId.val (), mode);
                    }

                }
            }
        );
    };

    var selectField = function  (element, mode) {
        var body     = jQuery ('table.rules').find ('tbody'),
            rowTable = jQuery (jQuery ('#row-rule-template').html());
        if (codeElementField.length > 0 && codeElementField.val () != '') {
            var fieldSelect = codeElementField.val ();
            var fieldname = jQuery ('option:selected', element).attr ('fieldname');
            body.empty ();
        } else if(!isInstance) {
            codeElementField.html ('');
            codeElementField.html ('<option value="">Seleccione ...</option>');
            alert ('Seleccione un campo');
            return false;
        } else {
            alert ('Seleccione un campo');
            return false;
        }

        new Ajax.Request (
            'index.php',
            {
                queue:      { position: 'end', scope: 'command' },
                method:     'post',
                postBody:   'module=Settings&action=SettingsAjax&file=LoadElementsKanban&function=codeElementFieldPick&fieldname=' + fieldname + '&fieldid=' + fieldSelect + '&modulename=' + encodeURIComponent (moduleName.val ()),
                onComplete: function (response) {
                    var data = JSON.parse (response.responseText);
                    jQuery ('#rules-edit').html ('');

                    if (data != null && data.length > 0) {
                        for (var j = 0; j < data.length; j++) {
                            rowTable.find ('input').eq (0).val ((mode === 'edit') ? data[ j ].ruleid : 0);
                            rowTable.find ('input').eq (1).val (data[ j ].pickfieldid);
                            rowTable.find ('input').eq (2).val (data[ j ].picklabel);
                            rowTable.find ('span').eq (0).html (data[ j ].picklabel);
                            rowTable.find ('select').eq (0).html (cardFields);
                            setColorPicker (rowTable.find ('.color'));
                            body.append (rowTable);
                            rowTable = jQuery (jQuery ('#row-rule-template').html());
                        }
                        jQuery ('#fieldname').val (fieldname);
                    }
                }
            }
        )
    };

    var selectModule = function  (element, elementField, mode) {
        var advancedFilter = jQuery ('#kanban-advanced-filter'),
            infoInclude      = 'Incluir en la vista de lista del módulo',
            moduleSelect,
            moduleLabel      = '',
			tab              = '';

        if (codeElement.length > 0 && codeElement.val () !== '') {
            setLoading (codeElementField);
            moduleSelect = codeElement.val ();
            moduleLabel = jQuery ('option:selected', element).attr ('tablabel');
            tab         = jQuery ('option:selected', element).attr ('tabname');
            isIncludeLabel.html(infoInclude + ': ' + moduleLabel );
            isIncludeInput.removeAttr ('disabled');
            isDefault.removeAttr ('disabled');
            if (lastModule === '') {
                lastModule = moduleSelect;
            } else if(lastModule !== moduleSelect) {
                    if (confirm ('Esta operación eliminará todaas las condiciones previas ¿Continuar?')) {
                        prevDefaultView.val ('');
                        isDefault.removeAttr ('checked');
                        lastModule = moduleSelect;
                    } else {
                        codeElement.val (lastModule);
                        return;
                    }
            }
        } else {
            codeElementField.html ('');
            codeElementField.html ('<option value="">Seleccione ...</option>');
            isIncludeLabel.html(infoInclude);
            alert ('Seleccione un módulo');
            return false;
        }

        codeElementField.html ('');
        codeElementField.html ('<option value="">Seleccione ...</option>');

        cardFieldElement.html ('');
        cardFieldElement.html ('<option value="">Seleccione ...</option>');

        if (firstAction.val () == '0') {
            var body     = jQuery ('table.rules').find ('tbody'),
                template = '',
                row      = jQuery (template);
            body.html (row);

            var bodyCard = jQuery ('table.cardField').find ('tbody'),
                template = '',
                row      = jQuery (template);
            bodyCard.html (row);
        }

        new Ajax.Request (
            'index.php',
            {
                queue:      { position: 'end', scope: 'command' },
                method:     'post',
                postBody:   'module=Settings&action=SettingsAjax&file=LoadElementsKanban&function=codeElementField&tabname=' + tab,
                onComplete: function (response) {
                    var data = JSON.parse (response.responseText);
                    codeElementField.html ('');
                    var htmlElement = '<option value="">Seleccione ...</option>';
                    if (data != null && data.length > 0) {
                        for (var j = 0; j < data.length; j++) {
                            htmlElement += '<option value="' + data[ j ].fieldid + '" fieldname="' + data[ j ].fieldname + '" ';
                            if (mode == 'edit' && data[ j ].fieldid == elementField && firstAction.val () == '1') {
                                htmlElement += ' selected="selected" >' + data[ j ].fieldlabel + '</option>';
                            } else {
                                htmlElement += '>' + data[ j ].fieldlabel + '</option>';
                            }
                        }
                        codeElementField.html (htmlElement);
                        moduleName.val (tab);
                        if (firstAction.val () == '1') {
                            firstAction.val ('0');
                        }
                        addFieldsCard (tab);
                        getCustomView (tab);
                    } else {
                        codeElementField.html ('<option value="">Seleccione ...</option>');
                        alert ('El módulo seleccionado no tiene registrados campos de tipo lista desplegable, imprescindibles para crear una vista kanban');
                    }
                }
            }
        );
    };

	var validateForm = function (formElement) {
        var form                      = jQuery (formElement),
            isValidate                = true,
            listGroupFilter           = jQuery ('.list-group-item'),
            rules                     = jQuery ('.table.cardField').find ('.card'),
            message                   = '',
            n                         = rules.length,
            standardFilterColumn      = form.find ('#standard-filter-column'),
            standardFilterColumnValue = standardFilterColumn.val (),
            standardFilterPeriod      = form.find ('#standard-filter-period'),
            standardFilterPeriodValue = standardFilterPeriod.val (),
            rule, field, value, i;

		field = form.find ('#label');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Introduce el nombre de la vista');
			field.focus ();
			return false;
		}

		field = form.find ('#codeApp');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Selecciona la aplicación');
			field.focus ();
			return false;
		}

		field = form.find ('#codeElement');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Selecciona el módulo');
			field.focus ();
			return false;
		}

		field = form.find ('#codeElementField');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Selecciona el campo');
			field.focus ();
			return false;
		}

		if (n == 0) {
			alert ('Seleccione al menos un campo del módulo a mostrar en la tarjeta');
			cardFieldElement.focus ();
			return false;
		}
        if (
            ((standardFilterColumnValue === null) || (standardFilterColumnValue === undefined) || (standardFilterColumnValue.trim () === '')) &&
            ((standardFilterPeriodValue !== null) && (standardFilterPeriodValue !== undefined) && (standardFilterPeriodValue.trim () !== ''))
        ) {
            alert ('Selecciona la columna');
            standardFilterColumn.focus ();
            return false;
        } else if (
            ((standardFilterColumnValue !== null) && (standardFilterColumnValue !== undefined) && (standardFilterColumnValue.trim () !== '')) &&
            ((standardFilterPeriodValue === null) || (standardFilterPeriodValue === undefined) || (standardFilterPeriodValue.trim () === ''))
        ) {
            alert ('Selecciona la duración');
            standardFilterPeriod.focus ();
            return false;
        } else if (standardFilterPeriodValue === 'custom') {
            field = form.find ('#standard-filter-start-date');
            value = field.val ();
            if ((value === null) || (value === undefined) || (value.trim () === '')) {
                alert ('Selecciona la fecha de inicio');
                field.focus ();
                return false;
            }

            field = form.find ('#standard-filter-end-date');
            value = field.val ();
            if ((value === null) || (value === undefined) || (value.trim () === '')) {
                alert ('Selecciona la fecha de fin');
                field.focus ();
                return false;
            }
        }

        if (listGroupFilter.length > 0) {
		    listGroupFilter.each(function (index) {
		        var field, operator, value;
		        field = jQuery(this).find('select').eq (0);
		        if ((index > 0) && field.length > 0) {
                    if ((field.val() === null) || (field.val() === undefined) || (field.val().trim() === '')) {
                        isValidate = false;
                    }
                    operator = jQuery(this).find('select').eq(1).val();
                    if ((operator === null) || (operator === undefined) || (operator.trim() === '')) {
                        isValidate = false;
                    }
                    value = jQuery(this).find('input').eq(0).val();
                    if ((value === null) || (value === undefined) || (value.trim() === '')) {
                        isValidate = false;
                    }
                }
            })
        }
        if (!isValidate) {
		    alert ('Complete los datos de los filtros avanzados!');
		    return false
        }
		return isValidate
	};

	window.KanbanUtils = {
        addTableCardField:       addTableCardField,
        deleteField:             deleteField,
        deleteRole:              deleteRole,
		deleteView:              deleteView,
        getCalculationOperators: getCalculationOperators,
        rowDown:                 rowDown,
        rowUp:                   rowUp,
        selectApp:               selectApp,
        selectField:             selectField,
        selectModule:            selectModule,
        setDefaultView:          setDefaultView,
        setViewIncluded:         setViewIncluded,
        syncRulesWithFieldValues: syncRulesWithFieldValues,
		validateForm:            validateForm
	};

	var onDocumentReadyHandler = function () {
		var form           = jQuery ('#KambanViewForm'),
            field          = (jQuery ('.color')),
            codeApp        = jQuery ('#codeApp'),
            codeElement    = jQuery('#codeElement'),
            startDateField = form.find ('#standard-filter-start-date'),
            endDateField   = form.find ('#standard-filter-end-date'),
            tab            = '',
            mode           = jQuery('#mode').val ();
        isInstance           = jQuery ('#isInstance').val();
        if (isInstance && mode === '') {
            var tab = jQuery ('option:selected', codeElement).attr ('tabname');
            getModuleFields (tab);
        }
		if (field.length === 0) {
			return;
		}
		setColorPicker (field);
        lastModule = codeElement.val ();
        lastApp    = codeApp.val ();
        tab        = jQuery ('option:selected', codeElement).attr ('tabname');
        if (cardFields === '') {
            getModuleFields (tab)
        }
        startDateField.datepicker ({ format: 'yyyy/mm/dd', language: 'es', weekStart: 1 });
        startDateField.mask ("9999/99/99");
        endDateField.datepicker ({ format: 'yyyy/mm/dd', language: 'es', weekStart: 1 });
        endDateField.mask ("9999/99/99");
	};

	jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));

