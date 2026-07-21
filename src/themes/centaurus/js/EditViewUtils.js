/**
 * EditViewUtils
 * Utilidades para el formulario de edición/creación de registros
 * Incluye soporte para notificaciones CANCEL_RECORD
 */

var EditViewUtils = (function() {
    'use strict';

    /**
     * Maneja la cancelación con modal de notificación
     * @param {string} module - Nombre del módulo
     * @param {string} recordId - ID del registro (puede estar vacío en modo creación)
     * @param {string} notificationId - ID de la notificación modal a mostrar
     */
    function handleCancelWithModal(module, recordId, notificationId) {
        if (!notificationId || notificationId === '') {
            // No hay notificación configurada, redirigir directamente
            redirectAfterCancel(module, recordId);
            return;
        }

        // Mostrar modal de notificación
        var modalUrl = 'index.php?module=notifications&action=NotificationsModal&notificationId=' + 
                       encodeURIComponent(notificationId) + 
                       '&formodule=' + encodeURIComponent(module);
        
        if (recordId && recordId !== '') {
            modalUrl += '&record=' + encodeURIComponent(recordId);
        }
        
        modalUrl += '&Ajax=true';

        // Cargar modal usando el sistema de lightbox de Platzilla
        jQuery.ajax({
            url: modalUrl,
            type: 'GET',
            dataType: 'html',
            success: function(response) {
                // Crear modal dinámicamente
                var modalHtml = '<div class="modal fade" id="cancel-notification-modal" tabindex="-1" role="dialog">' +
                               '<div class="modal-dialog" role="document">' +
                               '<div class="modal-content">' +
                               '<div class="modal-header">' +
                               '<button type="button" class="close" data-dismiss="modal" aria-label="Close">' +
                               '<span aria-hidden="true">&times;</span></button>' +
                               '<h4 class="modal-title">Cancelar</h4>' +
                               '</div>' +
                               '<div class="modal-body">' + response + '</div>' +
                               '<div class="modal-footer">' +
                               '<button type="button" class="btn btn-primary" onclick="EditViewUtils.confirmCancel(\'' + module + '\', \'' + recordId + '\');">Aceptar</button>' +
                               '</div>' +
                               '</div></div></div>';
                
                // Agregar modal al DOM
                jQuery('body').append(modalHtml);
                
                // Mostrar modal
                jQuery('#cancel-notification-modal').modal('show');
                
                // Limpiar modal del DOM cuando se cierre
                jQuery('#cancel-notification-modal').on('hidden.bs.modal', function() {
                    jQuery(this).remove();
                });
            },
            error: function() {
                // Si falla la carga del modal, redirigir directamente
                redirectAfterCancel(module, recordId);
            }
        });
    }

    /**
     * Confirma la cancelación y redirige
     * @param {string} module - Nombre del módulo
     * @param {string} recordId - ID del registro
     */
    function confirmCancel(module, recordId) {
        // Cerrar modal si está abierto
        jQuery('#cancel-notification-modal').modal('hide');
        
        // Redirigir
        redirectAfterCancel(module, recordId);
    }

    /**
     * Redirige después de cancelar
     * @param {string} module - Nombre del módulo
     * @param {string} recordId - ID del registro (vacío en modo creación)
     */
    function redirectAfterCancel(module, recordId) {
        var redirectUrl;
        
        if (recordId && recordId !== '' && recordId !== 'undefined') {
            // Modo EDICIÓN: Volver al DetailView
            redirectUrl = 'index.php?module=' + encodeURIComponent(module) + 
                         '&action=DetailView&record=' + encodeURIComponent(recordId);
        } else {
            // Modo CREACIÓN: Volver al ListView
            redirectUrl = 'index.php?module=' + encodeURIComponent(module) + '&action=ListView';
        }
        
        window.location.href = redirectUrl;
    }

    // API pública
    return {
        handleCancelWithModal: handleCancelWithModal,
        confirmCancel: confirmCancel,
        redirectAfterCancel: redirectAfterCancel
    };
})();
