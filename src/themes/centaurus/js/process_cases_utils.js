// Variable global para indicar que el script ya ha sido cargado
if (typeof window.PROCESS_CASES_UTILS_LOADED === 'undefined') {
    window.PROCESS_CASES_UTILS_LOADED = true;

(function (jQuery) {
    // private functions & variables
    let actionParameters = {},
        msg,
        ekkoLightBox,
        timer = null,
        doTask = '';

    var msgClose = function (ret) {
        console.log("msgClose -> retour %s", ret);
        if (msg === undefined || msg === 'undefined') {
            console.log('me sali')
            initCase ();
        }
        console.log("msgClose -> retour %s-", ret + '-');
        if (ret === 'CREATE') {
            var dummy = actionParameters.url.split ('?');
            location.href = dummy[0] + '?module=' + actionParameters.flModule +
                '&action=EditView&return_action=DetailView&parenttab=&mode=create&case_number=' + actionParameters.caseId;
        } else if (ret === 'SELECT') {
            showRecordsModal ()
        } else if (ret === 'YES') {
            doTask = 'YES';
        }
    }

    var showRecordsModal = function () {
        ekkoLightBox = jQuery(
            '<a href="index.php?module=' + actionParameters.module +
            '&action=AjaxDetailViewUtils&function=SELECT-MODULE-STEP' +
            '&caseId=' + actionParameters.caseId +
            '&records=' + actionParameters.records +
            '&flmodule=' + actionParameters.flModule +
            '&Ajax=true" data-toggle="lightbox" ' +
            'data-width="740" ' +
            'data-process="NO" ' +
            'data-task="" ' +
            'data-title="Seleccionar registro">&nbsp;</a>'
        );
        ekkoLightBox.ekkoLightbox({
            loadingMessage: "Cargando...",
            footer: "",
            onHidden: function () {
                var modalBackdrop = jQuery('.modal-backdrop');
                modalBackdrop.removeClass ('bottom');
                modalBackdrop.removeClass ('z-index');
            }
        });
        event.stopPropagation();
        event.preventDefault();
    }

    // public methods
    var closeStep = function (obj, id, stepId, event) {
       var closeButton  = jQuery (obj),
           caseId       = jQuery ('#case_id-' + id).val(),
           functionName = 'CLOSE-PROCESS-STEP',
           module       = jQuery ('#module-' + id).val(),
           records      = closeButton.attr('rel'),
           flModule     = closeButton.attr('data-fl-module'),
           recordModule = closeButton.attr('data-record-id');

       if (closeButton.hasClass('isDisabled')) {
           return false;
       } else {
           closeButton.addClass('isDisabled');
       }
        ekkoLightBox     = jQuery(
            '<a href="index.php?module=' + module +
            '&action=AjaxDetailViewUtils&function=' + functionName +
            '&recordsId=' + records +
            '&caseId=' + caseId +
            '&recordModule=' + recordModule +
            '&flModule=' + flModule +
            '&Ajax=true" data-toggle="lightbox" ' +
            'data-width="740" ' +
            'data-process="NO" ' +
            'data-task="" ' +
            'data-title="Evidencias de los entregables del paso">&nbsp;</a>'
        );
        event.stopPropagation();
        event.preventDefault();
        ekkoLightBox.ekkoLightbox({
            loadingMessage: "Cargando...",
            footer: "",
            onHidden: function () {
                closeButton.removeClass ('isDisabled');
                var modalBackdrop = jQuery('.modal-backdrop');
                modalBackdrop.removeClass ('bottom');
                modalBackdrop.removeClass ('z-index');
                console.log(ekkoLightBox.attr ('data-process'));
                console.log(ekkoLightBox.attr ('data-task'));
                console.log(doTask);
                if (
                    ekkoLightBox.attr ('data-process') === 'YES' &&
                    ekkoLightBox.attr ('data-task') !== '' &&
                    doTask === 'YES'
                ) {
                    doTask = 0;
                   location.href= ekkoLightBox.attr ('data-task');
                } else {
                    location.reload ();
                }
            }
        });

    }

    var createCase = function (obj, id, event) {
        var closeButton  = jQuery (obj);
        actionParameters = {
           'caseId':   jQuery ('#case_id-' + id).val(),
           'module':   jQuery ('#module-' + id).val(),
           'flModule': closeButton.attr('data-fl-module'),
           'records':  closeButton.attr('data-step-id'),
           'url':      location.href,
           'event':    event
        }
        if (msg === undefined || msg === 'undefined') {
            initCase ('Seleccionar', 'Crear', 'Cancelar','Okey');
        }
        msg.data('messageBox').question(
            'Ir al paso',
            'Este paso no ha sido abierto, ¿Cómo desea abrirlo?',
            [{text:'Seleccionar',return:'SELECT'}, {text:'Crear',return:'CREATE'},{text:'Cancelar',return:'CANCEL'}]
        );
        event.stopPropagation();
        event.preventDefault();
    }

    var editNotes = function (obj, id, isClose, event) {
        var noteButton   = jQuery (obj),
            caseId       = jQuery ('#case_id-' + id).val(),
            functionName = 'STEP-EDIT-NOTES',
            module       = jQuery ('#module-' + id).val(),
            records      = noteButton.attr('rel'),
            stepType     = noteButton.attr('data-step-type');

        noteButton.addClass('isDisabled');

        ekkoLightBox     = jQuery(
            '<a href="index.php?module=' + module +
            '&action=AjaxDetailViewUtils&function=' + functionName +
            '&recordsId=' + records +
            '&caseId=' + caseId +
            '&caseType=' + stepType +
            '&Ajax=true" data-toggle="lightbox" data-width="740" data-title="Notas del paso">&nbsp;</a>'
        );
        ekkoLightBox.ekkoLightbox({
            loadingMessage: "Cargando...",
            footer: "",
            onHidden: function () {
                noteButton.removeClass ('isDisabled');
                var modalBackdrop = jQuery('.modal-backdrop');
                modalBackdrop.removeClass ('bottom');
                modalBackdrop.removeClass ('z-index');
                if (stepType === 'MANUAL') {
                    var dummy = records.split ('@');
                    jQuery('#close-link-'+ dummy[1]).removeClass('isDisabled');
                }
                if (ekkoLightBox.attr('data-process') === 'YES') {
                    location.reload()
                }
            }
        });
        event.stopPropagation();
        event.preventDefault();
    }

    var finishProcess = function (obj, id, caseNumber, event) {
        var btn       = jQuery(obj),
            arguments = {
                'module':   'process_cases',
                'action':   'FinishProcess',
                'caseid':    caseNumber,
                'Ajax':      true,
            },
        infoClose = 'Esta acción cerrará todos los pasos de este proceso de manera definitiva.\nAsegúrate de que has revisado todas las condiciones y la información necesaria antes de proceder.\n¿Desea continuar con el cierre del proceso completo?';
        btn.addClass('isDisabled');

        if (confirm (infoClose) && caseNumber !== '') {
            btn.html ('<i class="fa fa-spinner fa-spin fa-fw"></i> Finalizando el proceso');
            jQuery.post('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse(JSON.stringify(data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else {
                        location.reload();
                    }
                } catch (e) {
                    alert(e);
                    btn.removeClass('isDisabled');
                }
            });
        } else {
            btn.removeClass('isDisabled');
        }

        event.stopPropagation();
        event.preventDefault();

    }

    var gotoStep = function (obj, id, event) {
        var arguments         = {},
            btnGotoStep = jQuery (obj),
            moduleName  = jQuery ('#module-' + id).val(),
            flModule    = btnGotoStep.attr ('data-fl-module'),
            stepView    = btnGotoStep.attr ('data-step-view'),
            stepIds     = btnGotoStep.attr ('data-step-id'),
            stepType    = btnGotoStep.attr ('data-step-type'),
            stepSeq     = btnGotoStep.attr ('data-seq'),
            caseNumber  = btnGotoStep.attr ('rel'),
            url = location.href;

        if (caseNumber !== '') {
            btnGotoStep.addClass('isDisabled');
            arguments ={
                'module':   moduleName,
                'action':   'AjaxDetailViewUtils',
                'function': 'GET-STEP-MODULE-CRMID',
                'flmodule':  flModule,
                'caseid':    caseNumber,
                'stepids':   stepIds,
                'setptype':  stepType,
                'sequnce':   stepSeq,
                'Ajax':      true,
            }
            jQuery.post ('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse (JSON.stringify (data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else if(message.html !== '') {
                        var dummy = url.split ('?');
                        if (stepView === 'DetailView') {
                            location.href = dummy[0] + '?module=' + flModule + '&action=DetailView&record=' + message.html + '&parenttab=&mode=';
                        } else {
                            location.href = dummy[0] + '?module=' + flModule + '&action=EditView&record=' + message.html + '&return_module=' + flModule + '&return_action=DetailView&parenttab=&return_id=' + message.html;
                        }

                    } else {
                        alert('No se ha encontrado el registro');
                    }
                } catch (e) {
                    alert (e);
                    btnGotoStep.removeAttr('disabled');
                }
            });
        } else {
            alert('Uoops! No se ha encontrado el registro');
        }
        event.stopPropagation();
        event.preventDefault();
    }

    var initCase = function (no, yes, cancel, okey) {
        var localNo     = (no !== undefined) ? no : 'NO',
            localYes    = (yes !== undefined) ? yes : 'YES',
            localCancel = (cancel !== undefined) ? cancel : 'CANCEL',
            localOkey   = (okey !== undefined) ? okey : 'Okey';
        msg = jQuery ("body").messageBox({
            modal:true,
            cbClose : msgClose,
            autoClose: 0,
            locale:{
                NO : localNo,
                YES : localYes,
                CANCEL : localCancel,
                OK : localOkey,
                textAutoClose: 'Esta opción se cierra en %d segundos'
            }
        });
    };

    var joinProcessCase = function (obj, id) {
        var arguments         = {},
            sendButton        = jQuery (obj),
            businessProcesses = jQuery ('#business_processes-' + id).val(),
            module            = jQuery ('#module-' + id),
            record            = jQuery ('#record-' + id);
        sendButton.attr('disabled','disabled');
        sendButton.find('i').addClass('fa-spin');
        sendButton.find('span').removeClass('hidden');

        if (businessProcesses !== '') {
            arguments  = {
                'module':             module.val(),
                'action':            'AjaxDetailViewUtils',
                'function':          'JOIN-PROCESS-CASE',
                'record':            record.val(),
                'Ajax':               true,
                'business_processes': businessProcesses,
            }
            jQuery.ajaxSetup({async: false});
            jQuery.post ('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse (JSON.stringify (data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else {
                        alert(message.html);
                        location.reload();
                    }
                } catch (e) {
                    alert (e);
                    sendButton.removeAttr('disabled');
                }
            });
        } else {
            alert('Por favor seleccione un modelo de proceso.');
            sendButton.removeAttr('disabled');
        }
        sendButton.removeAttr('disabled');
    }

    var joinRecordToCase = function (obj, crmId, event) {
        var btn       = jQuery(obj),
            arguments = {
            'module':   btn.attr('data-module'),
            'action':   'AjaxDetailViewUtils',
            'function': 'JOIN-STEP-CRMID',
            'flmodule':  btn.attr('data-fl-module'),
            'crmid':     crmId,
            'records':  btn.attr ('rel'),
            'caseid':    btn.attr ('data-case-number'),
            'Ajax':       true,
        }
        btn.addClass('disabled');
        jQuery.post ('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if (message.error !== 'OK') {
                    throw message.error;
                } else {
                    location.reload()
                }
            } catch (e) {
                alert (e);
                btn.removeAttr('disabled');
            }
        });
    }

    var setQualityAssessment = function (obj, id) {
        var buttonQA      = jQuery (obj),
            caseId        = buttonQA.attr  ('data-case-number'),
            functionName  = 'SET-QUALITY-ASSESSMENT',
            module        = jQuery ('#module-' + id).val(),
            records       = buttonQA.attr ('rel'),
            relatedRecord = buttonQA.attr ('data-record-id')
        ekkoLightBox     = jQuery(
            '<a href="index.php?module=' + module +
            '&action=AjaxDetailViewUtils&function=' + functionName +
            '&recordsId=' + records +
            '&caseNumbser=' + caseId +
            '&relatedRecord=' + relatedRecord +
            '&Ajax=true" data-toggle="lightbox" data-width="800" data-title="Evaluación de la calidad del paso ejecutado">&nbsp;</a>'
        );
        ekkoLightBox.ekkoLightbox({
            loadingMessage: "Cargando...",
            footer: ""
        });
        event.stopPropagation();
        event.preventDefault();
    }

    var saveComments = function (obj, id, event) {
        var sendButton = jQuery (obj),
            message    = jQuery ('#step_notes-' + id).val(),
            form       = jQuery ('#case-form-' + id);

        sendButton.attr('disabled','disabled');
        if (message !== '') {
            var arguments  = form.serialize();
            jQuery.post('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse (JSON.stringify (data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    }
                } catch (e) {
                    alert (e);
                    sendButton.removeAttr('disabled');
                }
            });
        } else {
            alert('Por favor ingrese un comentario');
            sendButton.removeAttr('disabled');
        }
        sendButton.removeAttr('disabled');
    }

    var saveDocumentToCase = function (obj, id, record) {
        var sendButton = jQuery (obj),
            btnCancel  = jQuery ('#btn-cancel-' + id),
            cancelTask = jQuery ('#cancel-task-' + record),
            notDoc     = jQuery ('#not-document-' + id),
            message    = jQuery ('#step_notes-' + id).val(),
            checkBoxes = jQuery ('#tbody-' + id + ' input[type="checkbox"]'),
            hasDoc     = true,
            numDocs    = parseInt (jQuery ('#num-docs-' + id).val()),
            form       = jQuery ('#form-close-step-' + id);

        sendButton.attr('disabled','disabled');
        cancelTask.val ('0')
        console.log(checkBoxes)
        if (!notDoc.is(':checked') && numDocs > 0) {
            hasDoc = false;
            checkBoxes.each(function () {
                if (jQuery(this).is(':checked')) {
                    hasDoc     = true;
                }
            });
        }
        if (!hasDoc) {
            alert('Por favor seleccione al menos un documento');
            sendButton.removeAttr('disabled');
            return false;
        }
        if (message !== '' && hasDoc) {
            var arguments  = form.serialize();
            jQuery.post('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse (JSON.stringify (data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    }
                    alert('El paso se ha cerrado correctamente');
                    btnCancel.trigger('click')
                } catch (e) {
                    alert (e);
                    sendButton.removeAttr('disabled');
                }
            });
        } else {
            alert('Por favor ingrese el reporte de actividad');
            sendButton.removeAttr('disabled');
        }
        sendButton.removeAttr('disabled');
    }

    var saveQualityAssessment = function (obj, id, event) {
        var sendButton       = jQuery (obj),
            qualityValuation = jQuery ('#quality_valuation-' + id),
            errorQuality     = qualityValuation.parent().find('span').eq(0),
            reasonValuation  = jQuery ('#reason_valuation-' + id),
            errorReason      = reasonValuation.parent().find('span').eq(0),
            form             = jQuery ('#case-form-' + id);
        sendButton.attr('disabled','disabled');
        errorQuality.html('')
        errorReason.html('')
        if (qualityValuation.val() !== '' && reasonValuation.val() !== '') {
            var arguments  = form.serialize();
            jQuery.post('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse (JSON.stringify (data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else {
                        jQuery('.modal').modal('hide')
                    }
                } catch (e) {
                    alert (e);
                    sendButton.removeAttr('disabled');
                }
            });
        } else {
            if (qualityValuation.val() === '') {
                errorQuality.html('Por favor seleccione una valoración de calidad');
            }
            if (reasonValuation.val() === '') {
                errorReason.html('Por favor ingrese un comentario');
            }
            sendButton.removeAttr('disabled');
            return false;
        }
        sendButton.removeAttr('disabled');
    }

    var selectStep = function (obj, setpId, id) {
        var step = jQuery(obj),
            actionMenu = jQuery ('#flag-' + id + '-' + setpId);
        jQuery('ul[id ^= flag-'+ id +'-]').addClass('hidden');
       actionMenu.removeClass('hidden');
    }

    var unCloseStepCase = function (obj, id) {
        var cancelTask = jQuery ('#cancel-task-' + id);

        if (cancelTask.val() === '1') {
            ekkoLightBox.attr ('data-process', 'NO');
        }
    }

    var updateClosedStep = function (stepId, id) {
        var step        = jQuery ('#step-' + stepId),
            closeLink   = jQuery ('#close-link-' + stepId),
            processTack = jQuery ('#task-process-' + id).val(),
            taskName    = jQuery ('#task-name-' + id).val();
        if (step.hasClass('active-step')) {
            step.removeClass('active-step');
        }
        step.addClass ('closed-step');
        closeLink.addClass ('isDisabled');
        ekkoLightBox.attr ('data-process', 'YES');
        ekkoLightBox.attr ('data-task', processTack);
        if (msg === undefined || msg === 'undefined') {
            initCase()
        }
        if (processTack !== '') {
            msg.data('messageBox').question(
                'Cerrar paso',
                'Después de cerrar el paso, se ejecutará la tarea:<br>".'+ taskName + '" <br>¿Desea continuar?',
                [{text:'Si',return:'YES'}, {text:'No',return:'NOT'}]
            );
        } else if (false) {
            msg.data('messageBox').default(
                'Cerrar paso', 'El paso se ha cerrado con éxito!.'
            );
        }
    }

    var viewComments = function (obj, id, event) {
        var comments = jQuery (obj).attr('rel'),
            stepName = jQuery (obj).attr('data-name');
        console.log(msg);
        if (msg === undefined || msg === 'undefined') {
            initCase()
        }
        msg.data('messageBox').default(
            stepName, comments
        );
        event.stopPropagation();
        event.preventDefault();
    }

    window.ProcessCaseUtils = {
        closeStep:             closeStep,
        createCase:            createCase,
        editNotes:             editNotes,
        finishProcess:         finishProcess,
        gotoStep:              gotoStep,
        initCase:              initCase,
        joinProcessCase:       joinProcessCase,
        joinRecordToCase:      joinRecordToCase,
        setQualityAssessment:  setQualityAssessment,
        saveComments:          saveComments,
        saveDocumentToCase:    saveDocumentToCase,
        saveQualityAssessment: saveQualityAssessment,
        selectStep:            selectStep,
        unCloseStepCase:       unCloseStepCase,
        updateClosedStep:      updateClosedStep,
        viewComments:          viewComments
    };

    jQuery(document).on('ready', function () {
        initCase ()
    });
}(jQuery));

} // Cierre del condicional PROCESS_CASES_UTILS_LOADED