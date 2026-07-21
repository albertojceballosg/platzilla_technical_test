(function (jQuery) {


    // private method
    var setWorkingDays = function (id) {
        var myForm      = jQuery('#form-workig-day-' + id),
            formElement = jQuery("form[name='" + myForm.attr ('name') +"'] :input"),
            row          = jQuery ('#working-days-table-' + id),
            template     = jQuery ('#working-days-table_template-' + id).html (),
            taskRow = jQuery (template);

        formElement.map(function (index, elm) {
            var element = jQuery(elm),
                elementTitle = element.attr('title');
            if ((jQuery.inArray(elm.type, ['hidden', 'button', 'submit', 'select-multiple', 'checkbox', 'undefined']) === -1) && elementTitle !== '' && elementTitle !== undefined) {
                element.val('')
            }
        });
        jQuery('#description_working_day').val ('');
        row.empty();
        row.append (taskRow);

        onDocumentReadyHandler (false);
    };

    var validateForm = function (objForm) {
        var formElement    = jQuery("form[name='" + objForm.attr ('name') +"'] :input"),
            form           = jQuery (objForm),
            isValidate     = true,
            selectedFields = [],
            field, operationValue, value;
        jQuery('span[id ^= wd-]').html('');
        jQuery('div[id ^= wd-div-]').removeClass('has-error');
        jQuery('td[id ^= wd-div-]').removeClass('has-error');
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
                        element.parent().find('.help-block').html(elementTitle + ' requerido');
                    }
                    isValidate = false;
                }
            }
        });

        return isValidate;
    };

    //public method
    var cancelWorkingDay = function (id) {
        var myForm      = jQuery('#form-workig-day-' + id),
            formElement = jQuery("form[name='" + myForm.attr ('name') +"'] :input");
        if (confirm ('¿Estás seguro que quieres cancelar el tipo dejornada')) {
            jQuery('span[id ^= wd-]').html('');
            jQuery('div[id ^= wd-div-]').removeClass('has-error');
            jQuery('td[id ^= wd-div-]').removeClass('has-error');
            myForm[0].reset();
            setWorkingDays (id);
        }
    };

    var editWorkingType = function (obj, id) {
        var record = jQuery(obj).val (),
            row    = jQuery ('#working-day-edit-selected-' + id),
            arguments = {
                'module':      'Home',
                'action':      'AjaxWorkingDayUtils',
                'function':    'WORKING-DAY-EDIT',
                'record':      record,
                'template_id': id,
                'Ajax':        true
            };
        if (record !== '') {
            row.empty();
            row.html('<img src="themes/images/loading.gif" alt="Loading" class="img-responsive center-block"/>');
            jQuery.post ('index.php', arguments, function (data) {
                var message, data;
                try {
                    message = JSON.parse(JSON.stringify(data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else {
                        row.empty();
                        row.html(message.html);
                        onDocumentReadyHandler (true);
                        jQuery('.timeEditClass').val('');
                    }
                }
                catch (e) {
                    alert(e);
                }
            })
        } else {
            cancelWorkingDay (id);
        }
    };

    var extendWorkingHours = function (obj, id) {
        var tBody        = jQuery ('#working-days-table-' + id).find ('tr'),
            regularHours = [
                '',
                jQuery ('#regular_working_hours').val (),
                jQuery ('#regular_hours_day_gai').val (),
                jQuery ('#regular_hours_day_gaf').val (),
                jQuery ('#regular_hours_day_gbi').val (),
                jQuery ('#regular_hours_day_gbf').val ()
            ];
        jQuery ('.wd-timepicker').timepicker ('remove');
        tBody.each (function (k, tr) {
            var rows = jQuery(this).find ('td');
            rows.each (function (j, td) {
                var column   = jQuery (this),
                    columnId = column.attr('id');
                if (columnId !== undefined && columnId !== 'undefined') {
                    if (regularHours[ j ] !== '') {
                        column.find ('input').eq (0).val (regularHours[ j ])
                    }
                }
            });
        });

        onDocumentReadyHandler (true);
    };

    var getWorkingType = function (obj, id) {
        var record = jQuery(obj).val (),
            row    = jQuery ('#working-day-selected-' + id),
            arguments = {
                'module':   'Home',
                'action':   'AjaxWorkingDayUtils',
                'function': 'WORKING-TYPE',
                'record':   record,
                'Ajax':     true
            };
        if (record !== '') {
            row.empty();
            row.html('<img src="themes/images/loading.gif" alt="Loading" class="img-responsive center-block"/>');
            jQuery.post ('index.php', arguments, function (data) {
                var message, data;
                try {
                    message = JSON.parse(JSON.stringify(data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else {
                        row.empty();
                        row.html(message.html)
                    }
                }
                catch (e) {
                    alert(e);
                }
            })
        }
    };

    var goToHistory = function () {

    };

    var removeDay = function (obj, day) {
        var row   = jQuery (obj).parent().parent();

        if (!confirm ('¿Estás seguro que quieres eliminar el día ' + day)) {
            return;
        }
        row.remove();
    };

    var normalizeWorkingTime = function (obj, e) {
        var fieldLength = jQuery (obj);

        if ((e.ctrlKey === true) ||
            (e.metaKey === true) ||
            (e.keyCode === 16) ||
            (e.keyCode <= 47  && e.keyCode !== 8) ||
            (e.keyCode >= 58 && e.keyCode !== 190)
        ) {
            e.preventDefault ();
        }
    };

    var saveWorkingType = function (obj, editId, createdId) {
        var sendButton   = jQuery(obj),
            editType     = jQuery ('#working-day-edit-types-' + editId),
            selectedType = jQuery ('#working-day-types-' + createdId),
            myForm       = jQuery('#form-workig-day-' + editId);
        sendButton.attr('disabled','disabled');
        if (!validateForm (myForm)) {
            sendButton.removeAttr('disabled');
            return false;
        }
        var arguments = myForm.serialize();

        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    alert('El tipo de Jornada ha sido guardada con éxito!');
                    myForm[0].reset();
                    sendButton.removeAttr('disabled');
                    editType.empty();
                    selectedType.empty();
                    editType.append (jQuery(message.html[1]));
                    selectedType.append (jQuery(message.html[0]));
                    setWorkingDays (editId);
                }
            }
            catch (e) {
                alert(e);
                sendButton.removeAttr('disabled');
            }
        });

    };

    var setWorkingType = function (obj, id) {
        var btn       = jQuery(obj),
            record    = jQuery('#working-day-types-' + id).val (),
            arguments = {
                'module':   'Home',
                'action':   'AjaxWorkingDayUtils',
                'function': 'USER-WORKING-TYPE',
                'record':   record,
                'Ajax':     true
            };
        if (record !== '') {
            btn.attr('disabled','disabled');
            jQuery.post ('index.php', arguments, function (data) {
                var message, data;
                try {
                    message = JSON.parse(JSON.stringify(data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else {
                        alert(message.html);
                        btn.removeAttr('disabled');
                    }
                }
                catch (e) {
                    alert(e);
                    btn.removeAttr('disabled');
                }
            })
        } else {
            alert('Seleccione un tipo de jornada de trabajo')
        }
    };

    window.WorkingDayUtils = {
        cancelWorkingDay:     cancelWorkingDay,
        editWorkingType:      editWorkingType,
        extendWorkingHours:   extendWorkingHours,
        getWorkingType:       getWorkingType,
        goToHistory:          goToHistory,
        normalizeWorkingTime: normalizeWorkingTime,
        removeDay:            removeDay,
        saveWorkingType:      saveWorkingType,
        setWorkingType:       setWorkingType
    };

    var onDocumentReadyHandler = function (holdValues) {
        jQuery ('.wd-timepicker').timepicker ({
            minuteStep:   5,
            showSeconds:  true,
            showMeridian: false,
            disableFocus: false,
            showWidget:   true
        }).focus (function () {
            jQuery (this).next ().trigger ('click');
        });
        if (!holdValues) {
            jQuery ('.wd-timepicker').val('');
        }

    };
    jQuery (document).ready (onDocumentReadyHandler(false));
} (jQuery));