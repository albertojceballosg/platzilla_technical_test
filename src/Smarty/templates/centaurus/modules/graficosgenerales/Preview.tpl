<style>
    #preview-container {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: stretch;
        padding: 0;
        margin: 0;
    }
    
    #preview-container .main-box-header {
        margin: 0;
        padding: 15px 20px;
        width: 100%;
        flex-shrink: 0;
    }
    
    #preview-container .main-box-body {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        flex: 1;
        min-height: 0;
        padding: 0 20px 20px 20px;
    }
    
    #preview-container .graph.simple {
        width: 100% !important;
        height: 100% !important;
        min-height: 400px !important;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    #preview-container .graph.simple img[alt="Loading"] {
        width: auto !important;
        height: auto !important;
        max-width: 64px !important;
        max-height: 64px !important;
    }
</style>

<div id="preview-container">
    <header class="main-box-header clearfix text-center">
        <h2>{$GRAPH.title}</h2>
    </header>
    <div class="main-box-body">
        <div id="{$GRAPH.applicationcode}-{$GRAPH.tipografico}-{$GRAPH.graficoid}" class="graph simple {$GRAPH.tipografico}">
            <img src="themes/images/loading.gif" alt="Loading" class="img-responsive center-block"/>
            <div class="alert alert-info text-center" style="display: none;">
                <div class="message" style="margin-bottom: 5px;">
                    No hay data para graficar
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
// Asegurar que el contenedor tenga dimensiones antes de cargar el gráfico
(function() {
    var containerId = '{$GRAPH.applicationcode}-{$GRAPH.tipografico}-{$GRAPH.graficoid}';
    
    // Esperar a que el DOM esté completamente cargado
    var checkContainer = function() {
        var container = document.getElementById(containerId);
        
        if (container) {
            // Remover la imagen de loading cuando el gráfico se renderice
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length > 0) {
                        var svg = container.querySelector('svg');
                        if (svg) {
                            var loadingImg = container.querySelector('img[alt="Loading"]');
                            if (loadingImg) {
                                loadingImg.style.display = 'none';
                            }
                            observer.disconnect();
                        }
                    }
                });
            });
            
            observer.observe(container, { childList: true, subtree: true });
        }
    };
    
    // Ejecutar inmediatamente y también después de un delay
    checkContainer();
    setTimeout(checkContainer, 100);
})();
</script>

{loadGraphic objGraphic=$GRAPH}