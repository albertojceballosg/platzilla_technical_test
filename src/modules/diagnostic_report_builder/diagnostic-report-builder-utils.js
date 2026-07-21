(function (jQuery) {
    //private var
    var ckEditorInstance = [],
        fieldIds         = [],
        loadText         = [],
        loading          = '<div class="loading" style="text-align: center;"><span><i class="fa fa-spinner fa-spin fa-fw fa-2x"></i></span></div>';

    var validateForm = function (objForm) {
        var formElement    = jQuery ("form[name='" + objForm.attr ('name') +"'] :input"),
            form           = jQuery (objForm),
            isValidate     = true,
            selectedFields = [],
            field, operationValue, value;
        jQuery ('span[id ^= photo-]').html('');
        jQuery ('span[id ^= help-field-]').html('');
        jQuery ('span[id ^= question-field-]').html('');
        jQuery ('span[id ^= answer-field-]').html('');
        jQuery ('span[id ^= element-field-]').html('');
        jQuery ('div[id ^= div-drb-]').removeClass('has-error');
        formElement.map(function (index, elm) {
            var element = jQuery (elm),
                elementTitle = element.attr ('title'),
                elementName  = element.attr ('name'),
                value = element.val ();

            if ((jQuery.inArray(elm.type, ['button', 'submit', 'select-multiple', 'checkbox', 'undefined']) === -1) && elementTitle !== '' && elementTitle !== undefined) {
                if (elm.type === 'textarea') {
                    var dummy = elm.id.split ('-');
                    value = ckEditorInstance[ dummy[2] ].getData();
                }
                if ((value === null) || (value === undefined) || (value.trim() === '') || (value.trim() === '<br />')) {
                    element.parent().addClass('has-error');
                    if (element.parent().find('.help-block').length) {
                        element.parent().find('.help-block').html(elementTitle + ' requerido');
                    } else {
                        element.parent().parent().find('.help-block').html(elementTitle + ' requerido');
                    }
                    isValidate = false;
                }
            }
        });
        return isValidate;
    };

    //public method
    var loadCkEditor = function (inputId) {
        var options = {
            contentsCss:   [ 'themes/centaurus/css/bootstrap/bootstrap.min.css' ],
            entities:      false,
            language:      'es',
            removePlugins: 'elementspath',
            extraPlugins:  'diagnosticbuildertemplatevariables',
            height:        90,
            //smiley_images: ['Onion--1.gif','Onion--2.gif','angel_smile.gif', 'angry_smile.gif', 'broken_heart.gif', 'confused_smile.gif', 'cry_smile.gif', 'devil_smile.gif', 'embaressed_smile.gif', 'envelope.gif', 'heart.gif', 'kiss.gif', 'lightbulb.gif', 'omg_smile.gif', 'regular_smile.gif', 'sad_smile.gif', 'shades_smile.gif', 'teeth_smile.gif', 'thumbs_down.gif', 'thumbs_up.gif', 'tounge_smile.gif', 'whatchutalkingabout_smile.gif', 'wink_smile.gif'],
            //smiley_descriptions: ['', ':(', '', '', ':~', ':\'(', '', '', '', '', '', '', ':-O', ':-)', ':-(', '8-)', ':D', '', '', ':-P', ':|', ';-)'],
            toolbar:       [
                [ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'Subscript', 'Superscript' ],
                [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent' ],
                [ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ],
                [ 'Link', 'Unlink', 'Anchor', '-', 'Undo', 'Redo', '-', 'Find', 'Replace', '-', 'SelectAll', 'RemoveFormat', '-', 'Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak', 'TextColor', 'BGColor','UniversalKey' ],
                '/',
                [ 'Styles', 'Format', 'Font', 'FontSize', 'Buildertemplatevariables', '-', 'Source']
            ]
        };
        return CKEDITOR.replace (inputId, options);
    };

    var addBlockToReportBuilder = function (obj, id) {
        var arguments       = {},
            container       = jQuery('#questionnaire-data-' + id),
            questionnaireId = jQuery('#questionnaire-' + id).val(),
            questionBlock   = jQuery ('#question-block-' + id),
            elements        = container.find('div').length,
            topics          = container.find ('.topics_name'),
            topicsValues    = '';

        if (questionnaireId === '') {
            alert('Seleccione un questionario');
            return;
        }
        if (elements <= 2) {
            container.empty();
            container.html (loading);
        } else {
            container.append(loading)
        }
        if (topics.length) {
            console.log(topics.length);
            topics.each(function (index, obj) {
                var value     = jQuery(obj).val (),
                    idRow     = jQuery(obj).attr('rel'),
                    topicName = jQuery ('#answer-' + idRow).val();
                if((value !== null) || (value !== undefined) || (value.trim() !== '')) {

                    if (topicsValues === ''){
                        topicsValues =  topicName + '-' + value;
                    } else {
                        topicsValues += ';' + (topicName + '-' + value);
                    }
                }
            })
        }

        arguments = {
            'module':   'diagnostic_report_builder',
            'action':   'AjaxDiagnosticReportBuilderUtils',
            'function': 'GET-QUESTION-BLOCK',
            'record':   questionnaireId,
            'reportid': id,
            'topics':   topicsValues,
            'Ajax':     'true'
        };
        jQuery.post ('index.php', arguments, function (data) {
            try {
                var message = JSON.parse(JSON.stringify(data));
                if (message.error !== 'OK') {
                    throw message.error;
                } else {
                    if (elements <= 2) {
                        container.empty();
                        container.html (message.html);
                    } else {
                        jQuery('.loading').remove ();
                        container.append (message.html)
                    }
                }
            }
            catch (e) {
                alert(e);
            }
        });
    };

    var addQuestion = function (obj, idRow, idBlock) {
        var row          = jQuery ('#question-row-' + idRow),
            question     = jQuery ('#question-' + idRow).val (),
            btn          = jQuery (obj),
            rowId        = Math.floor (Math.random() * 5000) + 1,
            template     = jQuery ('#row-template-' + idRow).html ()
                .replace (/__ID__/g, rowId),
            taskRow = jQuery (template);
        if (question === 'TOPICS') {
            alert('Imposible incluir mas temas ni preguntas');
            return false;
        }
        row.append (taskRow);
    };

    var delQuestion = function (obj, idRow, idBlock) {
        var row       = jQuery ('#row-question-answer-' + idRow),
            block     = jQuery ('#block-' + idBlock),
            elementId = jQuery ('#element-' + idBlock),
            element   = jQuery ('#element-object-' + idBlock);

        if (!confirm ('¿Estás seguro que quieres eliminar el elemento seleccionado?')) {
            return;
        }
        if (block.find('.delete-question-row').length <= 1) {
            elementId.val ('');
            element.empty();
        }
        row.remove();
    };

    var delQuestionBlock = function (obj, idRow, idBlock) {
        var block       = jQuery ('#block-' + idRow),
            elementType = jQuery ('#element-' + idRow + ' option:selected').val();

        if (!confirm ('¿Estás seguro que quieres eliminar el conjunto de preguntas y respuestas seleccionado?')) {
            return;
        }
        console.log(elementType)
        if (elementType === 'DYNAMIC_TEXT' || elementType === 'VALUED_FUNCTIONS') {
           var idText = block.find('textarea').eq(0).attr('id'),
               dummy  = idText.split('-');
            ckEditorInstance[ dummy[2] ].destroy ();
            ckEditorInstance[ dummy[2] ] = '';
        }
        block.parent().remove();


    };

    var filterByBlock = function (obj, id) {
        var selectedBlock = jQuery (obj). val(),
            blocks        = jQuery('.main-block-' + id);

        jQuery ('#element-filter').val ('');
        blocks.each(function (index, obj) {
            var block      = jQuery(obj),
                reportTab  = block.find ('select[id ^= report-tab-]').val();
            if (selectedBlock !== '') {
                if (reportTab === selectedBlock) {
                    block.removeClass ('hide');
                } else {
                    block.addClass ('hide');
                }
            } else {
                block.removeClass('hide');
            }
        })
    }

    var filterByElement = function (obj, id) {
        var selectedElement = jQuery (obj). val(),
            blocks        = jQuery ('.main-block-' + id);

        jQuery ('#block-filter').val ('');
        blocks.each(function (index, obj) {
            var block      = jQuery(obj),
                reportTab  = block.find ('select[id ^= element-]').val();
            if (selectedElement !== '') {
                if (reportTab === selectedElement) {
                    block.removeClass ('hide');
                } else {
                    block.addClass ('hide');
                }
            } else {
                block.removeClass('hide');
            }
        })
    }

    var getAnswerOption = function (obj, id) {
        var answers    = jQuery ('#answer-' + id),
            questionId = jQuery(obj).val ();

        answers.val('');
        if (questionId !== '') {
            answers.find('option').each(function (index, obj) {
                var option = jQuery (obj);

                if (option.attr('data-question') === questionId) {
                    option.removeAttr('disabled');
                    option.closest('optgroup').show()
                    option.show();

                } else {
                    option.attr('disabled', '');
                    option.closest('optgroup').hide()
                    option.hide();
                }
            })
        } else {
            answers.find('option').each(function (index, obj) {
                var option = jQuery (obj);
                option.closest('optgroup').show();
                option.show();
                option.attr('disabled', '');
            })
        }
    };

    var getBuilderTemplateVariables = function (editor) {
        var modalVariables = jQuery('#' + editor.name + '-tamplate');
        modalVariables.modal();
    };

    var getCurrentStatus = function (obj, idField) {
        var imageName = jQuery(obj).val(),
            images    = jQuery ("[id^='" + idField + "-']");

        images.addClass ('hide');
        jQuery ("#" + idField + '-' + imageName).removeClass('hide');
    };

    var getElementToReportBuilder = function (obj, idRow, idReport) {
        var arguments  = {},
            answerId   = jQuery ('#answer-' + idRow).val (),
            container  = jQuery ('#element-object-' + idRow),
            elementId  = jQuery (obj),
            questionId = jQuery ('#question-' + idRow).val(),
            questionnaireId = jQuery ('#questionnaire-' + idReport).val ();
        if (elementId.val () === '') {
            container.empty();
            return
        } else if (
            answerId === '' ||
            questionId === ''
        ) {
            elementId.val('');
            alert ('Upos! se debe seleccionar una pregunta, una opción de respuesta');
            return
        }
        container.html (loading);
        arguments = {
            'module':   'diagnostic_report_builder',
            'action':   'AjaxDiagnosticReportBuilderUtils',
            'function': 'GET-ELEMENT-REPORT',
            'element':  elementId.val (),
            'reportid': questionnaireId,
            'rowid':    idRow,
            'Ajax':     'true'
        };
        jQuery.post ('index.php', arguments, function (data) {
            try {
                var message = JSON.parse(JSON.stringify(data));
                if (message.error !== 'OK') {
                    throw message.error;
                } else {
                    container.empty();
                    container.html(message.html);
                    fieldIds.push(message.fieldId);
                    if (
                        elementId.val () === 'DYNAMIC_TEXT' ||
                        elementId.val () === 'VALUED_FUNCTIONS'
                    ) {
                        ckEditorInstance[message.fieldId] = loadCkEditor ('block-field-'+ message.fieldId);
                    }
                }
            }
            catch (e) {
                alert(e);
            }
        });

    };

    var getQuestionnaireData = function (obj, id) {
        var arguments       = {},
            container       = jQuery('#questionnaire-data-' + id),
            questionnaireId = jQuery(obj).val(),
            questionBlock   = jQuery ('#question-block-' + id);

        if (questionnaireId === '') {
            container.empty();
            return
        }
        container.html (loading);
        arguments = {
            'module':   'diagnostic_report_builder',
            'action':   'AjaxDiagnosticReportBuilderUtils',
            'function': 'GET-QUESTION-BLOCK',
            'record':   questionnaireId,
            'reportid': id,
            'Ajax':     'true'
        };
        jQuery.post ('index.php', arguments, function (data) {
            try {
                var message = JSON.parse(JSON.stringify(data));
                if (message.error !== 'OK') {
                    throw message.error;
                } else {
                    container.empty();
                    container.html(message.html);
                    questionBlock.removeClass('hide');
                }
            }
            catch (e) {
                alert(e);
            }
        });
    };

    var getTabReport = function (obj, id) {
        var sections = jQuery ('#report-tab-section-' + id),
            tabId    = jQuery(obj).val ();

        sections.val('');
        if (tabId !== '') {
            sections.find('option').each(function (index, obj) {
                var option = jQuery (obj);

                if (option.attr('data-tab') === tabId) {
                    option.removeAttr('disabled')
                } else {
                    option.attr('disabled', '')
                }
            })
        } else {
            sections.find('option').each(function (index, obj) {
                var option = jQuery (obj);
                option.attr('disabled', '')
            })
        }

    };

    var initDinamicText = function (idFields) {
        var fields = JSON.parse(idFields),
            totalField = fields.length,
            i;

        for (i = 0; i< totalField; i++) {
            ckEditorInstance[fields[i]] = loadCkEditor ('block-field-'+ fields[i]);
        }

    };

    var showPreview = function (objFileInput, id) {
        var idImage = '#option-photo-' + id,
            fileId  = '#photo-' + id;

        if (objFileInput.files[0]) {
            var fileReader = new FileReader();
            fileReader.onload = function (e) {
                jQuery (idImage).attr ('src',e.target.result);
                jQuery (fileId).val (objFileInput.files[0].type);
                newPhoto = e.target.result;

            };
            fileReader.readAsDataURL(objFileInput.files[0]);

        }
    };

    var saveDiagnosticBuilder = function (obj, id) {
        var sendButton    = jQuery (obj),
            myForm        = jQuery ('#REPORT-BUILDER-' + id),
            elementFilter = jQuery ('#element-filter'),
            blockFilter   = jQuery ('#block-filter');
        elementFilter.val('');
        elementFilter.trigger ('change');
        blockFilter.val('').trigger ('change');
        sendButton.attr ('disabled','disabled');
        if (!validateForm (myForm)) {
            sendButton.removeAttr ('disabled');
            return false;
        }
        myForm.submit();
    };

    var setBuilderTemplateVariables = function (obj, fieldId) {
        var variable   = jQuery (obj),
            answerId   = variable.val(),
            answer     = variable.find('option:selected').text(),
            attribute  = jQuery('#block-field-attribute-'+ fieldId),
            questionId = variable.find('option:selected').attr('data-question'),
            question   = variable.find('option:selected').parent().attr('label'),
            varName    =  answerId + '-' + questionId;

        ckEditorInstance[fieldId].insertHtml ('&nbsp;[' + varName + ']&nbsp;');
        if (attribute.val() === '') {
            attribute.val(varName)
        } else {
            attribute.val (attribute.val() + '@' + varName)
        }
        variable.val('')
    };

    var validatePhotoSize = function (obj, uploadSize) {
        if (jQuery(obj).val() === '') {
            return true;
        }
        var fileSize = obj.files[ 0 ].size;
        if (fileSize > uploadSize) {
            alert ('El tamaño del Archivo no debe ser superior a ' + uploadSize / (1024 * 1024) + 'MB');
            obj.value = '';
        }
    };

    function validateUrl (obj) {
        var urlVideo = jQuery (obj),
            help     = urlVideo.parent().parent().find('span').eq(1),
            pattern  = /((([A-Za-z]{3,9}:(?:\/\/)?)(?:[\-;:&=\+\$,\w]+@)?[A-Za-z0-9\.\-]+|(?:www\.|[\-;:&=\+\$,\w]+@)[A-Za-z0-9\.\-]+)((?:\/[\+~%\/\.\w\-_]*)?\??(?:[\-\+=&;%@\.\w_]*)#?(?:[\.\!\/\\\w]*))?)/gi;
        if (pattern.test (urlVideo.val())) {
            help.html('');
            return true;
        } else {
            help.html('Introduzca una url valida!');
            urlVideo.val('');
            return false;
        }
    }

    window.DiagnosticRerportBuilderUtls = {
        addBlockToReportBuilder:     addBlockToReportBuilder,
        addQuestion:                 addQuestion,
        delQuestion:                 delQuestion,
        delQuestionBlock:            delQuestionBlock,
        filterByBlock:               filterByBlock,
        filterByElement:             filterByElement,
        getAnswerOption:             getAnswerOption,
        getBuilderTemplateVariables: getBuilderTemplateVariables,
        getCurrentStatus:            getCurrentStatus,
        getElementToReportBuilder:   getElementToReportBuilder,
        getQuestionnaireData:        getQuestionnaireData,
        getTabReport:                getTabReport,
        initDinamicText:             initDinamicText,
        saveDiagnosticBuilder:       saveDiagnosticBuilder,
        setBuilderTemplateVariables: setBuilderTemplateVariables,
        showPreview:                 showPreview,
        validatePhotoSize:           validatePhotoSize,
        validateUrl:                 validateUrl
    };

    var onDocumentReadyHandler = function () {
    };

    jQuery(document).ready(onDocumentReadyHandler);

}(jQuery));