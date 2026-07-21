{literal}
<!-- Modal de Previsualización de Gráfico -->
<div class="modal fade" id="graphPreviewModal" tabindex="-1" role="dialog" aria-labelledby="graphPreviewModalLabel">
    <div class="modal-dialog" role="document" style="width: 90vw; max-width: 90vw; height: 96vh; margin: 2vh auto;">
        <div class="modal-content" style="height: 100%;">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title text-center" id="graphPreviewModalLabel">Previsualización del Gráfico</h4>
            </div>
            <div class="modal-body" style="height: calc(100% - 120px); padding: 20px; display: flex; flex-direction: column;">
                <div id="graphPreviewContainer" style="width: 100%; height: 100%; display: flex; flex-direction: column;">
                    <img src="themes/images/loading.gif" alt="Cargando..." class="img-responsive center-block" />
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<style>
#graphPreviewModal .modal-dialog {
    margin: 2vh auto;
}

#graphPreviewModal .modal-content {
    border-radius: 8px;
}

#graphPreviewModal .modal-body {
    overflow: auto;
}

#graphPreviewContainer svg {
    max-width: 100% !important;
    max-height: 100% !important;
    width: auto !important;
    height: auto !important;
    display: block !important;
    margin: 0 auto !important;
}

#graphPreviewContainer img {
    max-width: 100%;
    max-height: 100%;
}
</style>
{/literal}

<script type="text/javascript" src="modules/graficosgenerales/graphPreview.js"></script>
