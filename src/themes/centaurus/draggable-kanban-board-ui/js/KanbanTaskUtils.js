(function (jQuery) {
    //public Method
    var assignRecord = function (obj, e) {
        var dummy  = jQuery(obj).attr('rel').split('@'),
            record = dummy [0],
            module = dummy [1];
        ekkoLightBox = jQuery('<a href=index.php?module=' + module + '&action=AjaxDetailViewUtils&function=CHANGE-ENTITY-OWNER&flmodule=Calendar&Ajax=true&record=' + record + ' data-toggle="lightbox" data-max-width="400" data-title="Asignar expediente">&nbsp;</a>');
        ekkoLightBox.ekkoLightbox({
            loadingMessage: "Cargando...",
            onHidden: function () {
            }
        });
        e.preventDefault();
    };

    var deleteRecord = function (obj, e, item) {
        var postData = {},
            block     = jQuery(obj).parent().parent(),
            dummy     = jQuery(obj).attr('rel').split('@'),
            record    = dummy [0],
            module    = dummy [1];
        console.log(block);
        if (!confirm ('¿Está seguro que desea eliminar el expediente seleccionado?')) {
            return
        }
        postData = {
            'module':    module,
            'action':   'AjaxDetailViewUtils',
            'flmodule': 'Calendar',
            'function': 'DELETE-TASK-RECORD',
            'record':    record,
            'Ajax':     'true'
        };
        jQuery.post ('index.php', postData, function (data) {
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
        var dummy    = jQuery(obj).attr('rel').split('@'),
            record   = dummy [0],
            module   = dummy [1],
            swEditor = (module === 'orden_de_trabajo') ? '&isWork=1' : '';
        ekkoLightBox = jQuery('<a href=index.php?module=Calendar&action=EditView&Ajax=true&record=' + record + swEditor + ' data-toggle="lightbox" data-width="950" data-title="Edición de la tarea">&nbsp;</a>');
        ekkoLightBox.ekkoLightbox({
            loadingMessage: "Cargando...",
            onHidden: function () {
            }
        });
        e.preventDefault();
    };

    var viewRecord = function (obj, e) {
        var dummy  = jQuery(obj).attr('rel').split('@'),
            record = dummy [0];
        if (window.WorkTaskActivityModal && typeof window.WorkTaskActivityModal.openView === 'function') {
            window.WorkTaskActivityModal.openView(record);
        } else {
            alert('Error: El modal de tareas no está disponible. Por favor, recargue la página.');
        }
        e.preventDefault();
    };

    window.KanbanTaskUtils = {
        assignRecord: assignRecord,
        deleteRecord: deleteRecord,
        openRecord:   openRecord,
        viewRecord:   viewRecord
    };

    var onDocumentReadyHandler = function () {
    };

    jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));