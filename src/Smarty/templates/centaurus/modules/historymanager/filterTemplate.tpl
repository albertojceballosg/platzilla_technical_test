{strip}
    {assign var='groupId' value='__GROUP_ID__'}
<li id="row-0" class="condition list-group-item" data-id="{$conditionId}">
    <div class="row">
        <div class="col-xs-4 variable-cell">
            <select class="form-control" id="fieldFilter" name="filterField[]" title="" onchange="HistoryUtils.setFilterOperators (this);">
                <option value="">-Ninguno-</option>
                {if (isset ($FIELD_LIST)) && (!empty ($FIELD_LIST))}
                    {foreach $FIELD_LIST as $field}
                        {if $field.typeofdata neq ''}
                            <option value="{$field.fieldname}" data-type="{$field.typeofdata}" data-id="{$field.fieldid}">{$field.label}</option>
                        {/if}
                    {/foreach}
                {/if}
            </select>
            <span  class="help-block"></span>
        </div>
        <div class="col-xs-2">
            <select class="form-control" id="filterOperator" name="filterOperator[]" title="Operadores" onchange="HistoryUtils.setHelpToField(this)">
                <option value="">-Ninguno-</option>
            </select>
            <span  class="help-block"></span>
        </div>
        <div class="col-xs-4">
            <div class="input-group">
                <div class="input-group-addon is-date hide" style="border: 1px solid #ddd !important">
                    <i class="fa fa-calendar"></i>
                </div>
                <input name="filterValue[]" id="filterValue" class="form-control" value="" type="text" placeholder="">
                <div class="input-group-addon" onclick="HistoryUtils.eraseFilterValue (this);" title="Borrar" alt="Borrar"><i class="fa fa-eraser"></i>
                </div>
            </div>
            <span  class="help-block"></span>
        </div>
        <div class="col-xs-1">
            <select name="filterJoin[]" id="filterJoin" class="form-control hidden" disabled="disabled" style="padding: 0px;">
                <option value="OR">&nbsp;&nbsp;&nbsp;o</option>
            </select>
        </div>
        <div class="col-xs-1 text-right">
            <button type="button" class="btn btn-link hidden" onclick="HistoryUtils.eraseFilterRow (this);" title="Eliminar condición"><i class="fa fa-trash-o"></i></button>
        </div>
        <input type="hidden" name="fieldId[]"  value="">
    </div>
    <input type="hidden" name="indexGrupo[]"  value="{$groupId}">

</li>
{/strip}