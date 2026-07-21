{strip}
<li id="row-0" class="condition list-group-item" data-id="{if isset($conditionId)}{$conditionId}{else}0{/if}">
    <div class="row">
        <div class="col-xs-4 variable-cell">

            <select class="form-control" id="fieldFilter" name="filterField[]" title="" onchange="NotificationUtils.setFilterOperators (this);">
                <option value="">-Ninguno-</option>
            </select>
            <span class="help-block field-description" style="color: #666; font-size: 0.9em; margin-top: 5px; display: none;"></span>
            <span  class="help-block" style="color: red"></span>
        </div>
        <div class="col-xs-2">
            <select class="form-control" id="filterOperator" name="filterOperator[]" title="Operadores" onchange="NotificationUtils.setHelpToField (this)">
                <option value="">-Ninguno-</option>
</li>
{/strip}
</select>
<span  class="help-block" style="color: red"></span>
</div>
<div class="col-xs-4">
    <div class="input-group">
        <div class="input-group-addon is-date hide" style="border: 1px solid #ddd !important">
            <i class="fa fa-calendar"></i>
        </div>
        <input name="filterValue[]" id="filterValue" class="form-control" value="" type="text" placeholder="">
        <div class="input-group-addon" onclick="NotificationUtils.eraseFilterValue (this);" title="Borrar" alt="Borrar"><i class="fa fa-eraser"></i>
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
    <button type="button" class="btn btn-link hidden" onclick="NotificationUtils.eraseFilterRow (this);" title="Eliminar condición"><i class="fa fa-trash-o"></i></button>
</div>
</div>
<input type="hidden" name="indexGrupo[]"  value="__GROUP_ID__">