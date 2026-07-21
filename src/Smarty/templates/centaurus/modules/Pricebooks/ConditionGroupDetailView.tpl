{strip}
<div class="condition-group list-group">
	<div class="condition-group-header list-group-item">
		<div class="row">
			<div class="col-xs-4">Variable</div>
			<div class="col-xs-2">Operador</div>
			<div class="col-xs-4">Valor</div>
			<div class="col-xs-2"></div>
		</div>
	</div>
	<div class="condition-group-body list-group-item">
		<ul class="list-group conditions">
{if (isset ($GROUP_DATA)) && (!empty ($GROUP_DATA.conditions))}
	{foreach $GROUP_DATA.conditions as $conditionData}
		{include file="modules/Pricebooks/ConditionDetailView.tpl" CONDITION_DATA=$conditionData}
	{/foreach}
{/if}
		</ul>
	</div>
</div>
{if (isset ($GROUP_DATA)) && (!empty ($GROUP_DATA.glue))}
<div class="condition-group-glue">
	<span class="form-control" disabled="disabled">{if ($GROUP_DATA.glue == 'and')}y{elseif ($GROUP_DATA.glue == 'or')}o{/if}</span>
</div>
{/if}
{/strip}