/**
 * Inicialización de Popover para campos de área de texto
 * Activa tooltips/modals para mostrar texto completo en DetailView
 * Usa Bootstrap Popover en lugar de Protip para mayor compatibilidad
 */
(function($) {
    'use strict';
    
    // Función para inicializar popovers en campos de texto
    function initTextFieldPopovers() {
        var elements = $('.protip');
        
        // Solo mostrar log si hay elementos para procesar
        if (elements.length > 0) {
            console.debug('[Protip-Init] Procesando ' + elements.length + ' elementos .protip');
        }
        
        // Destruir popovers existentes para evitar duplicados
        elements.popover('dispose');
        
        // Inicializar popover en elementos con clase .protip
        elements.each(function(index) {
            var $element = $(this);
            var content = $element.attr('data-pt-title');
            var width = $element.attr('data-pt-width') || 400;
            
            // Solo inicializar si tiene contenido
            if (content && content.length > 0) {
                $element.popover({
                    content: content,
                    trigger: 'hover',
                    placement: 'top',
                    html: true,
                    container: 'body',
                    template: '<div class="popover text-area-popover" role="tooltip" style="max-width: ' + width + 'px;"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'
                });
            }
        });
    }
    
    // Inicializar cuando el DOM esté listo
    $(document).ready(function() {
        initTextFieldPopovers();
    });
    
    // Re-inicializar después de actualizaciones AJAX
    $(document).ajaxComplete(function() {
        setTimeout(initTextFieldPopovers, 100);
    });
    
})(jQuery);
