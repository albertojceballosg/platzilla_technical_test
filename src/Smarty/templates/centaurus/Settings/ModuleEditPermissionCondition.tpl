{strip}
{if (isset ($FILTER))}
	{assign var='filterComparator' value=$FILTER->getComparator ()}
	{assign var='filterFieldName' value=$FILTER->getFieldName ()}
	{assign var='filterId' value=$FILTER->getSequence ()}
	{assign var='filterOperator' value=$FILTER->getOperator ()}
	{assign var='filterValue' value=$FILTER->getValue ()}
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
		<div class="col-xs-4">
			<select name="filtergroups[{$groupId}][filters][{$filterId}][fieldname]" class="form-control filter-field" title="Campo" onchange="PermissionUtils.setFilterField (this);">
{assign var='selectedDataType' value=null}
{if (!empty ($AVAILABLE_FIELDS))}
				<option value=""></option>
	{foreach $AVAILABLE_FIELDS as $field}
		{if (in_array ($field->getUiType (), array (5, 6, 7, 9, 14, 52, 70, 71)))}
			{assign var='dataType' value='NUMBER'}
		{else}
			{assign var='dataType' value='TEXT'}
		{/if}
		{if ($filterFieldName == $field->getName ())}
			{assign var='selectedDataType' value=$dataType}
		{/if}
				<option value="{$field->getName ()}" data-type="{$dataType}"{if ($filterFieldName == $field->getName ())} selected="selected"{/if}>{$field->getLabel ()}</option>
	{/foreach}
{/if}
			</select>
		</div>
		<div class="col-xs-3">
			<select name="filtergroups[{$groupId}][filters][{$filterId}][comparator]" class="form-control comparator" title="Operador">
				<option value=""></option>
				<option value="EQUALS"{if ($filterComparator == 'EQUALS')} selected="selected"{/if}>igual a</option>
				<option value="NOT_EQUALS"{if ($filterComparator == 'NOT_EQUALS')} selected="selected"{/if}>diferente a</option>
				<option value="STARTS_WITH"{if ($filterComparator == 'STARTS_WITH')} selected="selected"{/if} data-type="TEXT"{if ($selectedDataType != 'TEXT')} style="display: none;"{/if}>empieza con</option>
				<option value="ENDS_WITH"{if ($filterComparator == 'ENDS_WITH')} selected="selected"{/if} data-type="TEXT"{if ($selectedDataType != 'TEXT')} style="display: none;"{/if}>termina con</option>
				<option value="CONTAINS"{if ($filterComparator == 'CONTAINS')} selected="selected"{/if} data-type="TEXT"{if ($selectedDataType != 'TEXT')} style="display: none;"{/if}>contiene</option>
				<option value="DOES_NOT_CONTAIN"{if ($filterComparator == 'DOES_NOT_CONTAIN')} selected="selected"{/if} data-type="TEXT"{if ($selectedDataType != 'TEXT')} style="display: none;"{/if}>no contiene</option>
				<option value="LESS"{if ($filterComparator == 'LESS')} selected="selected"{/if} data-type="NUMBER"{if ($selectedDataType != 'NUMBER')} style="display: none;"{/if}>menor a</option>
				<option value="LESS_OR_EQUALS"{if ($filterComparator == 'LESS_OR_EQUALS')} selected="selected"{/if} data-type="NUMBER"{if ($selectedDataType != 'NUMBER')} style="display: none;"{/if}>menor o igual a</option>
				<option value="GREATER"{if ($filterComparator == 'GREATER')} selected="selected"{/if} data-type="NUMBER"{if ($selectedDataType != 'NUMBER')} style="display: none;"{/if}>mayor a</option>
				<option value="GREATER_OR_EQUALS"{if ($filterComparator == 'GREATER_OR_EQUALS')} selected="selected"{/if} data-type="NUMBER"{if ($selectedDataType != 'NUMBER')} style="display: none;"{/if}>mayor o igual a</option>
			</select>
		</div>
		<div class="col-xs-3">
			<input type="text" name="filtergroups[{$groupId}][filters][{$filterId}][value]" value="{$filterValue}" class="form-control value" placeholder="" title="Valor" />
		</div>
		<div class="col-xs-1">
			<select name="filtergroups[{$groupId}][filters][{$filterId}][operator]" class="form-control operator hidden" title="" disabled="disabled">
				<option value="AND"{if ($filterOperator == 'AND')} selected="selected"{/if}>y</option>
				<option value="OR"{if ($filterOperator == 'OR')} selected="selected"{/if}>o</option>
			</select>
		</div>
		<div class="col-xs-1 text-right">
			<button type="button" class="btn btn-default" onclick="PermissionUtils.deleteFilter (this);" title="Eliminar filtro"><i class="fa fa-trash-o"></i></button>
		</div>
	</div>
</li>
{/strip}