{strip}
{if (isset ($MODULE))}
	{assign var='availableFields' value=$MODULE->getFields ()}
	{$dummy = usort ($availableFields, $SORT_BY_LABEL_FUNCTION)}
{else}
	{assign var='availableFields' value=null}
{/if}
{if (isset ($FILTER))}
	{assign var='filterComparator' value=$FILTER->getComparator ()}
	{assign var='filterFieldName' value=$FILTER->getFieldName ()}
	{assign var='filterId' value=$FILTER->getSequence ()}
	{assign var='filterOperator' value=$FILTER->getOperator ()}
	{assign var='filterValue' value=$FILTER->getValue ()}
	{assign var='groupId' value=$FILTER->getGroupId ()}
	{assign var='optionValue' value=$OPTION_VALUES[$FILTER->getFieldName ()]}
	{*$OPTION_VALUES|var_dump*}
{else}
	{assign var='filterComparator' value=null}
	{assign var='filterFieldName' value=null}
	{assign var='filterId' value='__FILTER_ID__'}
	{assign var='filterOperator' value=null}
	{assign var='filterValue' value=null}
	{assign var='groupId' value='__GROUP_ID__'}
    {assign var='optionValue' value=null}
{/if}
    {assign var='selectedDate' value=null}
<li class="filter list-group-item" data-id="{$filterId}">
	<div class="row">
		<div class="col-xs-4" id="div-fieldname-{$groupId}">
			<select name="filtergroups[{$groupId}][filters][{$filterId}][fieldname]"
					class="form-control filter-field" title="Campo"
					onchange="SystemAlertUtils.selectElementField(this, '{$idAlertFilter}');">
    {assign var='selectedDataType' value=null}
{if (!empty ($availableFields))}
				<option value=""></option>
	{foreach $availableFields as $field}
		{if (in_array($field->getUiType(), $NO_AVAIABLE_UITYPE)) ||
        (in_array($field->getName (), $NO_AVAIABLE_FIELDNAME))
		}{continue}{/if}

		{if (in_array ($field->getUiType (), array (5, 6, 7, 9, 14, 52, 70, 71)))}
			{assign var='dataType' value='NUMBER'}
        	{if (in_array ($field->getUiType (), array (5, 6)))}
            	{assign var='selectedDate' value='DATE'}
				{/if}
		{else}
			{assign var='dataType' value='TEXT'}
            {if (in_array ($field->getUiType (), array (53)))}
                {assign var='selectedDate' value='DATE'}
            {/if}
		{/if}
		{if ($filterFieldName == $field->getName ())}
			{assign var='selectedDataType' value=$dataType}
		{/if}
				<option value="{$field->getName ()}"
						data-type="{$dataType}"
						data-field-type="{$field->getDataType ()}"
						data-uitype="{$field->getUiType ()}"
						{if ($filterFieldName == $field->getName ())} selected="selected"{/if}>
					{$field->getLabel ()}
				</option>
	{/foreach}
{/if}
			</select>
			<span id="sp-fieldname-{$groupId}" class="help-block" style="color: red"></span>
		</div>
		<div class="col-xs-3" id="div-operator-{$groupId}">
			<select name="filtergroups[{$groupId}][filters][{$filterId}][comparator]" class="form-control comparator" title="Operador">
				<option value=""></option>
				{if $selectedDate neq NULL}
					<option value="EQUALS"{if ($filterComparator == 'EQUALS')} selected="selected"{/if}>igual a</option>
					<option value="NOT_EQUALS"{if ($filterComparator == 'NOT_EQUALS')} selected="selected"{/if} style="display: none;">diferente a</option>
					<option value="STARTS_WITH"{if ($filterComparator == 'STARTS_WITH')} selected="selected"{/if} data-type="TEXT" style="display: none;">empieza con</option>
					<option value="ENDS_WITH"{if ($filterComparator == 'ENDS_WITH')} selected="selected"{/if} data-type="TEXT" style="display: none;">termina con</option>
					<option value="CONTAINS"{if ($filterComparator == 'CONTAINS')} selected="selected"{/if} data-type="TEXT" style="display: none;">contiene</option>
					<option value="DOES_NOT_CONTAIN"{if ($filterComparator == 'DOES_NOT_CONTAIN')} selected="selected"{/if} data-type="TEXT" style="display: none;">no contiene</option>
					<option value="LESS"{if ($filterComparator == 'LESS')} selected="selected"{/if} data-type="NUMBER" >menor a</option>
					<option value="LESS_OR_EQUALS"{if ($filterComparator == 'LESS_OR_EQUALS')} selected="selected"{/if} data-type="NUMBER" style="display: none;">menor o igual a</option>
					<option value="GREATER"{if ($filterComparator == 'GREATER')} selected="selected"{/if} data-type="NUMBER" >mayor a</option>
					<option value="GREATER_OR_EQUALS"{if ($filterComparator == 'GREATER_OR_EQUALS')} selected="selected"{/if} data-type="NUMBER" style="display: none;">mayor o igual a</option>
                {else}
					<option value="EQUALS"{if ($filterComparator == 'EQUALS')} selected="selected"{/if}>igual a</option>
					<option value="NOT_EQUALS"{if ($filterComparator == 'NOT_EQUALS')} selected="selected"{/if} >diferente a</option>
					<option value="STARTS_WITH"{if ($filterComparator == 'STARTS_WITH')} selected="selected"{/if} data-type="TEXT"{if ($selectedDataType != 'TEXT')} style="display: none;"{/if}>empieza con</option>
					<option value="ENDS_WITH"{if ($filterComparator == 'ENDS_WITH')} selected="selected"{/if} data-type="TEXT"{if ($selectedDataType != 'TEXT')} style="display: none;"{/if}>termina con</option>
					<option value="CONTAINS"{if ($filterComparator == 'CONTAINS')} selected="selected"{/if} data-type="TEXT"{if ($selectedDataType != 'TEXT')} style="display: none;"{/if}>contiene</option>
					<option value="DOES_NOT_CONTAIN"{if ($filterComparator == 'DOES_NOT_CONTAIN')} selected="selected"{/if} data-type="TEXT"{if ($selectedDataType != 'TEXT')} style="display: none;"{/if}>no contiene</option>
					<option value="LESS"{if ($filterComparator == 'LESS')} selected="selected"{/if} data-type="NUMBER"{if ($selectedDataType != 'NUMBER')} style="display: none;"{/if}>menor a</option>
					<option value="LESS_OR_EQUALS"{if ($filterComparator == 'LESS_OR_EQUALS')} selected="selected"{/if} data-type="NUMBER"{if ($selectedDataType != 'NUMBER')} style="display: none;"{/if}>menor o igual a</option>
					<option value="GREATER"{if ($filterComparator == 'GREATER')} selected="selected"{/if} data-type="NUMBER"{if ($selectedDataType != 'NUMBER')} style="display: none;"{/if}>mayor a</option>
					<option value="GREATER_OR_EQUALS"{if ($filterComparator == 'GREATER_OR_EQUALS')} selected="selected"{/if} data-type="NUMBER"{if ($selectedDataType != 'NUMBER')} style="display: none;"{/if}>mayor o igual a</option>
                {/if}
			</select>
			<span id="sp-operador-{$groupId}" class="help-block" style="color: red"></span>
		</div>
		<div class="col-xs-3" id="div-value-{$groupId}">
			<input type="text" name="filtergroups[{$groupId}][filters][{$filterId}][value]" value="{$filterValue}" class="form-control value {if $optionValue neq NULL} hide{/if}" placeholder="" {if $optionValue neq NULL}disabled{/if} title="Valor" />
			<select name="filtergroups[{$groupId}][filters][{$filterId}][value]" class="form-control value {if $optionValue eq NULL} hide{/if}" title="Valor" {if $optionValue eq NULL}disabled{/if}>
                {$optionValue}
			</select>
			<span id="sp-input-value-{$groupId}" class="help-block" style="color: red"></span>
		</div>
		<div class="col-xs-1">
			<select name="filtergroups[{$groupId}][filters][{$filterId}][operator]" class="form-control operator{if $filterOperator eq NULL} hidden{/if}" {if $filterOperator eq NULL}disabled="disabled"{/if}title="">
				<option value="AND"{if ($filterOperator == 'AND')} selected="selected"{/if}>y</option>
				<option value="OR"{if ($filterOperator == 'OR')} selected="selected"{/if}>o</option>
			</select>
		</div>
		<div class="col-xs-1 text-right">
			<button type="button" class="btn btn-default btn-icon" onclick="SystemAlertUtils.deleteFilter (this);" title="Eliminar filtro"><i class="fa fa-trash-o"></i></button>
		</div>
	</div>
</li>
{/strip}