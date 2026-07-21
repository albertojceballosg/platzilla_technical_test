(function (jQuery) {

    // private methods
    var updateActionsOn = function () {
        var actionData      = [],
            stepCode        = jQuery ('.process-step-code'),
            idTable         = jQuery (stepCode[0]).attr('data-Tableid'),
            helpAction      = jQuery ('#help-action-' + idTable);

            stepCode.each (function (index, obj) {
            var field    = jQuery (obj),
                dummyId  = field.attr ('id').split ('_'),
                rowIds   = dummyId[2].split ('-'),
                crmId    = jQuery ('#step_id-' + rowIds[1]).val (),
                stepType = jQuery ('#step_type-' + rowIds[1]).val ();
            actionData.push ({
                'crmId'  : crmId,
                'rowId'   : rowIds[1],
                'stepType': stepType,
                'stepCode': field.val ()
            }); // add new object
        });

        actionData.each (function (obj,index) {
            var actionOn   = jQuery ('#action_on-' + obj.rowId),
                actionTask = jQuery ('#action_task_on-' + obj.rowId),
                taskDiv    = jQuery ('#input-action_task_on-' + obj.rowId);
            taskDiv.addClass ('hidden');
            if (obj.stepType === 'ASSISTED') {
                actionOn.empty ();
                actionData.each(function (objStep, i) {
                    actionOn.append(
                        jQuery(
                            '<option>',
                            {
                                value: (i + 1) + '-' + objStep.crmId,
                                text: (i + 1) + '-' + objStep.stepCode
                            }
                        )
                    );
                })
            } else if (obj.stepType === 'AUTOMATIC') {
                taskDiv.removeClass ('hidden');
                actionOn.empty ();
                actionTask.empty ();
                actionData.each(function (objStep, i) {
                    actionOn.append (
                        jQuery(
                            '<option>',
                            {
                                value: (i + 1) + '-' + objStep.crmId,
                                text: (i + 1) + '-' + objStep.stepCode
                            }
                        )
                    );
                    actionTask.append (
                        jQuery(
                            '<option>',
                            {
                                value: (i + 1) + '-' + objStep.crmId,
                                text: (i + 1) + '-' + objStep.stepCode
                            }
                        )
                    );
                })
            } else {
                actionOn.empty ();
                actionOn.append (
                    jQuery (
                        '<option>',
                        {
                            value: (index +1) + '-' + obj.crmId,
                            text: (index +1) + '-' + obj.stepCode
                        }
                    )
                );
            }
        });
       helpAction.html('Reordenar acciones');
       var view = setTimeout(function () {
           helpAction.html('');
           clearTimeout(view);
       }, 3000);

    }

    //public method
       var addRowToTable = function (obj, tBody, idTable) {
           var row          = jQuery ('#' + tBody),
               btn          = jQuery (obj),
               sequence     = btn.attr ('data-sequence'),
               rowId        = Math.floor (Math.random() * 500) + 1,
               template     = jQuery ('#process_steps-template-' + idTable).html ()
                   .replace (/__ID__/g, rowId),
               taskRow = jQuery (template);

           if (sequence === '0') {
               row.empty();
           }

           row.append (taskRow);
           sequence = parseInt(sequence) + 1;
           btn.attr('data-sequence', sequence.toString());
       };

       var delRowToTable = function (buttonElement, tr, idTable) {
           var tBody = jQuery('#tbody-task-project-' + idTable),
               row   = jQuery ('#' + tr),
               rows  = row.parent(),
               trs  =  rows.find ('tr').length;

           if (!confirm ('¿Estás seguro que quieres eliminar el elemento seleccionado?')) {
               return;
           }
           jQuery (buttonElement).closest ('tr').remove ();
           if (trs === 1) {
               var template = jQuery ('#task-project-tr-' + idTable).html ();
               tBody.append (template);
           }
           updateActionsOn ();
       };

       var moveRowDown = function (btn, tr) {
           var rowToMove = jQuery ('#' + tr),
               next      = rowToMove.next ('tr.tabla-field-row');
           next.after (rowToMove);
           updateActionsOn ();
       };

       var moveRowUp = function (btn, tr) {
           var rowToMove = jQuery ('#' + tr),
               prev      = rowToMove.prev ('tr.tabla-field-row');
           prev.before (rowToMove);
           updateActionsOn ();
       };

        var relatedModule = function (obj,data, moduleName) {
            var arguments    = {},
                fieldObject  = jQuery (obj),
                moduleFields = ['step_name','step_type','step_state'],
                tableFields  = ['step_name','related_module','step_state'],
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
                'module':   'process',
                'action':   'ProcessStepsUtls',
                'flmodule': 'process_steps',
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
                        if (message.stepType !== 0) {
                            jQuery('#step_type-' + importantIds[1]).val(message.stepType.stepType);
                            jQuery('#step_type_view-' + importantIds[1]).val(message.stepType.viewType);
                        }
                        tFootBtn.removeAttr('disabled');
                        tFootBtn.parent().find('span').eq(0).addClass('hide');

                        row.find('button').each(function (index, btn) {
                            jQuery(btn).removeAttr('disabled');
                        });
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
                updateActionsOn ();
            });
        };

    window.ProcessStepsUtls = {
        addRowToTable:  addRowToTable,
        delRowToTable:  delRowToTable,
        moveRowDown:    moveRowDown,
        moveRowUp:     moveRowUp,
        relatedModule: relatedModule,
    };

    var onDocumentReadyHandler = function () {
    };
    jQuery(document).ready(onDocumentReadyHandler);
}(jQuery));

