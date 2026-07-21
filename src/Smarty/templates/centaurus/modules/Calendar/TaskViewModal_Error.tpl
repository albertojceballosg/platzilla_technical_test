<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="{$APP.LBL_CLOSE}">
        <span aria-hidden="true">&times;</span>
    </button>
    <h4 class="modal-title">
        <i class="bi bi-exclamation-triangle text-warning"></i> {$MOD.LBL_TASK_INFORMATION|default:'Información de la Tarea'}
    </h4>
</div>

<div class="modal-body">
    <div class="alert alert-warning text-center" style="margin: 20px 0;">
        <i class="bi bi-exclamation-circle" style="font-size: 48px; display: block; margin-bottom: 15px;"></i>
        <h4>{$ERROR_MESSAGE}</h4>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">
        <i class="bi bi-x-circle"></i> {$APP.LBL_CLOSE|default:'Cerrar'}
    </button>
</div>
