<li id="module-row-0" class="module-column list-group-item">
    <div class="row">
        {if $smarty.section.key.index eq 0}
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
        {/if}
        <div id="gr-td-wmodules[]" class="col-xs-3 variable-cell">
            <select name="wmodules[]" id="wmodule" class="form-control wmodule" title="El módulo"
                    onchange="GraphUtils.getGraphicalColumns (this);">
                <option value=""{if (empty ($graphModuleName))} selected="selected"{/if}>
                    Seleccione módulo
                </option>
                {foreach $AVAILABLE_MODULES as $module}
                    <option value="{$module.name}" {if ($graphModuleName[key] eq $module.name)} selected="selected"{/if}>{$module.tablabel}</option>
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
                    {foreach $AVAILABLE_FIELDS[key] as $field}
                        {assign var='tableField' value=$field.tablename|cat:'.'|cat:$field.fieldname}
                        <option value="{$tableField}" {if ($graphFieldOperation[key] eq $tableField)} selected="selected"
                            {assign var='selectedField' value=$tableField}
                            {capture name=optionSelected}{$field.label}{/capture}{/if}
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
                <option value=""{if (empty (operationId))} selected="selected"{/if}>
                    Seleccione tipo de operación
                </option>
                {foreach $AVAILABLE_OPERATIONS as $operationId => $operationName}
                    <option value="{$operationId}" {if ($graphOperation[key] eq $operationId)} selected="selected"{else} disabled="disabled" {/if}>{$operationName}</option>
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
            <button type="button" class="btn btn-link {if $smarty.section.key.index <1} hide {/if}"
                    onclick="GraphUtils.eraseModuleRow (this);"
                    title="Eliminar datoa a graficar"><i
                        class="fa fa-trash-o"></i></button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 justify-content-center">
            <div class="form-group condition-groups">
                <div class="action-bar text-center">
                    {if (isset ($RECORD) && !empty ($RECORD) && $GRAPH_FILTER[$smarty.section.key.index][$graphModuleName[key]]|@count gt 0)}
                        {assign var=filters value=$GRAPH_FILTER[$smarty.section.key.index][$graphModuleName[key]]}
                        {assign var="totalGroup" value=$filters['filterGroupJoin']|@count}
                        {assign var="filterField" value=$filters['filterField']}
                        {assign var="filterOperator" value=$filters['filterOperator']}
                        {assign var="filterValue" value=$filters['filterValue']}
                        {assign var="filterJoin" value=$filters['filterJoin']}
                        {assign var="filterGroupJoin" value=$filters['filterGroupJoin']}
                        {assign var="indexGrupo" value=$filters['indexGrupo']}
                        {assign var="totalIndex" value=$filters['indexGrupo']|@count}
                        {assign var="star" value=1}
                        {assign var="indexJoin" value=-1}
                        {if ! empty($filters['filterField'])}
                            {include file="modules/graficosgenerales/filterGraphEdit.tpl"}
                            {$totalGroup = $totalGroup + 1}

                        {else}
                            {assign var="totalGroup" value=0}
                            {assign var="totalIndex" value=0}
                        {/if}

                    {else}
                        {assign var="totalGroup" value=0}
                        {assign var="totalIndex" value=0}
                    {/if}
                </div>
            </div>
        </div>
    </div>
</li>