(function (jQuery) {
    // private method
    var clearResource = function (id) {
        var fieldDisplay = jQuery ('#edit_resource_initiative-id-' + id + '_display'),
            fieldValue   = jQuery ('#resource_initiative-id-' + id),
            modalDiv     = jQuery ('#resource-initiative-modal-' + id);

        modalDiv.attr ('data-referenced-module', '');
        modalDiv.attr ('data-title', '');
        fieldValue.val ('');
        fieldDisplay.val ('');

    };

    var setContributionFactor = function () {
        var fields            = jQuery ('input[id ^= contribution_factor-]'),
            fieldsId          = [],
            sumContribution   = 0,
            totalContribution = 100,
            totalFiles        = fields.length;

        if (totalFiles === 1) {
            jQuery (fields[0]).val(100);
        } else {
            fields.each (function (index, obj) {
                var field = jQuery (obj);
                if (field.val ()) {
                    sumContribution += parseFloat (field.val());
                } else {
                    fieldsId.push(field.attr('id'))
                }
            });

            totalContribution -= sumContribution;
            if (totalContribution <= 0) {
                totalContribution = 0;
            } else if (fieldsId.length > 0) {
                totalContribution = (totalContribution / (fieldsId.length))
            }
            for (var k = 0; k < fieldsId.length; k++) {
                jQuery('#' + fieldsId[k]).val (totalContribution.toString());

            }
            if (sumContribution > 100) {
                alert('La suma de los factores de contibución no puedeser mayor que 100%')
            }
        }
    };

    var setTotalContribution = function (id) {
        var contributionFactor  = parseFloat (jQuery ('#contribution_factor-' + id).val()),
            resourceProgress    = parseFloat (jQuery ('#resource_progress-' + id).val ()),
            idTable             = jQuery ('#tr-row-' + id).attr('data-id-table'),
            summaryFactor       = jQuery ('#total_contribution_factor-' + idTable),
            summaryContribution = jQuery ('#total_total_contribution-' + idTable),
            tBody               = jQuery ('#tbody-resource-initiative-' + idTable ),
            total               = 0,
            totalContribution   = jQuery ('#total_contribution-' + id),
            multiplication      =  0;

        if (
            !isNaN (contributionFactor) &&
            !isNaN (resourceProgress)
        )  {
            multiplication = (contributionFactor * resourceProgress) / 100;
        }
        totalContribution.val (multiplication.toFixed(2).toString());
        tBody.find ('.contribution-factor').each (function (index, field) {
            var fieldObj = jQuery(field);
            console.log(fieldObj.val());
            console.log(total)
            if (fieldObj.val()) {
                total += parseFloat(fieldObj.val());
            }
        });
        summaryFactor.val (total.toFixed(2).toString());
        total = 0;
        tBody.find ('.total-contribution').each (function (index, field) {
            var fieldObj = jQuery(field);
            console.log(fieldObj.val());
                        console.log(total)
            if (fieldObj.val()) {
                total += parseFloat(fieldObj.val());
            }
        });
        summaryContribution.val (total.toFixed(2).toString());
    };

    var validateFactor = function (id) {
        var contributionFactor = jQuery ('#contribution_factor-' + id),
            thisFactor         = parseFloat (contributionFactor.val()),
            fields             = jQuery ('input[id ^= contribution_factor-]'),
            sumContribution    = 0;

        if (!isNaN (thisFactor)) {
            fields.each (function (index, obj) {
                var fieldId  = jQuery (obj).attr ('id'),
                    fieldVal = parseFloat (jQuery (obj).val());
                if (!isNaN (fieldVal) && fieldId !== 'contribution_factor-' + id ) {
                    sumContribution += fieldVal;
                }
            });

            if (sumContribution > 100 || ((sumContribution + thisFactor) > 100)) {
                alert('La suma de los factores de contibución no puede ser mayor que 100%');
                contributionFactor.val('0');
            }
        }

    };

    //public method
    var addRowToTable = function (obj, tBody, idTable) {
        var row          = jQuery ('#' + tBody),
            btn          = jQuery (obj),
            sequence     = btn.attr ('data-sequence'),
            templateName = btn.attr ('data-template'),
            rowId        = Math.floor(Math.random() * 500) + 1000,
            template     = jQuery ('#' + templateName).html ()
                .replace (/__NUM__/g, sequence)
                .replace (/__ID__/g, rowId),
            taskRow = jQuery (template);
        if (sequence === '0') {
            row.empty();
        }
        row.append (taskRow);
        sequence = parseInt(sequence) + 1;
        btn.attr('data-sequence', sequence.toString());
        setContributionFactor ();
    };

    var deleteRow = function (buttonElement, tr, idTable) {
        var dummy = tr.split ('-'),
            row   = jQuery ('#' + tr),
            rows  = row.parent(),
            trs  =  rows.find ('tr').length;

        if (!confirm ('¿Estás seguro que quieres eliminar el elemento seleccionado?')) {
            return;
        }
        jQuery (buttonElement).closest ('tr').remove ();
        if (trs === 1) {
            var addBtn   = rows.parent().find ('tfoot').eq(0).find ('button').eq(0),
                template = jQuery ('#' + rows.attr('id') + '-template').html ();
            rows.append (template);
            addBtn.attr ('data-sequence', '0');
        }
    };

    var setTypeResource = function (obj, id) {
        var typeResource  = jQuery (obj),
            tabName      = typeResource.val (),
            fieldDisplay = jQuery ('#edit_resource_initiative-id-' + id + '_display'),
            fieldValue   = jQuery ('#resource_initiative-id-' + id),
            infoModal    = 'Seleccionar un(a) ',
            modalDiv     = jQuery ('#resource-initiative-modal-' + id),
            tabLabel     = typeResource.find ('option:selected').text();

        if (tabName !== '') {
            clearResource (id);
            modalDiv.attr('data-referenced-module', tabName);
            modalDiv.attr('data-title', infoModal + tabLabel);
            modalDiv.removeClass('hide');
        } else {
            clearResource (id);
            modalDiv.addClass('hide');
        }
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

    var updateNumFields = function (obj, idTable, max) {
        var field = jQuery (obj);
        field.val(field.val ().replace (/[^\d.-]/g, ''));
        if (max !== 0) {
            if (parseFloat(field.val()) > max) {
                field.val(max.toString())
            }
        }
        if (jQuery (obj).attr('id') === 'contribution_factor-' + idTable) {
            validateFactor (idTable)
        }
        setTotalContribution (idTable)
    };

    window.BusinessInitiativesUtils = {
        addRowToTable:           addRowToTable,
        delRowToTable:           deleteRow,
        setTypeResource:          setTypeResource,
        moveRowDown:             moveRowDown,
        moveRowUp:               moveRowUp,
        updateNumFields:         updateNumFields
    };

    var onDocumentReadyHandler = function () {


    };

    jQuery (document)
        .off ("relatedModuleRecordSelected")
        .on ("relatedModuleRecordSelected", function (evt, modalTitle, targetDisplayFieldId, targetDataFieldId) {
            var record      = jQuery ('#' + targetDataFieldId).val (),
                dummy       = targetDataFieldId.split('-'),
                id          = dummy [2],
                factorField = jQuery ('#resource_progress-' + id),
                modalDiv    = jQuery ('#resource-initiative-modal-' + id),
                module      = modalDiv.attr('data-referenced-module'),
                arguments = {
                    'module':    module,
                    'action':   'AjaxEditViewUtils',
                    'function': 'PROGRESS_FACTOR',
                    'record':    record,
                    'Ajax':     'true'
                };
            if (jQuery.inArray('resource_initiative', dummy) === -1) {
                evt.stopPropagation();
                return false;
            }
            jQuery.post ('index.php', arguments, function (data) {
                try {
                    var message = JSON.parse(JSON.stringify(data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else {
                        factorField.val (message.html);
                        setTotalContribution (id);
                    }
                }
                catch (e) {
                    alert(e);
                }
            });
            evt.preventDefault();
    });

    jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));
