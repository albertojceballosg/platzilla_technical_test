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
			<select name="filtergroups[{$groupId}][filters][{$filterId}][fieldname]" class="form-control filter-field" title="Campo" onchange="BackgroundTasksUtils.setFilterField (this);">
{assign var='selectedDataType' value=null}
{if (!empty ($AVAILABLE_FIELDS))}
				<option value=""></option>
	{foreach $AVAILABLE_FIELDS as $field}
		{if (in_array ($field.uitype, array (Field::UI_TYPE_CREATED_TIME, Field::UI_TYPE_DATE, Field::UI_TYPE_DATETIME, Field::UI_TYPE_TIME)))}
			{assign var='dataType' value='DATE'}
		{elseif (in_array ($field.uitype, array (Field::UI_TYPE_CURRENCY, Field::UI_TYPE_NUMBER, Field::UI_TYPE_PERCENTAGE)))}
			{assign var='dataType' value='NUMBER'}
		{elseif (in_array ($field.uitype, array (Field::UI_TYPE_MULTI_SELECT)))}
			{assign var='dataType' value='MULTI-SELECT'}
		{elseif (in_array ($field.uitype, array (Field::UI_TYPE_MODIFIED_BY, Field::UI_TYPE_OWNER)))}
			{assign var='dataType' value='USER'}
		{else}
			{assign var='dataType' value='TEXT'}
		{/if}
		{if ($filterFieldName == $field.fieldname)}
			{assign var='selectedDataType' value=$dataType}
		{/if}
				<option value="{$field.fieldname}" data-type="{$dataType}"{if ($filterFieldName == $field.fieldname)} selected="selected"{/if}>{$field.fieldlabel}</option>
	{/foreach}
{/if}
			</select>
		</div>
		<div class="col-xs-3">
			<select name="filtergroups[{$groupId}][filters][{$filterId}][comparator]" class="form-control comparator" title="Operador" onchange="BackgroundTasksUtils.setFilterComparator (this);">
				<option value=""></option>
				<option value="EQUALS"{if ($filterComparator == 'EQUALS')} selected="selected"{/if} data-type="['DATE', 'NUMBER', 'TEXT', 'USER']"{if (!in_array ($selectedDataType, array ('DATE', 'NUMBER', 'TEXT', 'USER')))} style="display: none;"{/if}>igual a</option>
				<option value="NOT_EQUALS"{if ($filterComparator == 'NOT_EQUALS')} selected="selected"{/if} data-type="['DATE', 'NUMBER', 'TEXT', 'USER']"{if (!in_array ($selectedDataType, array ('DATE', 'NUMBER', 'TEXT', 'USER')))} style="display: none;"{/if}>diferente a</option>
				<option value="STARTS_WITH"{if ($filterComparator == 'STARTS_WITH')} selected="selected"{/if} data-type="['TEXT']"{if (!in_array ($selectedDataType, array ('TEXT')))} style="display: none;"{/if}>empieza con</option>
				<option value="ENDS_WITH"{if ($filterComparator == 'ENDS_WITH')} selected="selected"{/if} data-type="['TEXT']"{if (!in_array ($selectedDataType, array ('TEXT')))} style="display: none;"{/if}>termina con</option>
				<option value="CONTAINS"{if ($filterComparator == 'CONTAINS')} selected="selected"{/if} data-type="['MULTI-SELECT', 'TEXT']"{if (!in_array ($selectedDataType, array ('MULTI-SELECT', 'TEXT')))} style="display: none;"{/if}>contiene</option>
				<option value="DOES_NOT_CONTAIN"{if ($filterComparator == 'DOES_NOT_CONTAIN')} selected="selected"{/if} data-type="['MULTI-SELECT', 'TEXT']"{if (!in_array ($selectedDataType, array ('MULTI-SELECT', 'TEXT')))} style="display: none;"{/if}>no contiene</option>
				<option value="LESS"{if ($filterComparator == 'LESS')} selected="selected"{/if} data-type="['DATE', 'NUMBER']"{if (!in_array ($selectedDataType, array ('DATE', 'NUMBER')))} style="display: none;"{/if}>menor a</option>
				<option value="LESS_OR_EQUALS"{if ($filterComparator == 'LESS_OR_EQUALS')} selected="selected"{/if} data-type="['DATE', 'NUMBER']"{if (!in_array ($selectedDataType, array ('DATE', 'NUMBER')))} style="display: none;"{/if}>menor o igual a</option>
				<option value="GREATER"{if ($filterComparator == 'GREATER')} selected="selected"{/if} data-type="['DATE', 'NUMBER']"{if (!in_array ($selectedDataType, array ('DATE', 'NUMBER')))} style="display: none;"{/if}>mayor a</option>
				<option value="GREATER_OR_EQUALS"{if ($filterComparator == 'GREATER_OR_EQUALS')} selected="selected"{/if} data-type="['DATE', 'NUMBER']"{if (!in_array ($selectedDataType, array ('DATE', 'NUMBER')))} style="display: none;"{/if}>mayor o igual a</option>
				<option value="DAYS_BEFORE"{if ($filterComparator == 'DAYS_BEFORE')} selected="selected"{/if} data-type="['DATE']" class="days"{if (!in_array ($selectedDataType, array ('DATE')))} style="display: none;"{/if}>menor que la fecha actual al menos</option>
				<option value="DAYS_BEFORE_EXACT"{if ($filterComparator == 'DAYS_BEFORE_EXACT')} selected="selected"{/if} data-type="['DATE']" class="days"{if (!in_array ($selectedDataType, array ('DATE')))} style="display: none;"{/if}>menor que la fecha actual exactamente</option>
				<option value="DAYS_AFTER"{if ($filterComparator == 'DAYS_AFTER')} selected="selected"{/if} data-type="['DATE']" class="days"{if (!in_array ($selectedDataType, array ('DATE')))} style="display: none;"{/if}>mayor que la fecha actual al menos</option>
				<option value="DAYS_AFTER_EXACT"{if ($filterComparator == 'DAYS_AFTER_EXACT')} selected="selected"{/if} data-type="['DATE']" class="days"{if (!in_array ($selectedDataType, array ('DATE')))} style="display: none;"{/if}>mayor que la fecha actual exactamente</option>
			</select>
		</div>
		<div class="col-xs-3">
			<input type="text" name="filtergroups[{$groupId}][filters][{$filterId}][value]" value="{$filterValue}" class="form-control value date" placeholder="Usar NULL para comparar  con un valor nulo o vacío" title="Valor" data-type="['DATE', 'MULTI-SELECT', 'NUMBER', 'TEXT']"{if (!in_array ($selectedDataType, array ('DATE', 'MULTI-SELECT', 'NUMBER', 'TEXT'))) || (in_array ($filterComparator, array ('DAYS_BEFORE', 'DAYS_BEFORE_EXACT', 'DAYS_AFTER', 'DAYS_AFTER_EXACT')))} style="display: none;" disabled="disabled"{/if} />
			<input type="number" name="filtergroups[{$groupId}][filters][{$filterId}][value]" value="{$filterValue}" class="form-control value days" placeholder="Días" title="Días" data-type="['DATE']" min="0" step="1"{if (!in_array ($selectedDataType, array ('DATE'))) || (!in_array ($filterComparator, array ('DAYS_BEFORE', 'DAYS_BEFORE_EXACT', 'DAYS_AFTER', 'DAYS_AFTER_EXACT')))} style="display: none;" disabled="disabled"{/if} />
			<select name="filtergroups[{$groupId}][filters][{$filterId}][value]" class="form-control value" title="Valor" data-type="['USER']"{if (!in_array ($selectedDataType, array ('USER')))} style="display: none;" disabled="disabled"{/if}>
{if (!empty ($AVAILABLE_USERS))}
				<option value=""></option>
	{foreach $AVAILABLE_USERS as $user}
				<option value="{$user->getId ()}"{if ($user->getId () == $filterValue)} selected="selected"{/if}>{trim("{$user->getFirstName ()} {$user->getLastName ()}")}</option>
	{/foreach}
{/if}
			</select>
		</div>
		<div class="col-xs-1">
			<select name="filtergroups[{$groupId}][filters][{$filterId}][operator]" class="form-control operator" title=""{if ($IS_LAST_FILTER)} style="display: none;" disabled="disabled"{/if}>
				<option value="AND"{if ($filterOperator == 'AND')} selected="selected"{/if}>y</option>
				<option value="OR"{if ($filterOperator == 'OR')} selected="selected"{/if}>o</option>
			</select>
		</div>
		<div class="col-xs-1 text-right">
			<button type="button" class="btn btn-default" onclick="BackgroundTasksUtils.deleteFilter (this);" title="Eliminar filtro"><i class="fa fa-trash-o"></i></button>
		</div>
	</div>
</li>
{/strip}