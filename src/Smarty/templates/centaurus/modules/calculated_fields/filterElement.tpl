{strip}
    {assign var='groupId' value='__GROUP_ID__'}
<li id="row-0" class="condition list-group-item" data-id="{$conditionId}">
    <div class="row">
        <div class="col-xs-4 variable-cell">

            <select class="form-control" id="fieldFilter" name="filterField[]" title="" onchange="CFUtils.setFilterOperators (this);">
                <option value="">-Ninguno-</option>
                {assign var="lastModule" value=''}
                {if (isset ($FIELD_LIST)) && (!empty ($FIELD_LIST))}
                    {foreach $FIELD_LIST as $field}
                        {if $field.typeofdata neq ''}
                            {if $lastModule eq ''}
                            <optgroup label="{$MODULES_LABELS[$field.module]}">
                                {$lastModule = $field.module}
                                <option value="{$field.tablename}.{$field.fieldname}" data-type="{$field.typeofdata}">{$field.label}</option>
                                {elseif $field.module eq $lastModule}
                                <option value="{$field.tablename}.{$field.fieldname}" data-type="{$field.typeofdata}">{$field.label}</option>
                                {else}
                            </optgroup>
                            <optgroup label="{$MODULES_LABELS[$field.module]}">
                                {$lastModule = $field.module}
                                <option value="{$field.tablename}.{$field.fieldname}" data-type="{$field.typeofdata}">{$field.label}</option>
                                {/if}
                        {/if}
                    {/foreach}
                {/if}
            </select>
            <span  class="help-block" style="color: red"></span>
        </div>
        <div class="col-xs-2">
            <select class="form-control" id="filterOperator" name="filterOperator[]" title="Operadores" onchange="CFUtils.setHelpToField(this)">
                <option value="">-Ninguno-</option>
            </select>
            <span  class="help-block" style="color: red"></span>
        </div>
        <div class="col-xs-4">
            <div class="input-group">
                <input name="filterValue[]" id="filterValue" class="form-control" value="" type="text" placeholder="">
                <div class="input-group-addon" onclick="CFUtils.setFilterRecord (this);" title="Comparar con su valor en el registro" alt="En el registro">
                    <i class="fa fa-cogs" aria-hidden="true"></i>
                </div>
            </div>
            <span  class="help-block" style="color: red"></span>
        </div>
        <div class="col-xs-1">
            <select name="filterJoin[]" id="filterJoin" class="form-control hidden" disabled="disabled" style="padding: 0px;">
                <option value="AND">&nbsp;&nbsp;&nbsp;y</option>
                <option value="OR">&nbsp;&nbsp;&nbsp;o</option>
            </select>
        </div>
        <div class="col-xs-1 text-right">
            <button type="button" class="btn btn-danger hidden" onclick="CFUtils.eraseFilterRow (this);" title="Eliminar condición"><i class="fa fa-trash-o"></i></button>
        </div>
    </div>
    <input type="hidden" name="indexGrupo[]"  value="{$groupId}">
</li>
{/strip}