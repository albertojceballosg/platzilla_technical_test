{strip}
{if (isset ($GROUP))}
	{assign var='filters' value=$GROUP->getFilters ()}
	{assign var='groupId' value=$GROUP->getId ()}
	{assign var='groupOperator' value=$GROUP->getOperator ()}
{else}
	{assign var='filters' value=null}
	{assign var='groupId' value='__GROUP_ID__'}
	{assign var='groupOperator' value=null}
{/if}
<div class="filter-group-container">
	<div class="filter-group list-group" data-id="{$groupId}">
		<div class="filter-group-header list-group-item">
			<div class="row">
				<div class="col-xs-4">Campo</div>
				<div class="col-xs-3">Operador</div>
				<div class="col-xs-3">Valor</div>
				<div class="col-xs-2 text-right">
					<button type="button" class="btn btn-success btn-icon" onclick="SystemAlertUtils.addFilter (this);" title="Agregar filtro" style="display: inline-block;"><i class="fa fa-plus"></i></button>
					&nbsp;<button type="button" class="btn btn-default btn-icon" onclick="SystemAlertUtils.deleteFilterGroup (this);" title="Eliminar grupo" style="display: inline-block;"><i class="fa fa-trash-o"></i></button>
				</div>
			</div>
		</div>
		<div class="filter-group-body list-group-item">
			<ul id="alert-filter-group-{$idAlertFilter}" class="list-group filters">
{if (!empty ($filters))}
	{foreach $filters as $filter}
		{include file='modules/systemalerts/Wizard/SystemAlertFilterCondition.tpl' FILTER=$filter}
	{/foreach}
{/if}
			</ul>
		</div>
	</div>
	<div class="filter-group-operator">
		<select name="filtergroups[{$groupId}][operator]" class="form-control operator{if $groupOperator eq NULL} hidden{/if}" {if $groupOperator eq NULL}disabled="disabled"{/if} title="" >
			<option value="AND"{if ($groupOperator == 'AND')} selected="selected"{/if}>y</option>
			<option value="OR"{if ($groupOperator == 'AND')} selected="selected"{/if}>o</option>
		</select>
	</div>
</div>
{/strip}