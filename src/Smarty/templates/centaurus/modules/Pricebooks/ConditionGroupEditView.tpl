{strip}
{if (isset ($GROUP))}
	{assign var='groupId' value=$GROUP->getId ()}
	{assign var='groupOperator' value=$GROUP->getOperator ()}
	{assign var='pricebookId' value=$GROUP->getPricebookId ()}
	{assign var='groupConditions' value=$GROUP->getConditions ()}
{else}
	{assign var='groupId' value='__GROUP_ID__'}
	{assign var='groupOperator' value=null}
	{assign var='pricebookId' value=null}
	{assign var='groupConditions' value=null}
{/if}
<div class="condition-group list-group" data-id="{$groupId}">
	<div class="condition-group-header list-group-item">
		<div class="row">
			<div class="col-xs-4">Variable</div>
			<div class="col-xs-2">Operador</div>
			<div class="col-xs-4">Valor</div>
			<div class="col-xs-1"></div>
			<div class="col-xs-1 text-right">
				<button type="button" class="btn btn-link" onclick="PricebookUtils.deleteConditionGroup (this);" title="Eliminar grupo de condiciones"><i class="fa fa-trash-o"></i></button>
			</div>
		</div>
	</div>
	<div class="condition-group-body list-group-item">
		<ul class="list-group conditions">
{if (!empty ($groupConditions))}
	{foreach $groupConditions as $condition}
		{include file="modules/Pricebooks/ConditionEditView.tpl" CONDITION=$condition}
	{/foreach}
{/if}
		</ul>
	</div>
	<div class="condition-group-footer list-group-item">
		<div class="row text-center">
			<button type="button" class="btn btn-link" onclick="PricebookUtils.addCondition (this);" title="Agregar condición"><i class="fa fa-plus"></i></button>
		</div>
	</div>
</div>
<div class="condition-group-operator">
	<select name="conditiongroups[{$groupId}][operator]" class="form-control operator{if (empty ($groupOperator))} hidden{/if}" title=""{if (empty ($groupOperator))} disabled="disabled"{/if}>
		<option value="{PricebookConditionGroup::OPERATOR_AND}"{if ($groupOperator == PricebookConditionGroup::OPERATOR_AND)} selected="selected"{/if}>y</option>
		<option value="{PricebookConditionGroup::OPERATOR_OR}"{if ($groupOperator == PricebookConditionGroup::OPERATOR_OR)} selected="selected"{/if}>o</option>
	</select>
</div>
{/strip}