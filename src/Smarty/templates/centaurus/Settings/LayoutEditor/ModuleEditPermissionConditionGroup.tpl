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
					<button type="button" class="btn btn-success btn-icon" onclick="PermissionUtils.addFilter (this);" title="Agregar filtro" style="display: inline-block;"><i class="fa fa-plus"></i></button>
					<button type="button" class="btn btn-default btn-icon" onclick="PermissionUtils.deleteFilterGroup (this);" title="Eliminar grupo" style="display: inline-block;"><i class="fa fa-trash-o"></i></button>
				</div>
			</div>
		</div>
		<div class="filter-group-body list-group-item">
			<ul class="list-group filters">
{if (!empty ($filters))}
	{foreach $filters as $filter}
		{include file='Settings/LayoutEditor/ModuleEditPermissionCondition.tpl' FILTER=$filter}
	{/foreach}
{/if}
			</ul>
		</div>
	</div>
	<div class="filter-group-operator">
		<select name="filtergroups[{$groupId}][operator]" class="form-control operator hidden" title="" disabled="disabled">
			<option value="AND"{if ($groupOperator == 'AND')} selected="selected"{/if}>y</option>
			<option value="OR"{if ($groupOperator == 'AND')} selected="selected"{/if}>o</option>
		</select>
	</div>
</div>
{/strip}