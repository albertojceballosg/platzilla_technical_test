(function (jQuery) {
    // Private variables
    var questionSequence = 0,
        maxCategories = 0;


// Private functions
    var updateResponseOption = function (tBoday, selector, maxRow) {
            var rows     = tBoday.find('tr'),
                totalRow = (rows.length + 1);
            totalRow = maxRow +1;
            rows.each (function () {
                var tr = jQuery (this);
                if (selector === 'select') {
                    var selectOption = tr.find('select').eq(0);
                    selectOption.empty();
                    for (var k = 1; k<totalRow; k++) {
                        selectOption.append(
                            jQuery(
                                '<option>',
                                {
                                    value: k,
                                    text: ' ' + k + ''
                                }
                            )
                        );
                    }
                }

            });
    };

    var validateForm = function (objForm) {
        var formElement    = jQuery("form[name='" + objForm.attr ('name') +"'] :input"),
            form           = jQuery (objForm),
            isValidate     = true,
            field, value;
        jQuery('span[id ^= sn-]').html('');
        jQuery('div[id ^= sn-div-]').removeClass('has-error');

        formElement.map(function (index, elm) {
            var element = jQuery(elm),
                elementTitle = element.attr('title'),
                elementName  = element.attr ('name'),
                value = element.val();
            if ((jQuery.inArray(elm.type, ['hidden', 'button', 'submit', 'select-multiple', 'checkbox', 'undefined']) === -1) && elementTitle !== '' && elementTitle !== undefined) {
                if ((value === null) || (value === undefined) || (value.trim() === '')) {
                    element.parent().addClass('has-error');
                    if (element.parent().find('.help-block').length) {
                        element.parent().find('.help-block').html(elementTitle + ' requerido');
                    } else {
                        element.parent().parent().find('.help-block').html(elementTitle + 'es requerida');
                    }
                    isValidate = false;
                }
            }
        });

        return isValidate;
    };

// Public function
    var addQuetionGroup = function (obj, id) {
        var row         = jQuery ('#question-' + id),
            sequence    = row.attr('data-sequence'),
            rowId       = Math.floor(Math.random() * 500) + 1,
            template    = jQuery ('#question-template-' + id).html ()
                .replace(/__ID__/g, sequence)
                .replace(/__ID_ROW__/g, rowId),
            questionRow = jQuery (template);

        questionSequence++;
        row.append (questionRow);
        sequence = parseInt(sequence) + 1;
        row.attr('data-sequence', sequence.toString())

    };

    var addRange = function (obj, idRow, idRange) {
        var btn      = jQuery (obj),
            theme    = btn.attr('data-theme'),
            tBody    = jQuery (idRow),
            rowId    = btn.attr('data-id'),
            template = jQuery ('#RANGE-TEMPLATE-' + idRange).html ()
                .replace(/__ID_ROW__/g, rowId)
                .replace(/__THEME__/g,theme),
            row       = jQuery (template);

        btn.attr('data-id',(parseInt (rowId) + 1).toString());
        tBody.append (row);
    };


    var addResponseOption = function (obj, id) {
        var btn        = jQuery (obj),
            optionName = btn.attr('data-tamplate'),
            tBody      = jQuery ('#TBODY-'+ optionName + '-' + id),
            numRow     = tBody.attr('data-next-option'),
            rowId       = Math.floor(Math.random() * 500) + 1,
            template   = jQuery ('#' + optionName + '-TEMPLATE-' + id).html ()
                .replace(/__ID__/g, numRow)
                .replace(/__ID_ROW__/g, rowId),
            row        = jQuery (template);
        numRow = (parseInt(numRow) + 1).toString();
        tBody.append (row);
        tBody.attr('data-next-option', numRow);
        if (optionName === 'SORT_SIMPLE') {
            var categories = jQuery('#sort-simple-max-' + id),
                value      = categories.val ();
            if ((value === null) || (value === undefined) || (value.trim () === '') || (value === '0')) {
                alert('Por favor insertar el máximo de categiras');
                categories.focus();
                return;
            }
            updateResponseOption(tBody, 'select', parseInt (value))
        }
    };

    var changeRanges = function (obj, event) {
        var sendButton = jQuery (obj),
            record      = parseInt(sendButton.attr('data-record'));
        ekkoLightBox = jQuery('<a href=index.php?module=questionnaire&action=AjaxQuestionUtils&function=CHAMGE_RANGES&Ajax=true&record='+record+' data-toggle="lightbox" data-width="740" data-title="">&nbsp;</a>');
        ekkoLightBox.ekkoLightbox({
            loadingMessage: "Cargando...",
            onHidden: function () {
                var modalBackdrop = jQuery('.modal-backdrop');
                modalBackdrop.removeClass('bottom');
                modalBackdrop.removeClass('z-index');
                if (ekkoLightBox.attr('data-process') === 'YES') {
                    location.reload()
                }
            }
        });
        event.stopPropagation();
        event.preventDefault();

    };

    var changeNavi = function (obj, event) {
        var sendButton = jQuery (obj),
            record      = parseInt(sendButton.attr('data-record'));
        ekkoLightBox = jQuery('<a href=index.php?module=questionnaire&action=AjaxQuestionUtils&function=CHAMGE_NAVI&Ajax=true&record='+record+' data-toggle="lightbox" data-width="740" data-title="">&nbsp;</a>');
        ekkoLightBox.ekkoLightbox({
            loadingMessage: "Cargando...",
            onHidden: function () {
                var modalBackdrop = jQuery('.modal-backdrop');
                modalBackdrop.removeClass('bottom');
                modalBackdrop.removeClass('z-index');
                if (ekkoLightBox.attr('data-process') === 'YES') {
                    location.reload()
                }
            }
        });
        event.stopPropagation();
        event.preventDefault();
    };

    var delQuetionGroup = function (obj, id) {
      var arguments = {},
          row = jQuery ('#ROW-' + id),
          idQuestion = jQuery('#question-id-' + id).val(),
          inforText;
        if (idQuestion !== '') {
            inforText = 'Esta operación eliminará de forma definitiva la pregunta ¿Estás seguro de borrar la pregunta seleccionada?'
        } else {
            inforText = '¿Estás seguro de borrar la pregunta seleccionada?'
        }

        if (!confirm (inforText)) {
            return;
        }
        if (idQuestion !== '') {
            arguments = {
                'module':    'questionnaire',
                'action':    'AjaxQuestionUtils',
                'idQuestion': idQuestion,
                'function':   'DELETE_QUESTION',
                'Ajax':      'true'
            };
            jQuery.post('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse(JSON.stringify(data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else {

                    }
                }
                catch (e) {
                    alert(e);
                }
            });
        }
        row.remove ();
    };

    var delRange = function (obj, id) {
        var arguments = {},
            btnDel    = jQuery (obj),
            row       = jQuery (obj).parent().parent(),
            record    = (id !== 'INSERT') ? parseInt(id) : 0,
            inforText;
        if (record) {
            inforText = 'Esta operación eliminará de forma definitiva el rango ¿Estás seguro de borrar el rango seleccionado?'
        } else {
            inforText = '¿Estás seguro de borrar el rango seleccionado?'
        }

        if (!confirm (inforText)) {
            return;
        }
        if (record) {
            btnDel.attr('disabled','disabled');
            arguments = {
                'module':   'questionnaire',
                'action':   'AjaxQuestionUtils',
                'record':    record,
                'function': 'DELETE_RANGE',
                'Ajax':     'true'
            };
            jQuery.post('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse(JSON.stringify(data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else {

                    }
                }
                catch (e) {
                    alert(e);
                }
            });
        }
        row.remove ();
    };
    
    var delResponseOption = function (obj, id) {
        var btn        = jQuery (obj),
            optionName = btn.attr('data-tamplate'),
            tBody      = jQuery ('#TBODY-'+ optionName + '-' + id),
            row        =  btn.parent().parent(),
            nextRow    = row.next();
        row.remove();
        if (optionName === 'SIMPLE_SELECTION') {
            nextRow.remove()
        }
    };

    var navFilter = function (obj, idRow) {
        var rowId = jQuery (obj).val();
        if (rowId === '') {
            jQuery ('div[id ^= ' + idRow + ']').removeClass ('hide');
        } else {
            jQuery ('div[id ^= ' + idRow + ']').addClass ('hide');
            jQuery ('#' + rowId).removeClass ('hide');
        }
    }

    var questionFilter = function (obj) {
      var rowId = jQuery (obj).val();
      if (rowId === '') {
          jQuery ('div[id ^= ROW-]').removeClass ('hide');
      } else {
          jQuery ('div[id ^= ROW-]').addClass ('hide');
          jQuery ('#' + rowId).removeClass ('hide');
      }
    };

    var quetionForm = function (obj, id) {
        var questionFrom      = jQuery (obj),
            formSelected      = questionFrom.val (),
            answereOption     = jQuery ('#answereoption-' + id),
            responseContainer = jQuery ('#question-type-' + id),
            answeres          = answereOption.find('option');
        answereOption.val ('');
        if (formSelected === '') {
            responseContainer.empty();
            answeres.each(function () {
                var thisOption = jQuery(this);
                jQuery (this).addClass ('hide');

            });
        } else {
            answeres.each(function () {
                var thisOption = jQuery (this),
                    theForm  = thisOption.attr('data-type');
                if (theForm === formSelected) {
                    thisOption.removeClass ('hide');
                } else {
                    thisOption.addClass ('hide');
                }
            });
        }

    };

    var quetionType = function (obj, id, seq) {
        var responseType      = jQuery (obj).val (),
            responseContainer = jQuery ('#question-type-' + id),
            arguments          = {
            'module':    'questionnaire',
            'action':    'AjaxQuestionUtils',
            'sequence':   seq,
            'optiontype': responseType,
            'function':  'ASNWERES_OPTIONS',
            'Ajax':      'true'
        };
        if (responseType !== '') {
            jQuery.post('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse(JSON.stringify(data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else {
                        responseContainer.empty();
                        responseContainer.append(message.html)
                    }
                }
                catch (e) {
                    alert(e);
                }
            });
        }
    };

    var saveGroupTheme = function (obj, id) {
        var btnSave   = jQuery(obj),
            formName  = jQuery('#form-' + id),
            form      = jQuery("form[name='" + formName.attr('name') + "']"),
            modalTitle = jQuery('.modal-title');

        btnSave.attr('disabled','disabled');
        if (!validateForm (formName)) {
            btnSave.removeAttr('disabled');
            return false;
        }
        modalTitle.html('<span class="help-block" style="color: red">Actualizando rangos por tema....</span>');
        var arguments = form.serialize();

        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    alert('Los rangos han sido actualizados');
                    modalTitle.html('');
                    btnSave.removeAttr('disabled');
                }
            }
            catch (e) {
                alert(e);
                btnSave.removeAttr('disabled');
            }
        });
    };

    var saveSurveyNav = function (obj, id) {
        var btnSave   = jQuery(obj),
            formName  = jQuery('#form-' + id),
            form      = jQuery("form[name='" + formName.attr('name') + "']"),
            modalTitle = jQuery('.modal-title');

        btnSave.attr('disabled','disabled');
        if (!validateForm (formName)) {
            btnSave.removeAttr('disabled');
            return false;
        }
        modalTitle.html('<span class="help-block" style="color: red">Actualizando secuencia de preguntas....</span>');
        var arguments = form.serialize();

        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    alert('La secuencia de las preguntas ha sido actualizada');
                    modalTitle.html('');
                    btnSave.removeAttr('disabled');
                }
            }
            catch (e) {
                alert(e);
                btnSave.removeAttr('disabled');
            }
        });
    };

    var setCategories = function (obj, id, e) {
        var categories = jQuery(obj),
            value      = categories.val (),
            optionName = categories.attr('data-tamplate'),
            tBody      = jQuery ('#TBODY-'+ optionName + '-' + id);
        if ((value === null) || (value === undefined) || (value.trim () === '') || value === '0') {
            alert('Por favor insertar el máximo de categprías');
            categories.focus();
            return;
        }
        updateResponseOption(tBody, 'select', parseInt (value));

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

    window.QuestionUtils = {
        addQuetionGroup:   addQuetionGroup,
        addRange:          addRange,
        addResponseOption: addResponseOption,
        changeRanges:      changeRanges,
        changeNavi:        changeNavi,
        delQuetionGroup:   delQuetionGroup,
        delRange:          delRange,
        delResponseOption: delResponseOption,
        navFilter:         navFilter,
        questionFilter:    questionFilter,
        quetionForm:       quetionForm,
        quetionType:       quetionType,
        saveGroupTheme:    saveGroupTheme,
        saveSurveyNav:     saveSurveyNav,
        setCategories:     setCategories,
        showPreview:       showPreview,
        validatePhotoSize: validatePhotoSize
    };
} (jQuery));