<li id="module-row-0" class="module-column list-group-item">
    <div class="row">
        <div id="graphc-module-column-titles">
            <div class="col-xs-3 variable-cell" style="margin-bottom: 2px">
                Módulo:
            </div>
            <div class="col-xs-3 variable-cell" style="margin-bottom: 2px">
                Campo:
            </div>
            <div class="col-xs-3 variable-cell" style="margin-bottom: 2px">
                Operación:
            </div>
            <div class="col-xs-2 variable-cell" style="margin-bottom: 2px">
                <p class="center-text">Filtros</p>
            </div>
            <div class="col-xs-1 variable-cell">
            </div>
        </div>
        <div id="gr-td-wmodules[]" class="col-xs-3 variable-cell">
            <select name="wmodules[]" id="wmodule" class="form-control wmodule" title="El módulo"
                    onchange="GraphUtils.getGraphicalColumns (this);">
                <option value="" selected="selected">
                    Seleccione módulo
                </option>
                {foreach $AVAILABLE_MODULES as $module}
                    <option value="{$module.name}">{$module.tablabel}</option>
                {/foreach}
            </select>
            <span id="gr-wmodules[]" class="help-block"></span>
        </div>
        <div id="gr-td-fieldoperation[]" class="col-xs-3">
            <select class="form-control" id="fieldoperation"
                    name="fieldoperation[]" title="El campo"
                    onchange="GraphUtils.setFieldOperation (this);">
                <option value=""{if (empty (operationId))} selected="selected"{/if}>
                    Seleccione campo
                </option>
                {if (isset ($AVAILABLE_FIELDS)) && (!empty ($AVAILABLE_FIELDS))}
                    {foreach $AVAILABLE_FIELDS as $field}
                        <option value="{$field.fieldname}"
                                data-type="{$field.typeofdata}"
                                data-uitype="{$field.uitype}">{$field.label}</option>
                    {/foreach}
                {/if}
            </select>
            <span id="gr-fieldoperation[]" class="help-block"></span>
        </div>
        <div id="gr-td-opcolumn[]" class="col-xs-3">
            <select name="opcolumn[]" id="opcolumn" class="form-control"
                    title="El tipo de operación">
                <option value="">
                    Seleccione tipo de operación
                </option>
                {foreach $AVAILABLE_OPERATIONS as $operationId => $operationName}
                    <option value="{$operationId}">{$operationName}</option>
                {/foreach}
            </select>
            <span id="gr-opcolumn[]" class="help-block"></span>
        </div>
        <div class="col-xs-2">
            <div class="row">
                <div class="col-xs-11">
                    <button type="button" class="btn btn-success " data-group="0"
                          onclick="GraphUtils.addFilterGroup (this);"
                          title="Agregar grupo de condiciones">
                            <i class="fa fa-plus" aria-hidden="true">&nbsp;Grupo de condiciones</i>
                </button>
                </div>
                <div class="filter-btn col-xs-1" style="cursor: pointer"
                     onclick="GraphUtils.accordionFilters(this)">
                    <i class="fa fa-arrow-up"></i>
                </div>
            </div>
        </div>
        <div class="col-xs-1 text-right">
            <button type="button" class="btn btn-link hide"
                    onclick="GraphUtils.eraseModuleRow (this);"
                    title="Eliminar datoa a graficar"><i
                        class="fa fa-trash-o"></i></button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 justify-content-center">
            <div class="form-group condition-groups">
                <div class="action-bar text-center">
                    {*
                    <button type="button" class="btn btn-success " data-group="0"
                            onclick="GraphUtils.addFilterGroup (this);"
                            title="Agregar grupo de condiciones">
                        <i class="fa fa-plus" aria-hidden="true">&nbsp;Grupo de condiciones</i></button>
                    *}
                </div>
            </div>
        </div>
    </div>
</li>