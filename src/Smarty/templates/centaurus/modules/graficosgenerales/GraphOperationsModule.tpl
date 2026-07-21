<li id="module-row-__GROUP_ID__" class="module-column list-group-item calculation-select">
    <div class="row">
        <div class="col-xs-12">
            <p class="pull-left" style="padding-left: 4px;font-weight: bold">2.1.- Fuente de datos calculado:</p>
        </div>
        <div id="graphc-module-column-titles">
            <div class="col-xs-3 variable-cell" style="margin-bottom: 2px">
                Campo:
            </div>
            <div class="col-xs-3 variable-cell" style="margin-bottom: 2px">
                Operación:
            </div>
            <div class="col-xs-3 variable-cell" style="margin-bottom: 2px">
                Campo:
            </div>
            <div class="col-xs-2 variable-cell" style="margin-bottom: 2px">
                &nbsp;
            </div>
            <div class="col-xs-1 variable-cell">
            </div>
        </div>
        <div class="col-xs-3">
            <input type="text" class="form-control" title=""
                   readonly value="">
            <input type="hidden" name="fieldsOperations[]" value="">
        </div>
        <div id="gr-td-wmodules[]" class="col-xs-3 variable-cell">
            <select name="fieldsOperations[]" id="wmodule" class="form-control" title="El tipo de operación">
                <option value="" selected="selected">
                    Seleccione operación
                </option>
                {foreach $OPERATION_COLUMNS as $key => $operation}
                    <option value="{$key}">{$operation}</option>
                {/foreach}
            </select>
            <span id="gr-wmodules[]" class="help-block"></span>
        </div>
        <div class="col-xs-3">
            <input type="text" class="form-control" title="" readonly value="">
            <input type="hidden" name="fieldsOperations[]" value="">
        </div>
        <div class="col-xs-2">
            &nbsp;
        </div>
        <div class="col-xs-1 text-right">
            <button type="button" class="btn btn-link"
                    onclick="GraphUtils.eraseModuleRow (this);"
                    title="Eliminar datoa a graficar"><i
                        class="fa fa-trash-o"></i></button>
        </div>
    </div>
</li>