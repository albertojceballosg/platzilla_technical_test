{strip}
{if (isset ($CONDITION))}
	{assign var='conditionId' value=$CONDITION->getId ()}
	{assign var='conditionComparator' value=$CONDITION->getComparator ()}
	{assign var='conditionOperator' value=$CONDITION->getOperator ()}
	{assign var='conditionVariableName' value=$CONDITION->getVariableName ()}
	{assign var='conditionVariableType' value=$CONDITION->getVariableType ()}
	{assign var='conditionValue' value=$CONDITION->getValue ()}
	{assign var='groupId' value=$CONDITION->getGroupId ()}
	{assign var='pricebookId' value=$CONDITION->getPricebookId ()}
{else}
	{assign var='conditionId' value='__CONDITION_ID__'}
	{assign var='conditionComparator' value=null}
	{assign var='conditionOperator' value=null}
	{assign var='conditionVariableName' value=null}
	{assign var='conditionVariableType' value=null}
	{assign var='conditionValue' value=null}
	{assign var='groupId' value='__GROUP_ID__'}
	{assign var='pricebookId' value=null}
{/if}
<li class="condition list-group-item" data-id="{$conditionId}">
	<div class="row">
		<div class="col-xs-4 variable-cell">
			<input type="hidden" name="conditiongroups[{$groupId}][conditions][{$conditionId}][variabletype]" class="variable-type" value="{$conditionVariableType}" />
			<select name="conditiongroups[{$groupId}][conditions][{$conditionId}][variablename]" class="form-control variable-name" title="Variable" onchange="PricebookUtils.setVariableType (this);">
				<option value=""></option>
{if (!empty ($CUSTOMER_FIELDS))}
				<optgroup label="Campo del módulo clientes" data-type="{PricebookCondition::VARIABLE_TYPE_CUSTOMER_FIELD}">
	{foreach $CUSTOMER_FIELDS as $fieldName => $fieldLabel}
					<option value="{$fieldName}"{if ($conditionVariableType == PricebookCondition::VARIABLE_TYPE_CUSTOMER_FIELD) && ($conditionVariableName == $fieldName)} selected="selected"{/if}>{$fieldLabel}</option>
	{/foreach}
				</optgroup>
{/if}
{if (!empty ($SYSTEM_VARIABLES))}
				<optgroup label="Variables de sistema" data-type="{PricebookCondition::VARIABLE_TYPE_SYSTEM_VARIABLE}">
	{foreach $SYSTEM_VARIABLES as $variableName => $variableLabel}
					<option value="{$variableName}"{if ($conditionVariableType == PricebookCondition::VARIABLE_TYPE_SYSTEM_VARIABLE) && ($conditionVariableName == $variableName)} selected="selected"{/if}>{$variableLabel}</option>
	{/foreach}
				</optgroup>
{/if}
			</select>
		</div>
		<div class="col-xs-2">
			<select name="conditiongroups[{$groupId}][conditions][{$conditionId}][comparator]" class="form-control comparator" title="Operador">
				<option value=""></option>
				<option value="{PricebookCondition::COMPARATOR_EQUALS}"{if ($conditionComparator == PricebookCondition::COMPARATOR_EQUALS)} selected="selected"{/if}>igual a</option>
				<option value="{PricebookCondition::COMPARATOR_LESS}"{if ($conditionComparator == PricebookCondition::COMPARATOR_LESS)} selected="selected"{/if}>menor a</option>
				<option value="{PricebookCondition::COMPARATOR_LESS_OR_EQUALS}"{if ($conditionComparator == PricebookCondition::COMPARATOR_LESS_OR_EQUALS)} selected="selected"{/if}>menor o igual a</option>
				<option value="{PricebookCondition::COMPARATOR_GREATER}"{if ($conditionComparator == PricebookCondition::COMPARATOR_GREATER)} selected="selected"{/if}>mayor a</option>
				<option value="{PricebookCondition::COMPARATOR_GREATER_OR_EQUALS}"{if ($conditionComparator == PricebookCondition::COMPARATOR_GREATER_OR_EQUALS)} selected="selected"{/if}>mayor o igual a</option>
				<option value="{PricebookCondition::COMPARATOR_NOT_EQUALS}"{if ($conditionComparator == PricebookCondition::COMPARATOR_NOT_EQUALS)} selected="selected"{/if}>diferente a</option>
				<option value="{PricebookCondition::COMPARATOR_CONTAINS}"{if ($conditionComparator == PricebookCondition::COMPARATOR_CONTAINS)} selected="selected"{/if}>contiene</option>
				<option value="{PricebookCondition::COMPARATOR_DOES_NOT_CONTAIN}"{if ($conditionComparator == PricebookCondition::COMPARATOR_DOES_NOT_CONTAIN)} selected="selected"{/if}>no contiene</option>
			</select>
		</div>
		<div class="col-xs-4">
			<input type="text" name="conditiongroups[{$groupId}][conditions][{$conditionId}][value]" value="{$conditionValue}" class="form-control value" placeholder="" />
		</div>
		<div class="col-xs-1">
			<select name="conditiongroups[{$groupId}][conditions][{$conditionId}][operator]" class="form-control operator{if (empty ($conditionOperator))} hidden{/if}" title=""{if (empty ($conditionOperator))} disabled="disabled"{/if}>
				<option value="{PricebookCondition::OPERATOR_AND}"{if ($conditionOperator == PricebookCondition::OPERATOR_AND)} selected="selected"{/if}>y</option>
				<option value="{PricebookCondition::OPERATOR_OR}"{if ($conditionOperator == PricebookCondition::OPERATOR_OR)} selected="selected"{/if}>o</option>
			</select>
		</div>
		<div class="col-xs-1 text-right">
			<button type="button" class="btn btn-link" onclick="PricebookUtils.deleteCondition (this);" title="Eliminar condición"><i class="fa fa-trash-o"></i></button>
		</div>
	</div>
</li>
{/strip}