{strip}
{if (isset ($GROUP))}
	{assign var='filters' value=$GROUP->getFilters ()}
	{assign var='groupId' value=$GROUP->getSequence ()}
	{assign var='groupColor' value=$GROUP->getColor ()}
{else}
	{assign var='filters' value=null}
	{assign var='groupId' value='__GROUP_ID__'}
	{assign var='groupColor' value=null}
{/if}
<div class="filter-group-container">
	<div class="filter-group list-group" data-id="{$groupId}" style="margin-bottom: 15px;">
		<div class="filter-group-header list-group-item">
			<div class="row">
				<div class="col-xs-1"><input type="color" name="colorfiltergroups[{$groupId}][color]" value="{$groupColor}" class="form-control color" title="" style="padding: 0;" /></div>
				<div class="col-xs-3">Campo</div>
				<div class="col-xs-3">Operador</div>
				<div class="col-xs-3">Valor</div>
				<div class="col-xs-2 text-right">
					<button type="button" class="btn btn-info" onclick="CustomViewUtils.addColorFilter (this);" title="Agregar filtro" style="display: inline-block;"><i class="fa fa-plus"></i></button>
					<button type="button" class="btn btn-default" onclick="CustomViewUtils.deleteFilterGroup (this);" title="Eliminar grupo" style="display: inline-block; margin-left: 5px;"><i class="fa fa-trash-o"></i></button>
				</div>
			</div>
		</div>
		<div class="filter-group-body list-group-item">
			<ul class="list-group filters">
{if (!empty ($filters))}
	{foreach $filters as $filter}
		{include file='CustomViewColorFilter.tpl' FILTER=$filter}
	{/foreach}
{/if}
			</ul>
		</div>
	</div>
</div>
{/strip}