(function (jQuery) {
    //private variables
    var idFieldTable                = '',
        TABLE_NAME_VALIDATE         = true,
        FIELD_TYPE_CURRENCY         = 71,
        FIELD_TYPE_GLOBAL_PICKLIST  = 16,
        FIELD_TYPE_MODULE_RECORDS   = 404,
        FIELD_TYPE_MODULE_REFERENCE = 10,
        FIELD_TYPE_MULTI_SELECT     = 33,
        FIELD_TYPE_NUMBER           = 7,
        FIELD_TYPE_PERCENTAGE       = 9,
        FIELD_TYPE_PICKLIST         = 15,
        FIELD_TYPE_PIPELINE         = 8192,
        FIELD_TYPE_TEXT             = 1;

    var wizard              = null,
        GridFieldName       = '',
        FieldNameAppearance = '',
        infoSave            = '',
        totalBlocks         = 0,
        totalFields         = 0,
        totalRelatedLists   = 0,
        relatedModuleFields = [],
        listFields          = [],
        checkBoxFields      = [],
        textFields          = [],
        numericFields       = [];

    // private method

    var destroyWizard = function () {
        wizard = null;
        window.location.reload ();
    };

    var getSelectedFields = function () {
        var tableFields = wizard.cards [ 'step-1' ].el.find('#step-1-table-' + idFieldTable);

        tableFields.find('tr').each(function(index, tr) {
            var fieldType  = jQuery (tr).find ('select').eq (0).val (),
                fieldLabel = jQuery (tr).find ('input').eq (1).val (),
                fieldName  = jQuery (tr).find ('input').eq (0).val (),
                item       = '',
                moduleName = '';
            item = fieldLabel + '@' + fieldName;
            if (jQuery.inArray(fieldType, ['1', '5', '21', '13', '11', '17','5']) !== -1) {
                textFields.push (item + '@' + fieldType)
            } else if (jQuery.inArray(fieldType, ['7', '71', '9']) !== -1) {
                numericFields.push (item + '@' + fieldType)
            } else if (fieldType === '56') {
                checkBoxFields.push (item + '@' + fieldType)
            } else if (fieldType === '15') {
                listFields.push (item + '@' + fieldType)
            } else if (fieldType === '10') {
                moduleName = jQuery (tr).find ('select').eq (1);
                item       = item + '@' + moduleName.find ('option:selected').text() + '@' + moduleName.val() + '@' + fieldType;
                relatedModuleFields.push (item);
            }
        });
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

    var searchDiff = function () {
        var hasDiff = false,
            tableFields = wizard.cards [ 'step-1' ].el.find('#step-1-table-' + idFieldTable);

        tableFields.find('tr').each(function(index, tr) {
            var fieldType  = jQuery (tr).find ('select').eq (0).val (),
                fieldLabel = jQuery (tr).find ('input').eq (1).val (),
                fieldName  = jQuery (tr).find ('input').eq (0).val (),
                item       = '',
                moduleName = '';
            item = fieldLabel + '@' + fieldName;
            if ((jQuery.inArray(fieldType, ['1', '5', '21', '13', '11', '17']) !== -1) && (jQuery.inArray ((item + '@' + fieldType), textFields) === -1)) {
                hasDiff = true;
            } else if ((jQuery.inArray(fieldType, ['7', '71', '9']) !== -1) && (jQuery.inArray ((item + '@' + fieldType), numericFields) === -1)) {
                hasDiff = true;
            } else if ((fieldType === '56')  && (jQuery.inArray ((item + '@' + fieldType), checkBoxFields) === -1)) {
                hasDiff = true;
            } else if ((fieldType === '15') && (jQuery.inArray ((item + '@' + fieldType), listFields) === -1)) {
                hasDiff = true;
            } else if(fieldType === '10') {
                moduleName = jQuery (tr).find ('select').eq (1);
                item       = item + '@' + moduleName.find ('option:selected').text() + '@' + moduleName.val()+ '@' + fieldType;
                if(jQuery.inArray(item, relatedModuleFields) === -1) {
                    hasDiff = true;
                }
            }
        });
        return hasDiff;
    };

    var selectedFieldActivation = function (obj, id, checkBoxName) {
        var column    = jQuery (obj),
            rows      = jQuery ('#tbody-' +  checkBoxName + '-' +  idFieldTable).find('tr'),
            thisRowId = column.parent().parent().attr('id');

        rows.each(function(index, tr) {
            var selectObj  = jQuery (tr).find ('select').eq (0).val(),
                trId       = jQuery (tr).attr('id');

            if ((selectObj !== 'undefined') && (selectObj !== undefined) && (selectObj !== '') ){
                if ((column.val() === selectObj) && (thisRowId !== trId)) {
                    alert('Columna ya seleccionada. Por favor intente otra columna');
                    column.val('');
                    return false;
                }
            }
        });

    };

    var selectedFieldImport = function (obj, id) {
        var avaiableTypes =  [],
            fieldOption   = jQuery (obj),
            tableColunm   = jQuery('#tabble-column-' + id),
            columns       = tableColunm.find('option'),
            uiType        = fieldOption.find ('option:selected').attr ('data-type');
        tableColunm.val('');
        if (jQuery.inArray(uiType, ['1', '21', '13', '11', '15', '17','5010']) !== -1) {
            avaiableTypes = ['1', '21', '13', '11', '17'];
        } else if (jQuery.inArray(uiType, ['7', '71', '9']) !== -1) {
            avaiableTypes = ['7', '71', '9'];
        } else {
            avaiableTypes = ['5']
        }
        columns.each (function (index, optionElement) {
            var option   = jQuery (optionElement),
                dataType = option.attr ('data-type');
            if (jQuery.inArray (dataType, avaiableTypes) !== -1) {
                option.removeAttr('disabled');
            } else if (dataType !== 0) {
                option.attr ('disabled', true);
            }
        });
    };

    var setActivationAction = function (card, hasDiff) {
        var k = 0,
            importRow = card.el.find('#group-checkbox-activation-' + idFieldTable),
            totalElement = checkBoxFields.length;
        if (hasDiff) {
            importRow.empty();
        }

        for (k = 0; k < totalElement; k++) {
            var importData = checkBoxFields[k].split('@'),
                infoText   = '',
                contents = jQuery ('#checkbox-action-template').html ().replace (/__ID_LINKAGE__/g, k)
                    .replace (/__CHECKBOX_LABEL__/g, importData[0])
                    .replace (/__CHECKBOX_NAME__/g, importData[1]);
            importRow.append(contents);
            if (GridFieldName !== '') {
                jQuery ('#btn-ADD-' + importData[1]).trigger ('onclick');
                console.log('disparando importar valores');
            }
        }
    };

    var setFieldType = function (selectElement) {
        var select            = jQuery (selectElement),
            row               = select.closest ('.field'),
            selectedFieldType = select.val (),
            fieldType         = isNaN (selectedFieldType) ? 1 : parseInt (selectedFieldType),
            field;

        field = row.find ('.field-length');
        if (jQuery.inArray (fieldType, [ FIELD_TYPE_TEXT, FIELD_TYPE_NUMBER, FIELD_TYPE_PERCENTAGE, FIELD_TYPE_CURRENCY ]) !== -1) {
            field.show ();
            if (jQuery.inArray (fieldType, [ FIELD_TYPE_NUMBER, FIELD_TYPE_PERCENTAGE, FIELD_TYPE_CURRENCY ]) !== -1) {
                field.val (18);
            } else {
                field.val (255);
            }
        } else {
            field.hide ();
        }

        field = row.find ('.field-precision');
        if (jQuery.inArray (fieldType, [ FIELD_TYPE_NUMBER, FIELD_TYPE_PERCENTAGE, FIELD_TYPE_CURRENCY ]) !== -1) {
            field.show ();
            field.val (2);
        } else {
            field.hide ();
        }

        field = row.find ('.field-picklist-values');
        if (jQuery.inArray (fieldType, [ FIELD_TYPE_PICKLIST, FIELD_TYPE_MULTI_SELECT, FIELD_TYPE_PIPELINE ]) !== -1) {
            field.show ();
        } else {
            field.hide ();
        }

        field = row.find ('.field-global-picklist');
        if (fieldType === FIELD_TYPE_GLOBAL_PICKLIST) {
            field.show ();
            row.find ('.field-name').prop ('readonly', true);
        } else {
            field.hide ();
            row.find ('.field-name').prop ('readonly', false);
        }

        field = row.find ('.field-referenced-module-name');
        if (jQuery.inArray (fieldType, [ FIELD_TYPE_MODULE_REFERENCE, FIELD_TYPE_MODULE_RECORDS ]) !== -1) {
            field.show ();
        } else {
            field.hide ();
        }
    };

    var setGlobalPicklistFieldName = function (obj) {
        var select                  = jQuery (obj),
            globalPicklistFieldName = select.val ();

        select.closest ('.field').find ('.field-name').val (globalPicklistFieldName);
    };

    var selectedOperationColumn = function (obj, id) {
        var column   = jQuery (obj),
            columnId = column.attr('id'),
            rows     = jQuery ('#' + id).find ('select');

        if (column.val () !== '') {
            rows.each(function (index, obj) {
                var value = jQuery(obj).val(),
                    id = jQuery(obj).attr('id');

                if ((value !== 'undefined') && (value !== undefined) && (value !== '')) {
                    if ((column.val() === value) && (columnId !== id)) {
                        alert('Campo ya seleccionada para esta operación Por favor intenta otro campo');
                        column.val('');
                        return false;
                    }
                }
            });
        }
    };

    var selectedSummaryColumn = function (obj, id) {
        var availableTypes = [],
            column         = jQuery (obj),
            columnType     = column.find('option:selected').attr('data-type'),
            columns        = jQuery ('#summary-operation-' + id).find('option'),
            rows           = jQuery ('#tbody-summary-' + idFieldTable).find('tr'),
            thisRowId      = column.parent().parent().attr('id');

        rows.each(function(index, tr) {
            var selectObj  = jQuery (tr).find ('select').eq (0).val(),
                trId       = jQuery (tr).attr('id');

            if ((selectObj !== 'undefined') && (selectObj !== undefined) && (selectObj !== '') ){
                if ((column.val() === selectObj) && (thisRowId !== trId)) {
                    alert('Columna ya seleccionada. Por favor intente otra columna');
                    column.val('');
                    return false;
                }
            }
        });

        if (jQuery.inArray(columnType, ['1', '21', '13', '11', '17']) !== -1) {
            availableTypes = ['COUNT_COLUMN'];
        } else if (jQuery.inArray(columnType, ['7', '71', '9']) !== -1) {
            availableTypes = ['SUM_COLUMN', 'COUNT_COLUMN', 'AVERAGE_COLUMN'];
        } else {
            availableTypes = ['COUNT_COLUMN']
        }
        columns.each (function (index, optionElement) {
            var option   = jQuery (optionElement),
                dataType = option.val();
            if (jQuery.inArray (dataType, availableTypes) !== -1) {
                option.removeAttr('disabled');
            } else if (dataType !== 0) {
                option.attr ('disabled', true);
            }
        });
    };

    var setImportAction = function (card, hasDiff) {
        var k = 0,
            importRow    = card.el.find('#group-linkages-import-' + idFieldTable),
            totalElement = relatedModuleFields.length,
            flmodule     = jQuery ('#modulename-' + idFieldTable).val ();
        if (hasDiff) {
            importRow.empty();
        }

        for (k = 0; k < totalElement; k++) {
            var importData = relatedModuleFields[k].split('@'),
                infoText   = '',
                contents = jQuery ('#linkages-import-template').html ().replace (/__ID_LINKAGE__/g, k)
                    .replace (/__MODULE_FIELDLAEL__/g, (importData[2] + ': ' + importData[0]))
                    .replace (/__MODULE_NAME__/g, importData[3])
                    .replace (/__RE_MODULE__/g, flmodule)
                    .replace (/__FIELD_NAME__/g, importData[1]);
            importRow.append(contents);
            if (GridFieldName !== '') {
                jQuery ('#btn-ADD-' + importData[1]).trigger ('onclick')
            }
        }
    };

    var setRelatedList = function (card, hasDiff) {
        var arguments,
            tableFields    = wizard.cards [ 'step-1' ].el.find('#step-1-table-' + idFieldTable),
            linkageListTab = card.el.find('#linkages-list-' + idFieldTable),
            moduleName     = jQuery('#modulename-' + idFieldTable).val(),
            lists          = [];
        
        if (hasDiff) {
            linkageListTab.empty();
            linkageListTab.html('<div class="alert alert-info" style="margin-top: 1.5em">No se han encontrado campos listas</div>')
        }
        tableFields.find('tr').each(function(index, tr) {
            var textArea  = jQuery (tr).find ('textarea').eq (0).val(),
                fieldName = jQuery (tr).find ('input').eq (0).val(),
                fieldLabel = jQuery (tr).find ('input').eq (1).val ();
            if ((textArea !== 'undefined') && (textArea !== undefined) && (textArea !== '') ){
                lists.push (fieldName + '@' + fieldLabel + '@' + textArea);
            }
        });
        arguments = {
            'module':        'Settings',
            'action':        'AjaxTableFieldUtils',
            'function':      'LINKAGE-LIST',
            'list':          lists,
            'columms':       textFields,
            'flmodule':      moduleName,
            'tableFileName': GridFieldName,
            'Ajax':          'true'
        };
        jQuery.post('index.php', arguments, function (data) {
            try {
                var message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    linkageListTab.html (message.html);
                }
            }
            catch (e) {
                alert(e);
            }
        });
    };

    var setOperationMatch = function (card, hasDiff) {
        var k = 0,
            btnAdd       = jQuery ('#btn-ADD-OPERATION-ROW'),
            operationRow = card.el.find('#group-operations-math-' + idFieldTable),
            totalElement = checkBoxFields.length;
        if (hasDiff) {
            operationRow.empty();
        }
        operationRow.append(jQuery ('#operation-math-template').html ());
        if (GridFieldName !== '') {
            jQuery ('#btn-ADD-OPERATION-ROW').trigger ('onclick');
        }
    };

    var updateProgressBar = function () {
        var cards      = wizard.cards,
            activeCard = wizard.getActiveCard (),
            index      = 0,
            cardName;
        for (cardName in cards) {
            if (cardName === activeCard.name) {
                break;
            }
            index += 1;
        }
        wizard.updateProgressBar ((index * 100) / Object.keys (wizard.cards).length);
    };

    var validateField = function(field) {
        var isValidate   = true,
            value        = field.val (),
            elementTitle = field.attr('title');

        if ((value === null) || (value === undefined) || (value.trim () === '')) {
            field.parent ().addClass ('has-error');
            if (field.parent ().find ('.help-block').length) {
                field.parent ().find ('.help-block').html (elementTitle + ' es requerido');
            } else {
                field.parent ().parent ().find ('.help-block').html (elementTitle + ' es requerido');
            }
            isValidate = false;
        }
        return isValidate;
    };

    var validateFields = function (card, fields) {
        var processedFieldNames = [],
            row, field, type, values, value, i;

        for (i = 0; i < fields.length; i += 1) {
            row = jQuery (fields [ i ]);

            field = row.find ('.field-name');
            value = field.val ();
            if ((value === undefined) || (value === null) || (value.trim () === '')) {
                card.wizard.errorPopover (field, 'Introduce el nombre');
                field.focus ();
                return false;
            } else if (jQuery.inArray (value, processedFieldNames) !== -1) {
                card.wizard.errorPopover (field, 'Ya tienes otro campo con el mismo nombre');
                field.focus ();
                return false;
            }
            processedFieldNames.push (value);

            field = row.find ('.field-label');
            value = field.val ();
            if ((value === undefined) || (value === null) || (value.trim () === '')) {
                card.wizard.errorPopover (field, 'Introduce la etiqueta');
                field.focus ();
                return false;
            }

            field = row.find ('.field-type');
            value = field.val ();
            if ((value === undefined) || (value === null) || (value.trim () === '')) {
                card.wizard.errorPopover (field, 'Selecciona el tipo');
                field.focus ();
                return false;
            }

            type = parseInt (value);
            if (jQuery.inArray (type, [ FIELD_TYPE_TEXT, FIELD_TYPE_NUMBER, FIELD_TYPE_PERCENTAGE, FIELD_TYPE_CURRENCY ]) !== -1) {
                field = row.find ('.field-length');
                value = field.val ();
                if ((value === undefined) || (value === null) || (value.trim () === '')) {
                    card.wizard.errorPopover (field, 'Introduce la longitud del campo');
                    field.focus ();
                    return false;
                } else if ((!jQuery.isNumeric (value)) || (value <= 0)) {
                    card.wizard.errorPopover (field, 'Introduce una longitud de campo mayor que cero');
                    field.focus ();
                    return false;
                }
            }

            if (jQuery.inArray (type, [ FIELD_TYPE_NUMBER, FIELD_TYPE_PERCENTAGE, FIELD_TYPE_CURRENCY ]) !== -1) {
                field = row.find ('.field-precision');
                value = field.val ();
                if ((value === undefined) || (value === null) || (value.trim () === '')) {
                    card.wizard.errorPopover (field, 'Introduce la precisión del número');
                    field.focus ();
                    return false;
                } else if ((!jQuery.isNumeric (value)) || (value < 0)) {
                    card.wizard.errorPopover (field, 'Introduce una precisión de número mayor o igual que cero');
                    field.focus ();
                    return false;
                }
            }

            if (jQuery.inArray (type, [ FIELD_TYPE_PICKLIST, FIELD_TYPE_MULTI_SELECT, FIELD_TYPE_PIPELINE ]) !== -1) {
                field = row.find ('.field-picklist-values');
                value = field.val ();
                if ((value === undefined) || (value === null) || (value.trim () === '')) {
                    card.wizard.errorPopover (field, 'Introduce la lista de valores');
                    field.focus ();
                    return false;
                }
                values = value.split ('\n');
                if (values.length < 2) {
                    card.wizard.errorPopover (field, 'Introduce al menos dos valores');
                    field.focus ();
                    return false;
                }
            }

            if (jQuery.inArray (type, [ FIELD_TYPE_MODULE_REFERENCE, FIELD_TYPE_MODULE_RECORDS ]) !== -1) {
                field = row.find ('.field-referenced-module-name');
                value = field.val ();
                if ((value === undefined) || (value === null) || (value.trim () === '')) {
                    card.wizard.errorPopover (field, 'Selecciona el módulo de la lista');
                    field.focus ();
                    return false;
                }
            }

            if (jQuery.inArray (type, [ FIELD_TYPE_GLOBAL_PICKLIST ]) !== -1) {
                field = row.find ('.field-global-picklist');
                value = field.val ();
                if ((value === undefined) || (value === null) || (value.trim () === '')) {
                    card.wizard.errorPopover (field, 'Selecciona el campo especial');
                    field.focus ();
                    return false;
                }
            }
        }
        return true;
    };

    var validateFieldsAction = function(rows) {
        var isValidate = true;
        rows.each(function(index, tr) {
            var selectOne      = jQuery (tr).find ('select').eq (0),
                selectTwo      = jQuery (tr).find ('select').eq (1),
                selectThree    = jQuery (tr).find ('select').eq (2),
                selectFour     = jQuery (tr).find ('select').eq (3),
                idSelectecTree = selectThree.attr('id'),
                idSelectecFour = selectFour.attr('id');

            jQuery (tr).find('td').each(function (index, td) {
                jQuery(td).removeClass('has-error');
            });

            if (!validateField (selectOne)) {
                isValidate = false;
            }
            if (!validateField (selectTwo)) {
                isValidate = false;
            }
            if ((idSelectecTree !== null) && (idSelectecTree !== undefined) && (idSelectecTree.trim () !== '')) {
                if (!validateField(selectThree)) {
                    isValidate = false;
                }
            }
            if ((idSelectecFour !== null) && (idSelectecFour !== undefined) && (idSelectecFour.trim () !== '')) {
                if (!validateField(selectFour)) {
                    isValidate = false;
                }
            }
        });
        return isValidate;
    };

    var validateStart = function (card) {
        var isValidate = true,
            moduleName     = jQuery('#modulename-' + idFieldTable).val (),
            tableFieldName = card.el.find ('#tabla-name-' + idFieldTable),
            blockName      = card.el.find ('#block-label-' + idFieldTable),
            alertTitle     =  card.el.find ('#block-sequence-' + idFieldTable);
        card.el.find ('span[id ^= sp-]').html ('');
        card.el.find ('div[id ^= div-]').removeClass ('has-error');
        [tableFieldName, blockName, alertTitle].each(function (element, i) {
            var result = validateField (element);
            if (isValidate) {
                isValidate = result;
            }
        });
        if (isValidate) {
            if (GridFieldName === '') {
                isValidate = false;
                tableFieldName.parent ().addClass ('has-error');
                tableFieldName.parent ().find ('.help-block').html ('validando nombre...');
                var arguments = {
                    'module':        'Settings',
                    'action':        'AjaxTableFieldUtils',
                    'function':      'VALIDATE-NAME',
                    'tableFileName': tableFieldName.val(),
                    'flmodule':      moduleName,
                    'Ajax':          'true'
                };
                jQuery.ajax ('index.php', {
                    async: false,
                    data: arguments,
                    method:   'post'
                }).done (function (data) {
                    try {
                        var message = JSON.parse (JSON.stringify (data));
                        if(message.error !== 'OK') {
                            throw message.error;
                        } else {
                            isValidate          = true;
                            TABLE_NAME_VALIDATE = true;
                            tableFieldName.parent ().removeClass ('has-error');
                            tableFieldName.parent ().find ('.help-block').html ('');
                        }
                    }
                    catch (e) {
                       alert(e);
                        tableFieldName.parent ().find ('.help-block').html (e);
                        isValidate          = false;
                        TABLE_NAME_VALIDATE = false;
                    }
                });
                codeType.val (area.val ())
            } else {
                isValidate = true;
            }
        } else {
            isValidate = false;
        }
        return isValidate;
    };

    var validateStepOne = function (card) {
        var blocksFields = card.el.find ('.block-fields'),
            fields       = card.el.find ('.field'),
            i, blockField;

        if (card.isDisabled ()) {
            return true;
        }

        card.wizard.hidePopovers ();
        if (fields.length === 0) {
            card.wizard.errorPopover (jQuery (blocksFields[ 0 ]).find ('table tfoot'), 'Agrega al menos un campo');
            return false;
        } else {
            for (i = 0; i < blocksFields.length; i += 1) {
                blockField = jQuery (blocksFields [ i ]);
                if (blockField.find ('.field').length === 0) {
                    card.wizard.errorPopover (blockField.find ('table tfoot'), 'Agrega al menos un campo');
                    return false;
                }
            }
            return validateFields (card, fields);
        }
    };

    var validateAppearance = function (card) {
        var isValidate   = true,
            infoValidate = '',
            rows       = jQuery ('#tbody-table-appearance-' + idFieldTable).find('tr'),
            totalWidth = 0;

        rows.each(function(index, tr) {
            var width      = jQuery (tr).find ('input').eq (2),
                titleStyle = jQuery (tr).find('textarea').eq(0).val (),
                allStyles  = [],
                singleStyle = [],
                numWidth  = parseInt (width.val());
            jQuery (tr).find('td').each(function (index, td) {
                jQuery(td).find('div').removeClass('has-error');
                jQuery(td).find('span').html('');
            });

            if (validateField (width)) {
                if (numWidth > 100) {
                    isValidate = false;
                    infoValidate += 'El ancho de la columna (' + index + ') no puede ser mayor que 100%' + '\n';
                } else if (numWidth < 10) {
                    isValidate = false;
                    infoValidate += 'El ancho de la columna (' + index + ') no puede ser menor que 10%' + '\n';
                }
            }  else {
                numWidth = 0;
                infoValidate += 'El ancho de la columna (' + index + ') no puede estar vacío 0%' + '\n';
            }

            if ((titleStyle !== null) || (titleStyle !== undefined) || (titleStyle.trim () !== '')) {
                allStyles = titleStyle.split(';');
                for (var k = 0; k < allStyles.length; k++) {
                    if (allStyles[k] !== '') {
                        if (allStyles[k].search(':') === -1) {
                            isValidate    = false;
                            infoValidate += 'El estilo de la columna (' + index + ') parece que no tiene el formato correcto' + '\n';
                        } else {
                            singleStyle = allStyles[k].split(':');
                            if ((singleStyle.length !== 2) || singleStyle[1] === '') {
                                isValidate    = false;
                                infoValidate += 'El estilo de la columna (' + index + ') parece que no tiene el formato correcto' + '\n';
                            }
                        }
                    }
                }
            }
            totalWidth += numWidth;
        });
        if (totalWidth > 100) {
            isValidate = false;
            infoValidate += 'La suma de los anchos de las columnas no puede ser mayor que 100%';
        }
        if (!isValidate) {
            alert (infoValidate);
        }
        return isValidate;
    };

    var validateActions = function (card) {
        var dataForm      = jQuery ('#' + card.name + '-section-' + idFieldTable + '  > .data-section'),
            operationRows = jQuery ('#tbody-operation-' + idFieldTable).find('tr'),
            summaryRows   = jQuery('#tbody-summary-' + idFieldTable).find('tr'),
            k             = 0,
            isValidate    = true,
            infoValidate  = 'Oops! se encontró errores en: ' + '\n',
            data, field, tBody, totalRows, value, infoText = '';

        if (relatedModuleFields.length > 0) {
            var isValidateRelateModule = true;
            for (k = 0; k < relatedModuleFields.length; k++) {
                data      = relatedModuleFields[ k ].split ('@');
                tBody     = jQuery ('#tbody-' + data[ 3 ] + '-' + idFieldTable);

                totalRows = tBody.find ('tr');
                if (totalRows.length > 0) {
                    isValidateRelateModule =  validateFieldsAction (totalRows);
                }
            }
            if (!isValidateRelateModule) {
                infoValidate += '.- Importar Valores' + '\n';
                isValidate    = false;
            }
        }
        if (listFields.length > 0 && textFields.length > 0) {
            var isValidateList = true;
            for (k = 0; k < listFields.length; k++) {
                data      = listFields[ k ].split ('@');
                tBody     = jQuery ('#tbody-' + data[ 1 ] + '-' + k);
                totalRows = tBody.find('tr');
                if (totalRows.length > 0) {
                    isValidateList =  validateFieldsAction (totalRows);
                }
            }
            if (!isValidateList) {
                infoValidate += '.- Vincular listas' + '\n';
                isValidate    = false;
            }
        }
        if (checkBoxFields.length > 0) {
            var isValidateCheck = true;
            for (k = 0; k < checkBoxFields.length; k++) {
                data      = checkBoxFields[ k ].split ('@');
                tBody     = jQuery ('#tbody-' + data[ 1 ] + '-' + idFieldTable);

                totalRows = tBody.find ('tr');
                if (totalRows.length > 0) {
                    isValidateCheck =  validateFieldsAction (totalRows);
                }
            }
            if (!isValidateCheck) {
                infoValidate += '.- Activaciones' + '\n';
                isValidate    = false;
            }
        }
        if (operationRows.length > 0) {
            var isValidateOperations =  validateFieldsAction (operationRows);
            if (!isValidateOperations) {
                infoValidate += '.- Operaciones' + '\n';
                isValidate    = false;
            }
        }

        if (summaryRows.length > 0) {
            var isValidateSummary =  validateFieldsAction (summaryRows);
            if (!isValidateSummary) {
                infoValidate += '.- Fila resumen' + '\n';
                isValidate    = false;
            }
        }

        if (!isValidate) {
            alert (infoValidate);
        }
        return isValidate;

    };

    var addActionsToRows = function (card) {
        var hasDiff     = false,
            appearance  = jQuery ('#tbody-table-appearance-' + idFieldTable),
            summaryRows = jQuery ('#tbody-summary-' + idFieldTable);
        wizard.backButton.show ();
        if (
            (relatedModuleFields.length > 0) ||
            (listFields.length > 0) ||
            (checkBoxFields.length > 0) ||
            (numericFields.length > 0) ||
            (textFields.length > 0)
        ) {
            hasDiff = searchDiff ();
            if (hasDiff) {
                alert ('En vista de que las columnas en la tabla han sido actualizadas, se deben reconfigurar las acciones entre campos');
                relatedModuleFields = [];
                listFields          = [];
                checkBoxFields      = [];
                textFields          = [];
                numericFields       = [];
                summaryRows.empty();
                appearance.empty();
                getSelectedFields ();
            }
        } else {
            getSelectedFields ();
            hasDiff = true;
        }
        if ((relatedModuleFields.length > 0) && hasDiff) {
             setImportAction (card, hasDiff)
        }
        if (listFields.length > 0 && hasDiff) {
            setRelatedList (card, hasDiff)
        }
        if (checkBoxFields.length> 0 && hasDiff) {
            setActivationAction (card, hasDiff)
        }
        if (numericFields.length >= 3 && hasDiff) {
            setOperationMatch (card, hasDiff)
        }
        if (GridFieldName !== '') {
            jQuery ('#btn-ADD-SUMMARY-ROW-' + idFieldTable).trigger ('onclick');
            GridFieldName = '';
        }
    };

    var addAppearance = function (card) {
        var arguments,
            columns  = [],
            rows     = jQuery ('#group-table-fields-' + idFieldTable).find('tr'),
            tBody    = jQuery ('#tbody-table-appearance-' + idFieldTable),
            flmodule = jQuery ('#modulename-' + idFieldTable).val ();

        if (tBody.html() !== '') {
            return false;
        } else {
            tBody.html('<img src="themes/images/loading.gif" class="img-responsive" style="display: inline-block;" />');
        }
        rows.each(function(index, tr) {
            var fieldName  = jQuery (tr).find ('input').eq (0).val (),
                fieldLabel = jQuery (tr).find ('input').eq (1).val ();
            columns.push (fieldLabel + '@' + fieldName);
        });

        arguments = {
            'module':        'Settings',
            'action':        'AjaxTableFieldUtils',
            'function':      'APPEARANCE',
            'columms':       columns,
            'tableFileName': FieldNameAppearance,
            'reModule':      flmodule,
            'Ajax':          'true'
        };
        jQuery.post('index.php', arguments, function (data) {
            try {
                var message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    tBody.html (message.html);
                }
            }
            catch (e) {
                alert(e);
            }
        });
    };

    var submitWizard = function (wizard) {
        if (infoSave !== '') {
            alert(infoSave);
        }
        jQuery.ajax ('index.php', {
            data:     wizard.serialize (),
            dataType: 'json',
            method:   'post'
        }).done (function () {
            wizard.submitSuccess ();
            wizard.hideButtons ();
        }).fail (function (jQueryResponse) {
            wizard.el.find ('.wizard-failure .message').text (jQueryResponse.responseJSON);
            wizard.submitFailure ();
            wizard.hideButtons ();
        });

    };

    var addFieldsToTable = function (card) {
        var fieldTableName = wizard.cards [ 'start' ].el.find ('#tabla-name-' + idFieldTable).val(),
            re =           new RegExp('__FIELD_ID__', 'g'),
            i, block, blockId, blockFields, blockField, fields;
        if (!TABLE_NAME_VALIDATE) {
            jQuery ('.wizard-back').trigger ('click');
        }
        if (card.isDisabled ()) {
            return;
        }
        card.el.find('#block-fields-' + idFieldTable).html('Campos de la tabla: ' + fieldTableName);
        card.el.find('.data-section').html().replace(re, totalFields);
        totalFields += 1;

    };

    var setWindowsSize = function (card) {
        var cardName  = card.name,
            footerTop = 0,
            cardContainer = jQuery ('.wizard-card-container'),
            wizardCards  = jQuery ('.wizard-cards'),
            wizardModal  = jQuery ('.wizard-modal');
        if (cardName === 'step-1') {
            cardContainer.animate ({
                height: '120px'
            },'slow');
            wizardModal.animate ({
                height: '120px'
            },'slow');
            cardContainer.css ('min-height', '120px');
            jQuery ('#step-1-section').css ('height', '70px');

        } else if (cardName === 'step-2') {
            cardContainer.animate({
                height: '465px'
            },'slow');
            wizardModal.animate({
                height: '650px'
            },'slow');
            cardContainer.css('min-height', '465px');
            jQuery('#step-2-section').css('min-height', '465px');
        } else if (cardName === 'step-3') {
            cardContainer.animate({
                height: '475px'
            },'slow');
            cardContainer.css ('min-height', '475px');
            wizardModal.animate ({
                height: '660px'
            }, 'slow');
            wizardCards.css('min-height', '475px');
            jQuery('#step-3-section').css('min-height', '475px');
        }
    };

    // public method
    var addField = function (buttonElement) {
        var card        = wizard.cards [ 'step-1' ],
            lastIndex   = parseInt (jQuery (buttonElement).attr('rel')),
            blockFields = jQuery (buttonElement).closest ('.block-fields'),
            fields      = blockFields.find ('.field');

        card.wizard.hidePopovers ();

        if ((fields.length > 0) && (!validateFields (card, fields))) {
            return;
        }

        lastIndex += 1;
        blockFields.find ('table > tbody').append (jQuery ('#module-creator-wizard-field-template').html ().replace (/__FIELD_ID__/g, lastIndex));
        jQuery (buttonElement).attr('rel', lastIndex.toString())
        FieldNameAppearance = '';
    };

    var addOperationRow = function (btn, id) {
        var button    = jQuery (btn),
            tBody     = jQuery ('#' + id),
            rows      = tBody.find ('tr'),
            flmodule  = jQuery ('#modulename-' + idFieldTable).val ();
            arguments = {
                'module':        'Settings',
                'action':        'AjaxTableFieldUtils',
                'function':      'OPERATION-ROW',
                'columns':       numericFields,
                'tableFileName': GridFieldName,
                'reModule':      flmodule,
                'Ajax':          'true'
            };
        if (numericFields.length === 0) {
            alert('Oops!, incluir al tres columnas de tipo númerica');
            return false;
        }
        if (rows.length >= numericFields.length) {
            alert('Oops!, ya has incluido todas las columnas disponibles');
            return false;
        }

        button.attr('disabled','disabled');
        button.parent().find('span').eq(0).removeClass('hide');
        jQuery.post('index.php', arguments, function (data) {
            try {
                var message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    tBody.append(message.html);
                    button.removeAttr('disabled');
                    button.parent().find('span').eq(0).addClass('hide');
                }
            }
            catch (e) {
                alert(e);
                button.removeAttr('disabled');
                button.parent().find('span').eq(0).addClass('hide');
            }
        });

    };

    var addRowToActivation  = function (btn, id, checkboxName) {
        var button    = jQuery(btn),
            tBody     = jQuery('#' + id),
            rows      = tBody.find ('tr'),
            muColumns = textFields.concat (numericFields),
            flmodule  = jQuery ('#modulename-' + idFieldTable).val (),
            arguments = {
                'module':       'Settings',
                'action':       'AjaxTableFieldUtils',
                'checkbox':     checkboxName,
                'function':     'FIELD-TO-ACTIVATION',
                'columns':       muColumns,
                'tableFileName': GridFieldName,
                'reModule':      flmodule,
                'idlinkage':     button.attr('data-id-linkage'),
                'Ajax':          'true'
            };
        if (muColumns.length === 0) {
            alert('Oops!, incluir al menos una columna de tipo texto o númerica');
            return false;
        }
        if (rows.length >= muColumns.length) {
            alert('Oops!, ya has incluido todas las columnas disponibles');
            return false;
        }
        button.attr('disabled','disabled');
        button.parent().find('span').eq(0).removeClass('hide');
        jQuery.post('index.php', arguments, function (data) {
            try {
                var message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    tBody.append(message.html);
                    button.removeAttr('disabled');
                    button.parent().find('span').eq(0).addClass('hide');
                }
            }
            catch (e) {
                alert(e);
                button.removeAttr('disabled');
                button.parent().find('span').eq(0).addClass('hide');
            }
        });
    };

    var addRowToImport = function (btn, id, moduleName, fieldName) {
        var button    = jQuery(btn),
            row       = jQuery('#' + id),
            muColumns = textFields.concat (numericFields),
            arguments = {
                'module':       'Settings',
                'action':       'AjaxTableFieldUtils',
                'flmodule':      moduleName,
                'fieldName':     fieldName,
                'reModule':      button.attr('data-related-module'),
                'function':      'FIELD-TO-IMPORT',
                'columns':       muColumns,
                'tableFileName': GridFieldName,
                'idlinkage':     button.attr('data-id-linkage'),
                'Ajax':          'true'
            };
        if (muColumns.length === 0) {
            alert('Oops!, incluir al menos una columna de tipo texto o númerica');
            return false;
        }
        button.attr('disabled','disabled');
        button.parent().find('span').eq(0).removeClass('hide');
        jQuery.post('index.php', arguments, function (data) {
            try {
                var message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    row.append(message.html);
                    button.removeAttr('disabled');
                    button.parent().find('span').eq(0).addClass('hide');
                }
            }
            catch (e) {
                alert(e);
                button.removeAttr('disabled');
                button.parent().find('span').eq(0).addClass('hide');
            }
        });
    };

    var addSummaryRow = function (btn, id) {
        var button    = jQuery (btn),
            tBody     = jQuery ('#' + id),
            rows      = tBody.find ('tr'),
            muColumns = textFields.concat (numericFields),
            flmodule  = jQuery ('#modulename-' + idFieldTable).val (),
            arguments = {
                'module':        'Settings',
                'action':        'AjaxTableFieldUtils',
                'function':      'SUMMARY-ROW',
                'columns':       muColumns,
                'tableFileName': GridFieldName,
                'reModule':      flmodule,
                'Ajax':          'true'
            };
        if (muColumns.length === 0) {
            alert('Oops!, incluir al menos una columna de tipo texto o númerica');
            return false;
        }
        if (rows.length >= muColumns.length) {
            alert('Oops!, ya has incluido todas las columnas disponibles');
            return false;
        }

        button.attr('disabled','disabled');
        button.parent().find('span').eq(0).removeClass('hide');
        jQuery.post('index.php', arguments, function (data) {
            try {
                var message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    tBody.append(message.html);
                    button.removeAttr('disabled');
                    button.parent().find('span').eq(0).addClass('hide');
                }
            }
            catch (e) {
                alert(e);
                button.removeAttr('disabled');
                button.parent().find('span').eq(0).addClass('hide');
            }
        });
    };

    var closeCreatorWizard = function () {
        if (wizard) {
            wizard.reset ().close ().trigger ('closed');
        }
    };

    var openModalWizard = function (moduleName, template, record) {
        var template = jQuery ('#' + template); //table-fields-wizard-template
        wizard = jQuery (template.html ()).wizard ({
            backdrop:   'static',
            showCancel: true,
            buttons:    {
                cancelText:     'Cancelar',
                nextText:       'Siguiente →',
                backText:       '← Atrás',
                submitText:     'Guardar',
                submittingText: 'Guardando...'
            },
            baseHeight: 0
        });

        if (moduleName) {
            wizard.cards [ 'start' ].el.closest ('form').find ('input[name="modulename"]').val (moduleName);
            wizard.cards [ 'start' ].el.find ('#new-notify-options').hide ().find ('input[name="wizardaction"]').prop ('disabled', true);
            wizard.cards [ 'start' ].el.find ('#existing-notify-options').show ().find ('input[name="wizardaction"]').prop ('disabled', false).first ().prop ('checked', true);
            idFieldTable = wizard.cards [ 'start' ].el.closest ('form').find ('#idFieldTable').val ();
            GridFieldName       = record;
            FieldNameAppearance = record;
        } else {
            wizard.cards [ 'start' ].el.closest ('form').find ('input[name="record"]').val ('');
            wizard.cards [ 'start' ].el.find ('#existing-notify-options').hide ().find ('input[name="wizardaction"]').prop ('disabled', true);
            wizard.cards [ 'start' ].el.find ('#new-notify-options').show ().find ('input[name="wizardaction"]').prop ('disabled', false).first ().prop ('checked', true);

        }
        //define table
        wizard.cards [ 'start' ].on ('validate', validateStart);
        //add column ontable
        wizard.cards [ 'step-1' ].on ('validate', validateStepOne)
                                 .on ('selected', addFieldsToTable);
        // action on row table
        wizard.cards [ 'step-2' ].on ('validate', validateActions)
                                 .on ('selected', addActionsToRows);
        // aspecto
        wizard.cards [ 'step-3' ].on ('validate', validateAppearance)
                                 .on ('selected', addAppearance);
        wizard.on ('submit', submitWizard).on ('closed', destroyWizard)
                                          .on ('incrementCard', updateProgressBar)
                                          .on ('decrementCard', updateProgressBar);
        wizard.show ();
        jQuery ('.wizard-modal .wizard-nav-item > .wizard-nav-link').on ('click', function (evt) {
            var link  = jQuery (this),
                links = link.closest ('.nav-list').find ('.wizard-nav-item'),
                i, requestedCardIndex, activeCardIndex;
            if ((!link.closest ('.wizard-nav-item').hasClass ('already-visited')) || (links.length === 0)) {
                return;
            }

            requestedCardIndex = null;
            for (i = 0; i < links.length; i += 1) {
                if (link.text () === jQuery (links [ i ]).text ()) {
                    requestedCardIndex = i;
                    break;
                }
            }

            evt.preventDefault ();
            evt.stopPropagation ();
            if (!requestedCardIndex) {
                return;
            }

            activeCardIndex = wizard.getActiveCard ().index;
            if (activeCardIndex > requestedCardIndex) {
                for (i = activeCardIndex; i > requestedCardIndex; i -= 1) {
                    wizard.decrementCard ();
                }
            } else if (activeCardIndex < requestedCardIndex) {
                for (i = activeCardIndex; i < requestedCardIndex; i += 1) {
                    wizard.incrementCard ();
                }
            }
        });
    };

    var deleteRow = function (buttonElement) {
        if (!confirm ('¿Estás seguro que quieres eliminar el elemento seleccionado?')) {
            return;
        }
        jQuery (buttonElement).closest ('tr').remove ();
        FieldNameAppearance = '';
        if (GridFieldName !== '') {
            infoSave      = 'Ha cambiado la estructura de la tabla!, es probable que la información registrda, previamente en la tabla, se halla perdido o encuentre inconsistencia en la misma';
            GridFieldName = '';
        }

    };

    var normalizeFieldContents = function (fieldElement) {
        var field = jQuery (fieldElement);
        field.val (getNormalizedText (field.val ()));
    };

    window.TableFieldUtils = {
        addField:                addField,
        addOperationRow:         addOperationRow,
        addRowToActivation:      addRowToActivation,
        addRowToImport:          addRowToImport,
        addSummaryRow:           addSummaryRow,
        closeCreatorWizard:      closeCreatorWizard,
        deleteField:             deleteRow,
        deleteOperation:         deleteRow,
        deleteRowActivation:     deleteRow,
        deleteRowLinkage:        deleteRow,
        deleteSummary:           deleteRow,
        selectedFieldActivation: selectedFieldActivation,
        selectedFieldImport:     selectedFieldImport,
        setFieldType:            setFieldType,
        setGlobalPicklistFieldName: setGlobalPicklistFieldName,
        selectedOperationColumn: selectedOperationColumn,
        selectedSummaryColumn:   selectedSummaryColumn,
        normalizeFieldContents:  normalizeFieldContents,
        openModalWizard:         openModalWizard
    };

    jQuery ('#event-parameter').keydown (function (e) {
        if (jQuery.inArray (e.keyCode, [ 46, 8, 9, 27, 13, 110 ]) !== -1 ||
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode >= 35 && e.keyCode <= 40 && (e.keyCode == 188 || e.keyCode == 190) )) {
            return;
        }
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault ();
        }
    });

    var onDocumentReadyHandler = function () {

    };

    jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));