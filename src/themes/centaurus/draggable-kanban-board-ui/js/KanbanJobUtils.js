(function (jQuery) {

    //public Method
    var assignRecord = function (obj, e) {
        var dummy  = jQuery(obj).attr('rel').split('@'),
            record = dummy [0],
            module = dummy [1];
        ekkoLightBox = jQuery('<a href=index.php?module=' + module + '&action=AjaxDetailViewUtils&function=CHANGE-ENTITY-OWNER&flmodule=' + module + '&Ajax=true&record=' + record + ' data-toggle="lightbox" data-max-width="400" data-title="Asignar expediente">&nbsp;</a>');
        ekkoLightBox.ekkoLightbox({
            loadingMessage: "Cargando...",
            onHidden: function () {
            }
        });
        e.preventDefault();
    };

    var deleteRecord = function (obj, e, item) {
        var arguments = {},
            block     = jQuery(obj).parent().parent(),
            dummy     = jQuery(obj).attr('rel').split('@'),
            record    = dummy [0],
            module    = dummy [1];
        console.log(block);
        if (!confirm ('¿Está seguro que desea eliminar el expediente seleccionado?')) {
            return
        }
        arguments = {
            'module':    module,
            'action':   'AjaxDetailViewUtils',
            'flmodule':  module,
            'function': 'DELETE-TASK-RECORD',
            'record':    record,
            'Ajax':     'true'
        };
        jQuery.post ('index.php', arguments, function (data) {
            try {
                var message = JSON.parse(JSON.stringify(data));
                if (message.error !== 'OK') {
                    throw message.error;
                } else {
                    block.remove();
                }
            }
            catch (e) {
                alert(e);
            }
        });
    };

    var openRecord = function (obj, e) {
        var dummy  = jQuery(obj).attr('rel').split('@'),
            record = dummy [0],
            module = dummy [1];
        window.open ('index.php?module=' + module + '&action=DetailView&record=' + record, '_blank');
        e.preventDefault();
    };

    var viewRecord = function (obj, e) {
        var dummy  = jQuery(obj).attr('rel').split('@'),
            record = dummy [0],
            module = dummy [1];
        ekkoLightBox = jQuery('<a href=index.php?module=' + module + '&action=AjaxDetailViewUtils&function=VIEW-TASK-RECORD&flmodule=' + module + '&Ajax=true&record=' + record + ' data-toggle="lightbox" data-max-width="400" data-title="Detalle de la tarea">&nbsp;</a>');
        ekkoLightBox.ekkoLightbox({
            loadingMessage: "Cargando...",
            onHidden: function () {
            }
        });
        e.preventDefault();
    };


    window.KanbanJobUtils = {
        assignRecord: assignRecord,
        deleteRecord: deleteRecord,
        openRecord:   openRecord,
        viewRecord:   viewRecord
    };

    var onDocumentReadyHandler = function () {
    };

    jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));