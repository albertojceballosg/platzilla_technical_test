{strip}
    {section name=Group start=0 loop=($totalGroup + 1)}
        {assign var="delBottonIndex" value=0}
        <div class="condition-group list-group filter_goup" id="group-{$smarty.section.Group.index}"  data-id="{$smarty.section.Group.index}">
            <div class="condition-group-header list-group-item">
                <div class="row">
                    <div class="col-xs-4">Variable</div>
                    <div class="col-xs-2">Operador</div>
                    <div class="col-xs-4">Valor</div>
                    <div class="col-xs-1"></div>
                    <div class="col-xs-1 text-right">
                        <button type="button" class="btn btn-link" onclick="CBUtils.eraseFilterGroup (this);" title="Eliminar grupo de condiciones"><i class="fa fa-trash-o"></i></button>
                    </div>
                </div>
            </div>
            <div class="condition-group-body list-group-item">
                <ul class="list-group conditions">
                <!-- Lista de condiciones -->
                    {for $ig=$star to $totalIndex}
                    <li id="row-{($ig - 1)}"     class="condition list-group-item" data-id="{$conditionId}">
                        <div class="row">
                            <div class="col-xs-4 variable-cell ">
                                <select class="form-control" id="fieldFilter" name="filterField[]" title="" onchange="CBUtils.setFilterOperators (this);">
                                    <option value="">-Ninguno-</option>
                                    {if (isset ($FIELD_LIST)) && (!empty ($FIELD_LIST))}
                                        {foreach $FIELD_LIST as $field}
                                            {if $field.typeofdata neq ''}
                                                <option value="{$field.fieldname}" data-type="{$field.typeofdata}"{if ($filterField[($ig - 1)] == $field.fieldname)} {$selectedTypeOfData = $field.typeofdata}  selected="selected"{/if}>{$field.label}</option>
                                            {/if}
                                        {/foreach}
                                    {/if}
                                </select>
                                <span class="help-block" style="color: red"></span>
                            </div>
                            <div class="col-xs-2">
                                <select class="form-control" id="filterOperator" name="filterOperator[]" title="Operadores" onchange="CBUtils.setHelpToField(this)">
                                    <option value="">-Ninguno-</option>
                                    {foreach from=$FILTER_TYPE[$selectedTypeOfData] key=k item=v}
                                        <option value="{$k}" {if $filterOperator[($ig - 1)] eq $k} selected {/if}>{$v}</option>
                                    {/foreach}
                                </select>
                                <span  class="help-block" style="color: red"></span>
                            </div>
                            <div class="col-xs-4">
                                <div class="input-group">
                                    <input name="filterValue[]" id="filterValue" class="form-control" value="{$filterValue[($ig - 1)]}" type="text" placeholder="">
                                    <div class="input-group-addon" onclick="CBUtils.eraseFilterValue (this);" title="Borrar" alt="Borrar"><i class="fa fa-eraser"></i>
                                    </div>
                                </div>
                                <span  class="help-block" style="color: red"></span>
                            </div>
                            <div class="col-xs-1">
                                <select name="filterJoin[]" id="filterJoin" class="form-control {if $indexGrupo[$ig] neq  $indexGrupo[($ig - 1)] }hidden" disabled="disabled" style="padding: 0px;" {else}"{$indexJoin = ($indexJoin +1)} {/if}>
                                    <option value="AND" {if $filterJoin[$indexJoin] eq "AND"}  selected {/if} >&nbsp;&nbsp;&nbsp;y</option>
                                    <option value="OR" {if $filterJoin[$indexJoin] eq "OR" } selected {/if}>&nbsp;&nbsp;&nbsp;o</option>
                                </select>
                            </div>
                            <div class="col-xs-1 text-right">
                                <button type="button" class="btn btn-link {if $delBottonIndex eq 0} {$delBottonIndex = ($delBottonIndex + 1)} hidden{/if}" onclick="CBUtils.eraseFilterRow (this);" title="Eliminar condición"><i class="fa fa-trash-o"></i></button>
                            </div>
                        </div>
                        <input type="hidden" name="indexGrupo[]"  value="{$indexGrupo[($ig - 1)]}">
                    </li>
                        {if $indexGrupo[$ig] neq  $indexGrupo[($ig - 1)] }
                            {assign var="star" value=($ig +1)}
                            {$indexJoin = ($indexJoin +1)}
                            {break}
                        {/if}
                    {/for}
                    <!-- Lista de condiciones -->
                </ul>
            </div>
            <div class="condition-group-footer list-group-item">
                <div class="row text-center">
                    <button type="button" class="btn btn-link" onclick="CBUtils.setFilterRow (this);" title="Agregar condición"><i class="fa fa-plus"></i></button>
                </div>
            </div>
        <div class="condition-group-operator" style="margin-top:4px; margin-bottom: 0px">
            <select name="conditionGroups[]" class="form-control operator{if $smarty.section.Group.index gte $totalGroup }      hidden" disabled="disabled" {else}"{/if}>
                <option value="AND" {if $filterGroupJoin[$smarty.section.Group.index] eq "AND"}selected{/if}>&nbsp;&nbsp;&nbsp;y</option>
                <option value="OR" {if $filterGroupJoin[$smarty.section.Group.index] eq "OR"}selected{/if}>&nbsp;&nbsp;&nbsp;o</option>
            </select>
        </div>
        </div>
    {/section}
{/strip}