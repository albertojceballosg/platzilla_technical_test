{assign var="totalField" value=$GRAPH_FILTER['filterField']|@count}
{assign var="selectedTypeOfData" value=""}
{foreach from=$GRAPH_FILTER['filterField'] key=kIndex item=vFilter}
<div id="graphc-row-{$kIndex}"  class="row" style="margin-top: 6px">
    <div class="col-md-3">
        <select class="form-control" id="fieldFilter" name="filterField[]" title="" onchange="GraphUtils.setFilterOperators (this);">
            <option value="">-Ninguno-</option>
            {if (isset ($AVAILABLE_FIELDS)) && (!empty ($AVAILABLE_FIELDS))}
                {foreach $AVAILABLE_FIELDS as $field}
                    {if $field.typeofdata neq ''}
                        <option value="{$field.fieldname}" data-type="{$field.typeofdata}"{if ($vFilter == $field.fieldname)} {$selectedTypeOfData = $field.typeofdata}  selected="selected"{/if}>{$field.label}</option>
                    {/if}
                {/foreach}
            {/if}
        </select>
    </div>
    <div class="col-md-2">
        <select class="form-control" id="filterOperator" name="filterOperator[]" title="Operadores" onchange="GraphUtils.setHelpToField(this)">
            <option value="">-Ninguno-</option>
            {foreach from=$FILTER_TYPE[$selectedTypeOfData] key=k item=v}
            <option value="{$k}" {if $GRAPH_FILTER['filterOperator'][$kIndex] eq $k} selected {/if}>{$v}</option>
            {/foreach}
        </select>
    </div>
    <div class="col-md-4">
        <div class="input-group">
            <input name="filterValue[]" id="filterValue" class="form-control" value="{$GRAPH_FILTER['filterValue'][$kIndex]}" type="text" placeholder="">
            <div class="input-group-addon" onclick="GraphUtils.eraseFilterValue (this);" title="Borrar" alt="Borrar"><i class="fa fa-eraser"></i>
            </div>
        </div>
    </div>
    <div class="col-md-1 filterJoin {if $kIndex gt ($totalField -1)} hide {/if}">
        <select name="filterJoin[]" id="filterJoin" class="form-control" style="padding: 0px;">
            <option value="AND" {if $GRAPH_FILTER['filterJoin' ][$kIndex] eq 'AND'} selected {/if}>&nbsp;&nbsp;&nbsp;y</option>
            <option value="OR" {if $GRAPH_FILTER['filterJoin' ][$kIndex] eq 'OR'} selected {/if}>&nbsp;&nbsp;&nbsp;o</option>
        </select>
    </div>

    <div class="col-md-2">
        <button type="button" class="btn btn-danger {if $kIndex eq 0} hide {/if} " onclick="GraphUtils.eraseFilterRow (this);" data-row = "{$kIndex}"><i class="fa fa-minus" aria-hidden="true"></i></button>
        <button type="button" class="btn btn-primary {if $kIndex lt ($totalField -1)} hide {/if}" onclick="GraphUtils.setFilterRow (this);"><i class="fa fa-plus" aria-hidden="true"></i></button>
    </div>
    <div class="col-md-11 filterGroupJoin {if $GRAPH_FILTER['filterGroupJoin' ][$kIndex] eq 'NOT'} hide {/if}" style="margin: 10px 0px 4px 0px">
        <select name="filterGroupJoin[]" id="filterJoin" class="form-control">
            <option value="NOT" {if $GRAPH_FILTER['filterGroupJoin' ][$kIndex] eq 'NOT'} selected {/if}>Seleccionar</option>
            <option value="AND" {if $GRAPH_FILTER['filterGroupJoin' ][$kIndex] eq 'AND'} selected {/if}>&nbsp;&nbsp;&nbsp;y</option>
            <option value="OR"{if $GRAPH_FILTER['filterGroupJoin' ][$kIndex] eq 'OR'} selected {/if}>&nbsp;&nbsp;&nbsp;o</option>
        </select>
    </div>
</div>
{/foreach}
<script type="text/javascript">
    var TotalRow = {$totalField};
    var lastElementRow = jQuery('#graphc-row-{($totalField - 1)}');

</script>