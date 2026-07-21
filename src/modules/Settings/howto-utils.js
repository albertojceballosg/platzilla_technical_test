(function (jQuery) {
    var htmlEditor;

    	/* Private methods */
    var loadCkEditor = function (inputId) {
        console.log ('cargando editor');
        var options = {
            contentsCss:   [ 'themes/centaurus/css/bootstrap/bootstrap.min.css' ],
            entities:      false,
            language:      'es',
            removePlugins: 'elementspath',
            height:        90,
            toolbar:       [
                    [ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'Subscript', 'Superscript' ],
                    [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent' ],
                    [ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ],
                    [ 'Link', 'Unlink', 'Anchor', '-', 'Undo', 'Redo', '-', 'Find', 'Replace', '-', 'SelectAll', 'RemoveFormat', '-', 'Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak', 'TextColor', 'BGColor' ],
                    '/',
                    [ 'Styles', 'Format', 'Font', 'FontSize', '-', 'EmailTemplateVariables', '-', 'Source' ]
                ]
            };
        return CKEDITOR.replace (inputId, options);
    };

    var validateForm = function (objForm) {
        var formElement    = jQuery("form[name='" + objForm.attr ('name') +"'] :input"),
            isValidate     = true,
            selectedFields = [],
            field, operationValue, value;
            //jQuery('span[id ^= help-field-]').html('');
            jQuery('span[id ^= how-to-spn-]').html('');
            jQuery('div[id ^= how-to-div-]').removeClass('has-error');
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
                            element.parent().parent().find('.help-block').html(elementTitle + ' requerido');
                        }
                        isValidate = false;
                    }
                }
            });
            /** this validation is nor
            value = htmlEditor.getData();
            if ((value === null) || (value === undefined) || (value.trim () === '')) {
            	jQuery('#how-to-spn-html').html('El texto para la Ayuda HowTo es requerido');
                isValidate = false;
            } else if (value.trim ().length < 90) {
                jQuery('#how-to-spn-htmlhow-to-spn-html').html('La descripción de la ayuda, parece estar vacía o es muy corta! introduce al menos 100 carácteres!');
                isValidate = false;
            }
             */
            value = jQuery('#how-to-video').val ();
            if ((value !== null) && (value !== undefined) && (value.trim () !== '')) {
                value = jQuery('#how-to-video-type').val ();
                if ((value === null) || (value === undefined) || (value.trim () === '')) {
                    jQuery('#how-to-div-video-type').addClass('has-error');
                    jQuery('#how-to-spn-video-type').html('Tipo de video?');
                    isValidate = false;
                }
            }
            return isValidate;
        };

    //public method
    var addRowToTable = function (obj, tBody) {
        var row          = jQuery (tBody),
            btn          = jQuery (obj),
            templateName = btn.attr ('data-template'),
            rowId        = Math.floor(Math.random() * 500) + 1000,
            template     = jQuery ('#' + templateName).html ()
                .replace (/__ID__/g, rowId),
            taskRow = jQuery (template);
        row.append (taskRow);
    };

    var deleteRow = function (obj, tr, idTable) {
            var row   = jQuery ('#' + tr);

            if (!confirm ('¿Estás seguro que quieres eliminar el elemento seleccionado?')) {
                return;
            }
            jQuery (obj).closest ('tr').remove ();
        };

    var saveHowTo = function (obj, id) {
        var btnSend = jQuery (obj),
            form    = jQuery ('#how-to-form-' + id);

        if (validateForm (form)) {
            form.submit();
        }
    }

    var selectedModule = function (obj, id) {
        var moduleName      = jQuery (obj).val(),
            moduleTex       = jQuery (obj).find('option:selected').text(),
            modalTitle  = 'Modulo: ' + moduleTex,
            div         = jQuery ('#div_howto-assign-record-' +id);
        if (moduleName !== '') {
            div.attr('data-referenced-module', moduleName);
            div.attr('data-title', modalTitle)
        }
    }

    var showPreview = function (objFileInput, id) {
            var idImage = '#option-photo-' + id,
                fileId  = '#how-to-image-upload';

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

    var verifyModule = function (id) {
        var tr = jQuery ('#tr-row-' + id),
            module = tr.find('select').eq(0).val();
        if (module === '' || module === undefined) {
            alert('Seleccionar un modulo');
            return false
        } else {
            return true;
        }
    }

    window.HowToUtils = {
        addAssignToTable:   addRowToTable,
        delRowToTable:      deleteRow,
        saveHowTo:          saveHowTo,
        selectedModule:     selectedModule,
        showPreview:        showPreview,
        validatePhotoSize:  validatePhotoSize,
        verifyModule:       verifyModule
    };

    jQuery(document).on('ready', function () {
        htmlEditor = loadCkEditor ('how-to-html');
    });
}(jQuery));