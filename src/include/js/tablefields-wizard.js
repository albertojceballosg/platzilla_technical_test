(function (jQuery) {
    //private variables
    var idFieldTable                = '',
        FIELD_TYPE_CURRENCY         = 71,
        FIELD_TYPE_GLOBAL_PICKLIST  = 16,
        FIELD_TYPE_MODULE_RECORDS   = 404,
        FIELD_TYPE_MODULE_REFERENCE = 10,
        FIELD_TYPE_MULTI_SELECT     = 33,
        FIELD_TYPE_NUMBER           = 7,
        FIELD_TYPE_PERCENTAGE       = 9,
        FIELD_TYPE_PICKLIST         = 15,
        FIELD_TYPE_PIPELINE         = 8192,
        FIELD_TYPE_TEXT             = 1,
        relatedSummaryField         = '';

    var wizard              = null,
        totalBlocks         = 0,
        totalFields         = 0,
        totalRelatedLists   = 0,
        relatedModuleFields = [],
        listFields          = [],
        checkBoxFields      = [],
        textFields          = [],
        numericFields       = [];

    // private method
    var americanToEuropean = function (number) {
        return number.toLocaleString('de-DE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    var europeanToAmerican = function (numberString) {
        return parseFloat(numberString.replace(/\./g, '').replace(',', '.'));
    }

    var setOperationResult = function (numFormat, results, c) {
        if (numFormat === 'EUROPEAN_FORMAT') {
            c.val (americanToEuropean (results));
        } else {
            c.val (results.toFixed(2));
        }
    }

    var updateTableField = function (idTable, idRow) {
        var tFoot             = jQuery ('#tfoot-' + idTable),
            tableName         = tFoot.attr('data-field-name'),
            tBody             = jQuery('#tbody-' + tableName + '-' + idTable),
            summaryDat        = tFoot.attr('data-summary-row'),
            summaryAction     = (tFoot.attr('data-summary-row') !== '') ? JSON.parse (tFoot.attr('data-summary-row')) : '',
            operationAction   = (tFoot.attr('data-operation-row') !=='')? JSON.parse (tFoot.attr('data-operation-row')) : '',
            operations        = Object.values (operationAction.operator),
            fieldsA           = Object.values (operationAction.fildnameA),
            fieldsB           = Object.values (operationAction.filenameB),
            fieldsC           = Object.values (operationAction.filenameD),
            summaryFields     = Object.values (summaryAction.filename),
            relatedSummary    = tFoot.attr('data-realted-summary-field'),
            summaryOperations = Object.values (summaryAction.operation),
            totalRows         = tBody.find('tr').length,
            numFormat         = tBody.attr('data-num-format'),
            summaryColumn     = 0;
            console.log(operationAction);
            console.log(numFormat)
        for (var k = 0; k <  operations.length ; k++) {
            var objectA = jQuery('#' + fieldsA[ k ] + '-' + idRow),
                uiTypeA = objectA.attr ('rel'),
                factorA = (uiTypeA === '9') ? 100 : 1,
                a       = (numFormat === 'EUROPEAN_FORMAT') ? europeanToAmerican (objectA.val()) / factorA : parseFloat (objectA.val()) / factorA,
                objectB = jQuery('#' + fieldsB[ k ] + '-' + idRow),
                uiTypeB = objectB.attr ('rel'),
                factorB = (uiTypeB === '9') ? 100 : 1,
                b       = (numFormat === 'EUROPEAN_FORMAT') ? europeanToAmerican (objectB.val()) / factorB : parseFloat (objectB.val()) / factorB,
                c       = jQuery('#' + fieldsC[ k ] + '-' + idRow),
                uiTypeC = c.attr ('rel'),
                factorC  = (uiTypeC === '9') ? 100 : 1,
                results;

            if (operations[ k ] === 'MULTIPLY') {
                if ((a === '') || (a === undefined) ||(a === 'undefined') || isNaN(a)) {
                    a = 1;
                }
                if ((b === '') || (b === undefined) ||(b === 'undefined') || isNaN(b)) {
                    b = 1;
                }

                results = (a * b);
                results = results * factorC;
                setOperationResult (numFormat, results, c);
            } else if (operations[ k ] === 'RULE_THREE') {
                if ((a === '') || (a === undefined) ||(a === 'undefined') || isNaN(a)) {
                    a = 1;
                }
                if ((b === '') || (b === undefined) ||(b === 'undefined') || isNaN(b)) {
                    b = 0;
                }
                results = ((b * 100) / a);
                setOperationResult (numFormat, results, c);
            } else if (operations[ k ] === 'DIVIDE') {
                if ((a === '') || (a === undefined) ||(a === 'undefined') || isNaN(a)) {
                    a = 1;
                }
                if ((b === '') || (b === undefined) ||(b === 'undefined') || isNaN(b)) {
                    b = 1;
                }
                results = (a/b);
                setOperationResult (numFormat, results, c);
            } else if (operations[ k ] === 'ADD') {
                if ((a === '') || (a === undefined) ||(a === 'undefined') || isNaN(a)) {
                    a = 0;
                }
                if ((b === '') || (b === undefined) ||(b === 'undefined') || isNaN(b)) {
                    b = 0;
                }
                results = (a + b) * factorC;
                setOperationResult (numFormat, results, c);
            } else if (operations[ k ] === 'SUBTRACT') {
                if ((a === '') || (a === undefined) ||(a === 'undefined') || isNaN(a)) {
                    a = 0;
                }
                if ((b === '') || (b === undefined) ||(b === 'undefined') || isNaN(b)) {
                    b = 0;
                }
                results = (a - b) * factorC;
                setOperationResult (numFormat, results, c);
            }
        }

        for (var j = 0; j < summaryOperations.length; j++) {
            var summary = jQuery('#' + summaryFields[j] + '-' + idTable);
            if (parseFloat(summary.val()) !== 0) {
                summary.val('0.00');
            }
        }

        tBody.find('tr').each(function (index, tr) {
            var trIdRow = jQuery(tr).attr('data-row-id');

            for (var j = 0; j < summaryOperations.length; j++) {
                var fieldId = '#' + summaryFields[j] + '-' + trIdRow,
                    field = jQuery(tr).find(fieldId),
                    fieldValue = field.val(),
                    sum = 0,temp = 0,
                    summary = jQuery('#' + summaryFields[j] + '-' + idTable);
                if (
                    (fieldValue !== '') &&
                    (fieldValue !== undefined) &&
                    (fieldValue !== 'undefined')
                ) {
                    fieldValue = (numFormat === 'EUROPEAN_FORMAT') ? europeanToAmerican(fieldValue) : parseFloat(fieldValue);
                    if (summaryOperations[j] === 'SUM_COLUMN') {
                        sum = (numFormat === 'EUROPEAN_FORMAT') ? europeanToAmerican(summary.val()) : parseFloat(summary.val());
                        sum = sum + fieldValue;
                        if (numFormat === 'EUROPEAN_FORMAT') {
                            summary.val(americanToEuropean(sum));
                        } else {
                            summary.val(sum.toFixed(2));
                        }

                    } else if (summaryOperations[j] === 'COUNT_COLUMN') {
                        temp = (numFormat === 'EUROPEAN_FORMAT') ? europeanToAmerican(summary.val()) : parseFloat(summary.val());
                        temp = temp + 1;
                        if (numFormat === 'EUROPEAN_FORMAT') {
                            summary.val(americanToEuropean(temp));
                        } else {
                            summary.val(temp.toFixed(2));
                        }
                    } else if (summaryOperations[j] === 'AVERAGE_COLUMN') {
                        temp (numFormat === 'EUROPEAN_FORMAT') ? europeanToAmerican(summary.val()) : parseFloat(summary.val());
                        if ((index + 1) === totalRows) {
                            temp = (temp + fieldValue)/totalRows;
                        } else {
                            temp += fieldValue;
                        }
                        if (numFormat === 'EUROPEAN_FORMAT') {
                            summary.val(americanToEuropean(temp));
                        } else {
                            summary.val(temp.toFixed(2));
                        }
                    }
                }
            }
        });
        if (relatedSummary !== '') {
            jQuery ('#'+ relatedSummary).val(summary.val());
        }
    };

    // public method
    var addRowToTable = function (btn, tBodyId, moduleName) {
        var arguments      = {},
            isAddRow       = true,
            button         = jQuery (btn),
            dummy          = tBodyId.split('-'),
            tableFieldName = dummy[ 1 ],
            idFieldTable   = dummy[ 2 ],
            tBody          = jQuery ('#' + tBodyId),
            rows           = tBody.find ('tr'),
            lastRow;

        rows.each(function(index, tr) {
            var td  = jQuery (tr).find ('td').eq (0).attr('colspan');

            if ((index === 0) && (td !== 'undefined') && (td !== undefined) && (td !== '') ) {
                isAddRow = false;
            }
        });

        arguments = {
            'module':   moduleName,
            'action':   'AjaxTableFieldUtils',
            'flmodule': moduleName,
            'function': 'ADD-ROW-TABLE',
            'idtable':   idFieldTable,
            'fieldname': tableFieldName,
            'Ajax':     'true'
        };

        button.attr('disabled','disabled');
        button.parent().find('span').eq(0).removeClass('hide');
        jQuery.post('index.php', arguments, function (data) {
            try {
                var message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    if (isAddRow) {
                        tBody.append(message.html);
                    } else {
                        tBody.empty();
                        tBody.html (message.html);
                    }
                    button.removeAttr('disabled');
                    button.parent().find('span').eq(0).addClass('hide');
                    lastRow = tBody.find('tr:last').find('td').each(function (index, td) {
                        var checkBox = jQuery(td).find('input[type=checkbox]').eq(0);

                        if ((checkBox !== undefined) && (checkBox !== 'undefined')) {
                            checkBox.trigger('onclick')
                        }
                    })
                }
            }
            catch (e) {
                alert(e);
                button.removeAttr('disabled');
                button.parent().find('span').eq(0).addClass('hide');
            }
        });
    };

    var deleteRow = function (buttonElement, tr, idTable) {
        var dummy = tr.split('-');
        if (!confirm ('¿Estás seguro que quieres eliminar el elemento seleccionado?')) {
            return;
        }
        jQuery (buttonElement).closest ('tr').remove ();

        updateTableField(idTable, dummy[2])
    };

    var moveRowUp = function (btn, tr) {
        var rowToMove = jQuery ('#' + tr),
            prev      = rowToMove.prev ('tr.tabla-field-row');
        prev.before (rowToMove);
    };
    var moveRowDown = function (btn, tr) {
        var rowToMove = jQuery ('#' + tr),
            next      = rowToMove.next ('tr.tabla-field-row');
        next.after (rowToMove);
    };


    var relatedModuleUpdate = function (obj,data, moduleName) {
        var actionData   = JSON.parse (JSON.stringify (data)),
            arguments    = {},
            fieldObject  = jQuery (obj),
            moduleFields = actionData.import.modulefield,
            tableFields = actionData.import['tablefield'],
            importantIds = fieldObject.attr('data-row-ids').split('@'),
            row          = jQuery ('#tr-row-' + importantIds[ 1 ]),
            tFoot        = jQuery ('#tfoot-' + importantIds[ 0 ]),
            tFootBtn     = tFoot.find('button').eq (0);

        tFootBtn.attr ('disabled','disabled');
        tFootBtn.parent().find('span').eq(0).removeClass('hide');

        row.find('button').each(function (index, btn) {
            jQuery(btn).attr('disabled','disabled');
        });

        arguments = {
            'module':   moduleName,
            'action':   'AjaxTableFieldUtils',
            'flmodule': actionData.relatedmodule,
            'function': 'RELATED-MODULE-ACTION',
            'idtable':  importantIds[ 0 ],
            'crmid':    fieldObject.val (),
            'Ajax':     'true'
        };

        jQuery.post('index.php', arguments, function (data) {
            try {
                var message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    var columnFields = message.html;
                    for (var k = 0; k < tableFields.length; k++) {
                        var colummName = tableFields[ k ],
                            fieldName  = moduleFields[ k ],
                            field      = jQuery ('#' + colummName + '-' + importantIds [1]);
                        field.val (columnFields[fieldName]);
                        field.attr('readonly', true);

                    }

                    tFootBtn.removeAttr('disabled');
                    tFootBtn.parent().find('span').eq(0).addClass('hide');

                    row.find('button').each(function (index, btn) {
                        jQuery(btn).removeAttr('disabled');
                    });
                    updateTableField(importantIds[ 0 ], importantIds[ 1 ])
                }
            }
            catch (e) {
                alert(e);
                tFootBtn.removeAttr('disabled');
                tFootBtn.parent().find('span').eq(0).addClass('hide');

                row.find('button').each(function (index, btn) {
                    jQuery(btn).removeAttr('disabled');
                });
            }
        });
    };

    var updateNumFields = function (obj, idTable, idRow) {
        var field = jQuery (obj);
        field.val(field.val ().replace (/[^\d.,-]/g, ''));
        updateTableField(idTable, idRow)
    };

    var updateCheckBoxFields = function (obj, idTable, idRow, data, moduleName) {
        var actionData = JSON.parse (JSON.stringify (data)),
            actionArr  = actionData.action,
            columnsArr = actionData.fieldname,
            checkBox   = jQuery (obj),
            checkBoxValue = checkBox.parent ().find ('input[type=hidden]').eq (0),
            status, index;

        if (checkBox.is(':checked')){
            checkBoxValue.val('on');
            for (var k = 0; k < columnsArr.length; k++) {
                if(actionArr[ k ] === 'ENABLED') {
                    jQuery('#' + columnsArr[ k ] + '-' + idRow).removeAttr('readonly')
                } else {
                    jQuery('#' + columnsArr[ k ] + '-' + idRow).attr('readonly', true)

                }
            }
        } else {
            checkBoxValue.val('off');
            status = 'DISABLED';
            for (k = 0; k < columnsArr.length; k++) {
                if(actionArr[ k ] === 'DISABLED') {
                    jQuery('#' + columnsArr[ k ] + '-' + idRow).removeAttr('readonly')
                } else {
                    jQuery('#' + columnsArr[ k ] + '-' + idRow).attr('readonly', true)
                }
            }
        }

    };

    var updatePickListFields = function (obj, idTable, idRow, data, moduleName) {
        var actionData     = JSON.parse (JSON.stringify (data)),
            arguments      = {},
            optionSelected = jQuery(obj).val (),
            optionsArr     = actionData.list['option'],
            columnsArr     = actionData.list['column'],
            pickListArr    = actionData.list['picklist'],
            fieldName, pickListDv, textDv, index;

        if(optionSelected === '') {
            for (var k = 0; k < columnsArr.length; k++) {
                pickListDv = jQuery('#list-' + columnsArr[ k ] + '-' + idRow);
                textDv     = jQuery('#input-' + columnsArr[ k ] + '-' + idRow);

                if (textDv.hasClass ('hide')) {
                    textDv.removeClass ('hide');
                    pickListDv.empty();
                    pickListDv.addClass ('hide');
                    jQuery('#' + columnsArr[ k ] + '-' + idRow).removeAttr('disabled');
                }
            }
        } else {
            for (k = 0; k < optionSelected.length; k++) {
                if ((optionSelected === optionsArr[ k ])) {
                    index = k;
                    break;
                }
            }
            if (columnsArr[ index ] !== 'DO_NOT_LINK') {
                fieldName = jQuery('#' + columnsArr[index] + '-' + idRow).attr('name');
                pickListDv = jQuery('#list-' + columnsArr[index] + '-' + idRow);
                textDv = jQuery('#input-' + columnsArr[index] + '-' + idRow);
                arguments = {
                    'module': moduleName,
                    'action': 'AjaxTableFieldUtils',
                    'function': 'PICKLIST-ACTION',
                    'idtable': idTable,
                    'idrow': idRow,
                    'fieldname': fieldName,
                    'column': columnsArr[index],
                    'picklist': pickListArr[index],
                    'Ajax': 'true'
                };

                jQuery.post('index.php', arguments, function (data) {
                    try {
                        var message = JSON.parse(JSON.stringify(data));
                        if (message.error !== 'OK') {
                            throw message.error;
                        } else {
                            pickListDv.empty();
                            jQuery('#' + columnsArr[index] + '-' + idRow).attr('disabled', true)
                            pickListDv.html(message.html);
                            pickListDv.removeClass('hide');
                            textDv.addClass('hide');
                        }
                    }
                    catch (e) {
                        alert(e);
                    }
                });
            }
        }
    };

    window.TableFieldUtils = {
        addRowToTable:        addRowToTable,
        delRowToTable:        deleteRow,
        moveRowDown:          moveRowDown,
        moveRowUp:            moveRowUp,
        relatedModuleUpdate:  relatedModuleUpdate,
        updateNumFields:      updateNumFields,
        updateCheckBoxFields: updateCheckBoxFields,
        updatePickListFields: updatePickListFields
    };

    var onDocumentReadyHandler = function () {
    };

    jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));