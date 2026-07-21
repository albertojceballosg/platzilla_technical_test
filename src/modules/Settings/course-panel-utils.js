(function (jQuery) {
    var newPhoto;

    var changeImage = function (obj, event) {
        var sendButton = jQuery (obj),
            record      = parseInt(sendButton.attr('data-record'));
        ekkoLightBox = jQuery('<a href=index.php?module=Settings&action=CourseAjaxUtils&function=CHAMGE_PHOTO&Ajax=true&record='+record+' data-toggle="lightbox" data-max-width="400" data-title="">&nbsp;</a>');
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

    var changeStatusCategory = function (obj) {
        var sendButton   = jQuery (obj),
            record    = sendButton.attr('data-record'),
            status    = sendButton.attr('data-status'),
            arguments = {
                'module':   'Settings',
                'action':   'CourseAjaxUtils',
                'record':   parseInt (record),
                'status':   status,
                'Ajax':     'true',
                'function': 'CHAMGE_STATUS_CATEGORY'
            },
            change = (status === 'ENABLED') ? '𝐃𝐞𝐬𝐚𝐜𝐭𝐢𝐯𝐚𝐝𝐚' : '𝐀𝐜𝐭𝐢𝐯𝐚𝐝𝐚';
        if (!confirm ('Con esta acción la categoría quedará ' + change+ '\n ¿Continuar?')) {
            return
        }
        sendButton.attr('disabled','disabled');
        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    alert('La categoría ha sido ' + change + '!');
                    location.reload();
                }
            }
            catch (e) {
                alert(e);
            }
        });
        sendButton.removeAttr('disabled');
    };

    var changeStatusCourse = function (obj) {
        var sendButton   = jQuery (obj),
            record    = sendButton.attr('data-record'),
            status    = sendButton.attr('data-status'),
            arguments = {
                'module':   'Settings',
                'action':   'CourseAjaxUtils',
                'record':   parseInt (record),
                'status':   status,
                'Ajax':     'true',
                'function': 'CHAMGE_STATUS_COURSE'
            },
            change = (status === 'ACTIVE') ? '𝐃𝐞𝐬𝐚𝐜𝐭𝐢𝐯𝐚𝐝𝐨' : '𝐀𝐜𝐭𝐢𝐯𝐚𝐝𝐨';
        if (!confirm ('Con esta acción el curso quedará ' + change+ '\n ¿Continuar?')) {
            return
        }
        sendButton.attr('disabled','disabled');
        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    alert('El curso ha sido ' + change + '!');
                    location.reload();
                }
            }
            catch (e) {
                alert(e);
            }
        });
        sendButton.removeAttr('disabled');
    };

    var changeStatusSerie = function (obj) {
        var sendButton   = jQuery (obj),
            record    = sendButton.attr('data-record'),
            status    = sendButton.attr('data-status'),
            arguments = {
                'module':   'Settings',
                'action':   'CourseAjaxUtils',
                'record':   parseInt (record),
                'status':   status,
                'Ajax':     'true',
                'function': 'CHAMGE_STATUS_SERIE'
            },
            change = (status === 'ENABLED') ? '𝐃𝐞𝐬𝐚𝐜𝐭𝐢𝐯𝐚𝐝𝐚' : '𝐀𝐜𝐭𝐢𝐯𝐚𝐝𝐚';
        if (!confirm ('Con esta acción la serie quedará ' + change+ '\n ¿Continuar?')) {
            return
        }
        sendButton.attr('disabled','disabled');
        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    alert('La serie ha sido ' + change + '!');
                    location.reload();
                }
            }
            catch (e) {
                alert(e);
            }
        });
        sendButton.removeAttr('disabled');
    };

    var clonarCourse = function (obj) {
        var sendButton   = jQuery (obj),
            record    = sendButton.attr('data-record'),
            arguments = {
                'module':   'Settings',
                'action':   'CourseAjaxUtils',
                'record':   parseInt (record),
                'Ajax':     'true',
                'function': 'CLONE_COURSE'
            };
        if (!confirm ('El curso seleccionado será 𝐃𝐮𝐩𝐥𝐢𝐜𝐚𝐝𝐨\n ¿Continuar?')) {
            return
        }
        sendButton.attr('disabled','disabled');
        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    alert('El curso ha sido 𝐃𝐮𝐩𝐥𝐢𝐜𝐚𝐝𝐨!');
                    location.reload();
                }
            }
            catch (e) {
                alert(e);
            }
        });
        sendButton.removeAttr('disabled');
    };

    var deleteCategory = function (obj) {
        var sendButton   = jQuery (obj),
            record    = sendButton.attr('data-record'),
            arguments = {
                'module':   'Settings',
                'action':   'CourseAjaxUtils',
                'record':   parseInt (record),
                'Ajax':     'true',
                'function': 'DELETE_IN_COURSE',
                'from':     'CATEGORY'
            };
        if (!confirm ('La categoría seleccionada será 𝐄𝐥𝐢𝐦𝐢𝐧𝐚𝐝𝐚!\n ¿Continuar?')) {
            return
        }
        sendButton.attr('disabled','disabled');
        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    alert('La categoría ha sido 𝐄𝐥𝐢𝐦𝐢𝐧𝐚𝐝𝐚!');
                    location.reload();
                }
            }
            catch (e) {
                alert(e);
            }
        });
        sendButton.removeAttr('disabled');
    };

    var deleteCourse = function (obj) {
        var sendButton   = jQuery (obj),
            record    = sendButton.attr('data-record'),
            arguments = {
                'module':   'Settings',
                'action':   'CourseAjaxUtils',
                'record':   parseInt (record),
                'Ajax':     'true',
                'function': 'DELETE_IN_COURSE',
                'from':     'COURSE'
            };
        if (!confirm ('El curso seleccionado será 𝐄𝐥𝐢𝐦𝐢𝐧𝐚𝐝𝐨!\n ¿Continuar?')) {
            return
        }
        sendButton.attr('disabled','disabled');
        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    alert('El curso ha sido 𝐄𝐥𝐢𝐦𝐢𝐧𝐚𝐝𝐨!');
                    location.reload();
                }
            }
            catch (e) {
                alert(e);
            }
        });
        sendButton.removeAttr('disabled');
    };

    var deleteSerie = function (obj) {
        var sendButton   = jQuery (obj),
            record    = sendButton.attr('data-record'),
            arguments = {
                'module':   'Settings',
                'action':   'CourseAjaxUtils',
                'record':   parseInt (record),
                'Ajax':     'true',
                'function': 'DELETE_IN_COURSE',
                'from':     'SERIE'
            };
        if (!confirm ('La serie seleccionada será 𝐄𝐥𝐢𝐦𝐢𝐧𝐚𝐝𝐚!\n ¿Continuar?')) {
            return
        }
        sendButton.attr('disabled','disabled');
        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    alert('La serie ha sido 𝐄𝐥𝐢𝐦𝐢𝐧𝐚𝐝𝐚!');
                    location.reload();
                }
            }
            catch (e) {
                alert(e);
            }
        });
        sendButton.removeAttr('disabled');
    };

    var filterCategories = function (obj) {
        var categorySelected = jQuery (obj).val (),
            serieSelected    = jQuery ('#forserie'),
            tbody            = jQuery('#course-panel-table > tr');

        if (categorySelected !== '') {
            jQuery('tr[id ^= cat-' + categorySelected+ '-]').removeClass ('hide');
            tbody.each(function (i, elemnet) {
                var tr = jQuery (elemnet),
                    trId = tr.attr('id').split('-');
                if (trId[ 1 ] !== categorySelected) {
                    tr.addClass('hide')
                }
            })
        } else if(serieSelected.val () === '') {
            tbody.each(function (i, elemnet) {
                jQuery (elemnet).removeClass ('hide');
            })
        } else {
            filterSerie (serieSelected);
        }
    };

    var filterSerie = function (obj) {
        var categorySelected = jQuery ('#forcategory'),
            serieSelected    = jQuery (obj).val(),
            tbody            = jQuery('#course-panel-table > tr');

        if (serieSelected !== '') {
            jQuery('tr[id $= -ser-' + serieSelected+ ']').removeClass ('hide');
            tbody.each(function (i, elemnet) {
                var tr = jQuery (elemnet),
                    trId = tr.attr('id').split('-');
                if (trId[ 5 ] !== serieSelected) {
                    tr.addClass('hide')
                }
            })
        } else if(categorySelected.val () === '') {
            tbody.each(function (i, elemnet) {
                jQuery (elemnet).removeClass ('hide');
            })
        } else {
            filterCategories (categorySelected)
        }
    };

    var showPreview = function (objFileInput, id) {
        var idImage = '#course-photo-' + id,
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

    var updateCoursePhoto = function (id) {
        var form = jQuery ('#form-' + id),
            field, value, arguments;
        field = form.find ("input[name='imageType']");
        value = field.val ();
        if ((value === null) || (value === undefined) || (value.trim () === '')) {
            alert ('Selecciona una foto');
            return;
        }
        arguments = form.serialize() + '&photo=' + encodeURIComponent(newPhoto);
        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    alert('La foto ha sido actualizada!');
                    location.reload();
                }
            }
            catch (e) {
                alert(e);
            }
        });
    };

    var validatePhotoSize = function (element, uploadSize) {
        if (jQuery(element).val() === '') {
            return true;
        }
        var fileSize = element.files[ 0 ].size;
        if (fileSize > uploadSize) {
            alert ('El tamaño del Archivo no debe ser superior a ' + uploadSize / (1024 * 1024) + 'MB');
            element.value = '';
        }
    };

    window.CoursePanelUtils = {
        changeImage:          changeImage,
        changeStatusCategory: changeStatusCategory,
        changeStatusCourse:   changeStatusCourse,
        changeStatusSerie:    changeStatusSerie,
        clonarCourse:         clonarCourse,
        deleteCategory:       deleteCategory,
        deleteCourse:         deleteCourse,
        deleteSerie:          deleteSerie,
        filterCategories:     filterCategories,
        filterSerie:          filterSerie,
        showPreview:          showPreview,
        updateCoursePhoto:    updateCoursePhoto,
        validatePhotoSize:    validatePhotoSize

    };

    jQuery (document).ready (function () {
    });
} (jQuery));