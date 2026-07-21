// Protección contra carga múltiple de calculatedsystem.js
(function() {
    'use strict';
    
    // Verificar si ya está cargado
    if (window.CSUtils) {
        console.warn('calculatedsystem.js ya está cargado, evitando duplicación');
        return;
    }
    
    // Marcar que el script se está inicializando
    window.CSUtilsLoading = true;
    
    // Función para verificar select2 con reintentos
    function checkSelect2Dependencies() {
        var attempts = 0;
        var maxAttempts = 20; // Aumentado a 20 intentos
        
        function tryInitSelect2() {
            attempts++;
            
            console.log('Intento', attempts, '- Verificando dependencias:');
            console.log('  jQuery disponible:', typeof jQuery !== 'undefined');
            console.log('  jQuery.fn disponible:', typeof jQuery !== 'undefined' && !!jQuery.fn);
            console.log('  jQuery.fn.select2 disponible:', typeof jQuery !== 'undefined' && jQuery.fn && !!jQuery.fn.select2);
            
            if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.select2) {
                console.log('✅ Select2 disponible después de', attempts, 'intentos');
                window.CSUtilsSelect2Ready = true;
                return true;
            } else if (attempts < maxAttempts) {
                setTimeout(tryInitSelect2, 200); // Aumentado a 200ms
            } else {
                console.warn('❌ Select2 no pudo cargarse después de', maxAttempts, 'intentos');
                console.warn('Estado final:');
                console.warn('  jQuery:', typeof jQuery);
                console.warn('  jQuery.fn:', typeof jQuery !== 'undefined' ? typeof jQuery.fn : 'N/A');
                console.warn('  jQuery.fn.select2:', typeof jQuery !== 'undefined' && jQuery.fn ? typeof jQuery.fn.select2 : 'N/A');
                window.CSUtilsSelect2Ready = false;
            }
        }
        
        // Iniciar verificación
        tryInitSelect2();
    }
    
    // Verificar dependencias cuando jQuery esté listo
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ready(function() {
            checkSelect2Dependencies();
        });
    } else {
        // Si jQuery no está disponible, intentar más tarde
        setTimeout(function() {
            if (typeof jQuery !== 'undefined') {
                jQuery(document).ready(function() {
                    checkSelect2Dependencies();
                });
            }
        }, 500);
    }
    
})();
