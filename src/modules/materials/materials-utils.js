(function (jQuery) {
    var VIMEO_BASE_URL = 'https://vimeo.com/api/oembed.json',
        players        = {};

    //private method
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

    //public method
    var copyUrl = function (id) {
      var $temp = jQuery('<input>'),
          url   = jQuery ('#url-' + id);

      url.closest('.col-xs-6').append($temp);
      $temp.val (url.val()).select();
        try {
            var successful = document.execCommand('copy');
            var msg = successful ? 'successful' : 'unsuccessful';
            console.log('Copying text command was ' + msg);
        } catch (err) {
            console.log('Oops, unable to copy');
        }
      $temp.remove();
    };

    var deleteCategories = function (obj) {
        var form       = jQuery(obj).parent(),
            arguments  = form.serialize(),
            sendButton = jQuery (obj);

        if (!confirm('Después de realizar esta operación deberá reasignar las carpetas incluidas en esta categoría ¿Continuar?')) {
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
                    alert('La categoría ha sido eliminada con éxito. \n Se actualizará esta pagina. Por favor espere');
                    location.reload();
                }
            }
            catch (e) {
                alert(e);
                sendButton.removeAttr('disabled');
            }
        });
    };

    var deleteFile = function (obj) {
        var form       = jQuery(obj).parent(),
            arguments  = form.serialize(),
            sendButton = jQuery (obj);

        if (!confirm('¿Estás seguro que quieres eliminar el documento seleccionado?')) {
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
                    alert('El documento ha sido eliminada con éxito. \n Se actualizará esta pagina. Por favor espere');
                    if (location.href.indexOf('&tab') === -1) {
                        window.location = location.href += '&tab=files-tab';
                    } else {
                        location.reload();
                    }
                }
            }
            catch (e) {
                alert(e);
                sendButton.removeAttr('disabled');
            }
        });
    };

    var deleteFolder = function (obj) {
        var form       = jQuery(obj).parent(),
            arguments  = form.serialize(),
            sendButton = jQuery (obj);

        if (!confirm('¿Estás seguro que quieres eliminar la carpeta seleccionada?\n Esta acción eliminara todos los documentos incluidos en la carpeta')) {
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
                    alert('La carpeta ha sido eliminada con éxito. \n Se actualizará esta página. Por favor espere');
                    location.reload();
                }
            }
            catch (e) {
                alert(e);
                sendButton.removeAttr('disabled');
            }
        });
    };

    var loadFolderPage = function (e) {
        var arguments = {
                'module':   'materials',
                'action':   'AjaxActions',
                'Ajax':     'true',
                'function': 'FOLDER_PAGE'
            },
            section   = jQuery ('#documents-tab');
        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    section.empty();
                    section.html (message.html)
                }
            }
            catch (e) {
                alert(e);
            }
        });
        e.preventDefault();
        e.stopPropagation();
    };

    var downLoadDocument = function (fileId) {
        var record    = fileId,
            section   = jQuery ('#documents-tab'),
            arguments = {
                'module':   'materials',
                'action':   'AjaxActions',
                'Ajax':     'true',
                'record':   record,
                'function': 'DOWNLOAD_DOCUMENT'
            };
        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    alert('Documento descargado!');

                }
            }
            catch (e) {
                alert(e);
            }
        });
    };

    var showDocumentPage = function (event, fileId) {
        var record    = fileId,
            section   = jQuery ('#documents-tab'),
            arguments = {
                'module':    'materials',
                'action':    'AjaxActions',
                'Ajax':      'true',
                'recordData': record,
                'function':   'DOCUMENT_PAGE'
            };
        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    section.empty();
                    section.html (message.html)
                }
            }
            catch (e) {
                alert(e);
            }
        });
        event.preventDefault()
    };

    var showPreview = function (objFileInput) {
        var photoType = jQuery('#photo-ebook');
        if (objFileInput.files[0]) {
            var fileReader = new FileReader();
            fileReader.onload = function (e) {
                jQuery ('#element-photo').attr('src',e.target.result);
                photoType.val (objFileInput.files[0].type)
            };
            fileReader.readAsDataURL(objFileInput.files[0]);
        }
    };

    var showVideo = function (inputElement) {
        var input          = jQuery (inputElement),
            videoUrl       = input.val (),
            videoContainer = jQuery ('#video'),
            videoId        = videoContainer.attr ('id');

        if ((videoUrl === null) || (videoUrl === undefined) || (videoUrl.trim () === '')) {
            if (players.hasOwnProperty (videoId)) {
                players [ videoId ].destroy ();
            }
            videoContainer.addClass('video');
            return;
        }
        videoContainer.removeClass('video');
        jQuery.ajax (VIMEO_BASE_URL, {
            data:     'url=' + videoUrl,
            dataType: 'json',
            method:   'GET'
        }).done (function (data) {
            if ((data !== null) && (data.hasOwnProperty ('video_id')) && (data [ 'video_id' ] > 0)) {
                players [ videoId ] = new Vimeo.Player (videoId, {
                    url: videoUrl
                });
            } else if (players.hasOwnProperty (videoId)) {
                alert ('El URL suministrado no corresponde a un video existente en VIMEO');
                players [ videoId ].destroy ();
            }
        }).fail (function () {
            if (players.hasOwnProperty (videoId)) {
                alert ('El URL suministrado no corresponde a un video existente en VIMEO');
                players [ videoId ].destroy ();
                videoContainer.addClass('video');
            }
        });
    };

    var validateFileSize = function (element, uploadSize) {
        if (jQuery(element).val() === '') {
            return true;
        }
        var fileSize = element.files[ 0 ].size;
        if (fileSize > uploadSize) {
            alert ('El tamaño del Archivo no debe ser superior a ' + uploadSize / (1024 * 1024) + 'MB');
            element.value = '';
        }

    };

    window.MaterialsUtils = {
        copyUrl:          copyUrl,
        deleteCategories: deleteCategories,
        deleteFile:       deleteFile,
        deleteFolder:     deleteFolder,
        downLoadDocument: downLoadDocument,
        loadFolderPage:   loadFolderPage,
        showDocumentPage: showDocumentPage,
        showPreview:      showPreview,
        showVideo:        showVideo,
        validateFileSize: validateFileSize
    };
    jQuery (document).on ('ready', function () {
        var pageUrl = window.location;
        console.log(pageUrl);
        if(pageUrl.search.indexOf('ebook') !== -1) {
            var dummy = pageUrl.search.split('=');
            showDocumentPage(event, dummy[(dummy.length - 1)])
        }
        loadCkEditor ('filedescription');
    });

} (jQuery));