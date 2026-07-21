<div class="row" style="margin-top: 6px">
    <div class="col-md-3">
        <select class="form-control" id="fieldFilter" name="filterField[]" title="" onchange="GraphUtils.setFilterOperators (this);">
            <option value="">-Ninguno-</option>
            {if (isset ($AVAILABLE_FIELDS)) && (!empty ($AVAILABLE_FIELDS))}
                {foreach $AVAILABLE_FIELDS as $field}
                    {if $field.typeofdata neq ''}
                        <option value="{$field.fieldname}" data-type="{$field.typeofdata}"{if ($vFilter == $field.fieldname)} selected="selected"{/if}>{$field.label}</option>
                    {/if}
                {/foreach}
            {/if}
        </select>
    </div>
    <div class="col-md-2">
        <select class="form-control" id="filterOperator" name="filterOperator[]" title="Operadores" onchange="GraphUtils.setHelpToField(this)">
            <option value="">-Ninguno-</option>
        </select>
    </div>
    <div class="col-md-4">
        <div class="input-group">
            <input name="filterValue[]" id="filterValue" class="form-control" value="" type="text" placeholder="">
            <div class="input-group-addon" onclick="GraphUtils.eraseFilterValue (this);" title="Borrar" alt="Borrar"><i class="fa fa-eraser"></i>
            </div>
        </div>
    </div>
    <div class="col-md-1 filterJoin hide">
        <select name="filterJoin[]" id="filterJoin" class="form-control" style="padding: 0px;">
            <option value="AND">&nbsp;&nbsp;&nbsp;y</option>
            <option value="OR">&nbsp;&nbsp;&nbsp;o</option>
        </select>
    </div>

    <div class="col-md-2">
        <button type="button" class="btn btn-danger hide" onclick="GraphUtils.eraseFilterRow (this);" data-row = "0"><i class="fa fa-minus" aria-hidden="true"></i></button>
        <button type="button" class="btn btn-primary" onclick="GraphUtils.setFilterRow (this);"><i class="fa fa-plus" aria-hidden="true"></i></button>
    </div>
    <div class="col-md-11 filterGroupJoin hide" style="margin: 10px 0px 4px 0px">
        <select name="filterGroupJoin[]" id="filterJoin" class="form-control">
            <option value="NOT" selected>Seleccionar</option>
            <option value="AND">&nbsp;&nbsp;&nbsp;y</option>
            <option value="OR">&nbsp;&nbsp;&nbsp;o</option>
        </select>
    </div>
</div>
<script type="text/javascript">
    var TotalRow = 0;
    var lastElementRow = '';

</script>