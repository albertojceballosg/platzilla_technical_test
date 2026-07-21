{strip}
{if (isset ($FILTER))}
	{assign var='filterComparator' value=$FILTER->getComparator ()}
	{assign var='filterFieldName' value=$FILTER->getFieldName ()}
	{assign var='filterId' value=$FILTER->getSequence ()}
	{assign var='filterOperator' value=$FILTER->getOperator ()}
	{assign var='filterValue' value=$FILTER->getValue ()}
    {assign var='filterEndDate' value=$FILTER->getEndDate ()}
    {assign var='filterStartDate' value=$FILTER->getStartDate ()}
	{assign var='groupId' value=$FILTER->getGroupId ()}
{else}
	{assign var='filterComparator' value=null}
	{assign var='filterFieldName' value=null}
	{assign var='filterId' value='__FILTER_ID__'}
	{assign var='filterOperator' value=null}
	{assign var='filterValue' value=null}
	{assign var='groupId' value='__GROUP_ID__'}
{/if}
<li class="filter list-group-item" data-id="{$filterId}">
	<div class="row">
		<div class="col-xs-3">
			<select name="colorfiltergroups[{$groupId}][filters][{$filterId}][column]" class="form-control filter-field" title="Campo" onchange="CustomViewUtils.setFilterField (this);">
{assign var='selectedDataType' value=null}
{if (!empty ($AVAILABLE_COLUMNS))}
				<option value=""></option>
	{foreach $AVAILABLE_COLUMNS as $columnName => $columnLabel}
		{assign var='dummy' value=explode(':', $columnName)}
		{if (in_array ($dummy[4], array ('D', 'DT', 'T')))}
			{assign var='dataType' value='DATE'}
        {elseif (in_array ($dummy[4], array ('I', 'N', 'NN')))}
            {assign var='dataType' value='NUMBER'}
		{else}
			{assign var='dataType' value='TEXT'}
		{/if}
		{if ($filterFieldName == $dummy[2])}
			{assign var='selectedDataType' value=$dataType}
		{/if}
				<option value="{$columnName}" data-type="{$dataType}"{if ($filterFieldName == $dummy[2])} selected="selected"{/if}>{$columnLabel}</option>
	{/foreach}
{/if}
			</select>
		</div>
		<div class="col-xs-3">
			<select name="colorfiltergroups[{$groupId}][filters][{$filterId}][comparator]" class="form-control comparator" data-control="{$groupId}{$filterId}"   title="Operador" onchange="CustomViewUtils.setColorPeriod (this);">
				<option value=""></option>
				<option value="e"{if ($filterComparator == 'e')} selected="selected"{/if}>igual a</option>
				<option value="n"{if ($filterComparator == 'n')} selected="selected"{/if}>diferente a</option>
				<option value="s"{if ($filterComparator == 's')} selected="selected"{/if} data-type="TEXT"{if ($selectedDataType != 'TEXT')} style="display: none;"{/if}>empieza con</option>
				<option value="ew"{if ($filterComparator == 'ew')} selected="selected"{/if} data-type="TEXT"{if ($selectedDataType != 'TEXT')} style="display: none;"{/if}>termina con</option>
				<option value="c"{if ($filterComparator == 'c')} selected="selected"{/if} data-type="TEXT"{if ($selectedDataType != 'TEXT')} style="display: none;"{/if}>contiene</option>
				<option value="k"{if ($filterComparator == 'k')} selected="selected"{/if} data-type="TEXT"{if ($selectedDataType != 'TEXT')} style="display: none;"{/if}>no contiene</option>
				<option value="l"{if ($filterComparator == 'l')} selected="selected"{/if} data-type="NUMBER"{if ($selectedDataType != 'NUMBER')} style="display: none;"{/if}>menor a</option>
				<option value="m"{if ($filterComparator == 'm')} selected="selected"{/if} data-type="NUMBER"{if ($selectedDataType != 'NUMBER')} style="display: none;"{/if}>menor o igual a</option>
				<option value="g"{if ($filterComparator == 'g')} selected="selected"{/if} data-type="NUMBER"{if ($selectedDataType != 'NUMBER')} style="display: none;"{/if}>mayor a</option>
				<option value="h"{if ($filterComparator == 'h')} selected="selected"{/if} data-type="NUMBER"{if ($selectedDataType != 'NUMBER')} style="display: none;"{/if}>mayor o igual a</option>
				<option value="a"{if ($filterComparator == 'a')} selected="selected"{/if} data-type="NUMBER"{if ($selectedDataType != 'NUMBER')} style="display: none;"{/if}>después de</option>
				<option value="b"{if ($filterComparator == 'b')} selected="selected"{/if} data-type="NUMBER"{if ($selectedDataType != 'NUMBER')} style="display: none;"{/if}>antes de</option>
				<option value="bw"{if ($filterComparator == 'bw')} selected="selected"{/if} data-type="NUMBER"{if ($selectedDataType != 'NUMBER')} style="display: none;"{/if}>entre</option>
                {foreach $AVAILABLE_PERIODS as $periodName => $periodLabel}
					<option value="{$periodName}"{if ($filterComparator == $periodName)} selected="selected" {assign var='dataType' value='TEXT'}{/if} data-type="DATE"{if ($selectedDataType != 'DATE')} style="display: none;"{/if}
					>{$periodLabel}</option>
                {/foreach}
			</select>
            {if ($filterComparator == 'custom')}
            {assign var='viewColorFilterPeriod' value='custom'}
			{else}
            {assign var='viewColorFilterPeriod' value=NULL}
			{/if}
		</div>
		<div class="col-xs-4">
			<div id="color-filter-std{$groupId}{$filterId}" {if ($viewColorFilterPeriod == 'custom')} style="display: none;"{/if}>
			<input type="text" name="colorfiltergroups[{$groupId}][filters][{$filterId}][value]" value="{$filterValue}" class="form-control value" placeholder="" title="Valor" />
			</div>
			<div  class="row" id="color-filter-period{$groupId}{$filterId}" {if ($viewColorFilterPeriod != 'custom')} style="display: none;"{/if}>
				<div class="form-group col-md-6">
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
						<input type="text"  name="colorfiltergroups[{$groupId}][filters][{$filterId}][startdate]" value="{if (isset ($filterStartDate)) && (!empty ($filterStartDate))}{$filterStartDate->format ('Y-m-d')}{/if}" class="form-control color-filter-date" placeholder="Inicio"/>
					</div>
				</div>
				<div class="form-group col-md-6">
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
						<input type="text" name="colorfiltergroups[{$groupId}][filters][{$filterId}][enddate]" value="{if (isset ($filterEndDate)) && (!empty ($filterEndDate))}{$filterEndDate->format ('Y-m-d')}{/if}" class="form-control color-filter-date" placeholder="Fin" />
					</div>
				</div>
			</div>
		</div>
		<div class="col-xs-1">
			<select name="colorfiltergroups[{$groupId}][filters][{$filterId}][operator]" class="form-control operator{if (empty ($filterOperator))} hidden{/if}" title=""{if (empty ($filterOperator))} disabled="disabled"{/if}>
				<option value="and"{if ($filterOperator == 'and')} selected="selected"{/if}>y</option>
				<option value="or"{if ($filterOperator == 'or')} selected="selected"{/if}>o</option>
			</select>
		</div>
		<div class="col-xs-1 text-right">
			<button type="button" class="btn btn-default" onclick="CustomViewUtils.deleteFilter (this);" title="Eliminar filtro"><i class="fa fa-trash-o"></i></button>
		</div>
	</div>
</li>
{/strip}