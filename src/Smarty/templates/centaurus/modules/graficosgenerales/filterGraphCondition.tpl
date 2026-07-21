{strip}
    {assign var='groupId' value='__GROUP_ID__'}
<li id="row-0" class="condition list-group-item" data-id="{$conditionId}">
    <div class="row">
        <div id="gr-td-filterField[]" class="col-xs-4 variable-cell">
            <select class="form-control" id="fieldFilter" name="filterField[]" title="El campo" onchange="GraphUtils.setFilterOperators (this);;">
                <option value="">-Ninguno-</option>
            </select>
            <span id="gr-filterField[]" class="help-block" ></span>
        </div>
        <div id="gr-td-filterOperator[]" class="col-xs-2">
            <select class="form-control" id="filterOperator" name="filterOperator[]" title="Operador" onchange="GraphUtils.setHelpToField(this)">
                <option value="">-Ninguno-</option>
            </select>
            <span id="gr-filterOperator[]" class="help-block" ></span>
        </div>
        <div class="col-xs-4">
            <div id="gr-td-filterValue[]"class="input-group">
                <input name="filterValue[]" id="filterValue" class="form-control" value="" type="text" placeholder="" title="el valor a filtar">
                <div class="input-group-addon" onclick="GraphUtils.eraseFilterValue (this);" title="Borrar" alt="Borrar"><i class="fa fa-eraser"></i></div>
            </div>
            <span id="gr-filterValue[]" class="help-block" style="color: red" ></span>
        </div>
        <div id="gr-td-filterJoin[]" class="col-xs-1">
            <select name="filterJoin[]" id="filterJoin" class="form-control hidden" disabled="disabled" style="padding: 0px;">
                <option value="AND">&nbsp;&nbsp;&nbsp;y</option>
                <option value="OR">&nbsp;&nbsp;&nbsp;o</option>
            </select>
            <span id="gr-filterJoin[]" class="help-block"></span>
        </div>
        <div class="col-xs-1 text-right">
            <button type="button" class="btn btn-link hidden" onclick="GraphUtils.eraseFilterRow (this);" title="Eliminar condición"><i class="fa fa-trash-o"></i></button>
        </div>
    </div>
    <input type="hidden" name="indexGrupo[]"  value="{$groupId}">
</li>
{/strip}