{strip}
<li class="condition list-group-item">
	<div class="row">
		<div class="col-xs-4">
			<span class="form-control" disabled="disabled">{if ($CONDITION_DATA.variabletype == 'SERVICE_FIELD')}Campo: {elseif ($CONDITION_DATA.variabletype == 'SYSTEM_VARIABLE')}Variable sistema: {/if}{$CONDITION_DATA.variablelabel}</span>
		</div>
		<div class="col-xs-2">
			<span class="form-control" disabled="disabled">
{if ($CONDITION_DATA.operator == '=')}igual a
{elseif ($CONDITION_DATA.operator == '<')}menor a
{elseif ($CONDITION_DATA.operator == '<=')}menor o igual a
{elseif ($CONDITION_DATA.operator == '>')}mayor a
{elseif ($CONDITION_DATA.operator == '>=')}mayor o igual a
{elseif ($CONDITION_DATA.operator == '!=')}diferente a
{elseif ($CONDITION_DATA.operator == 'like')}sigue el patrón
{/if}
			</span>
		</div>
		<div class="col-xs-4">
			<span class="form-control" disabled="disabled">{$CONDITION_DATA.value}</span>
		</div>
		<div class="col-xs-2">
{if (!empty ($CONDITION_DATA.glue))}
			<span class="form-control" disabled="disabled">{if ($CONDITION_DATA.glue == 'and')}y{elseif ($CONDITION_DATA.glue == 'or')}o{/if}</span>
{/if}
		</div>
	</div>
</li>
{/strip}