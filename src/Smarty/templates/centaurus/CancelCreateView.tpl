<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cancelando...</title>
    <link rel="stylesheet" type="text/css" href="themes/{$THEME}/style.css">
    <link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/bootstrap.min.css">
    <script type="text/javascript" src="include/js/jquery.js"></script>
    <script type="text/javascript" src="themes/{$THEME}/js/bootstrap.min.js"></script>
    <style>
        body {
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .loading-container {
            text-align: center;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="loading-container">
        <div class="spinner"></div>
        <p>Procesando...</p>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function() {
            {if $MODAL_ID}
                // Hay modal de notificación: Cargarlo y mostrarlo
                var modalUrl = 'index.php?module=notifications&action=NotificationsModal&notificationId={$MODAL_ID}&formodule={$FOR_MODULE}&Ajax=true';
                
                jQuery.ajax({
                    url: modalUrl,
                    type: 'GET',
                    dataType: 'html',
                    success: function(response) {
                        // Crear contenedor del modal
                        var modalHtml = '<div class="modal fade" id="cancel-notification-modal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">' +
                                       '<div class="modal-dialog" role="document">' +
                                       '<div class="modal-content">' +
                                       response +
                                       '</div></div></div>';
                        
                        // Agregar al DOM
                        jQuery('body').append(modalHtml);
                        
                        // Mostrar modal
                        jQuery('#cancel-notification-modal').modal('show');
                        
                        // Cuando se cierre el modal, redirigir
                        jQuery('#cancel-notification-modal').on('hidden.bs.modal', function() {
                            window.location.href = 'index.php?module={$RETURN_MODULE}&action={$RETURN_ACTION}';
                        });
                        
                        // Si el modal tiene botones que cierran, también redirigir
                        jQuery('#cancel-notification-modal').on('click', '[data-dismiss="modal"], .close', function() {
                            setTimeout(function() {
                                window.location.href = 'index.php?module={$RETURN_MODULE}&action={$RETURN_ACTION}';
                            }, 300);
                        });
                    },
                    error: function() {
                        // Si falla, redirigir directamente
                        window.location.href = 'index.php?module={$RETURN_MODULE}&action={$RETURN_ACTION}';
                    }
                });
            {else}
                // No hay modal: Redirigir directamente
                window.location.href = 'index.php?module={$RETURN_MODULE}&action={$RETURN_ACTION}';
            {/if}
        });
    </script>
</body>
</html>
