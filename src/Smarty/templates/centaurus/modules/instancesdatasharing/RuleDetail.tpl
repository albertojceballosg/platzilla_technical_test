{strip}
{if (isset ($FIELD))}
	{assign var='fieldLabel' value=$FIELD['label']}
	{assign var='fieldName' value=$FIELD['name']}
	{assign var='uiType' value=$FIELD['uitype']}
{else}
	{assign var='fieldLabel' value=null}
	{assign var='fieldName' value=null}
	{assign var='uiType' value=null}
{/if}
{assign var='actionType' value=null}
{assign var='detailId' value=null}
{assign var='parameterFormula' value=null}
{assign var='parameterType' value=null}
{if (isset ($RULE))}
	{if (isset ($FIELD))}
		{assign var='ruleDetails' value=$RULE->getDetails ()}
		{foreach $ruleDetails as $ruleDetail}
			{if ($FIELD['name'] == $ruleDetail->getTargetFieldName ())}
				{assign var='actionType' value=$ruleDetail->getActionType ()}
				{assign var='detailId' value=$ruleDetail->getId ()}
				{assign var='parameterFormula' value=$ruleDetail->getParameterFormula ()}
				{assign var='parameterType' value=$ruleDetail->getParameterType ()}
				{break}
			{/if}
		{/foreach}
	{/if}
{/if}
<div class="row rule-field" data-id="{$detailId}" data-uitype="{$uiType}">
	<div class="form-group col-xs-12 col-md-3">
		<input type="hidden" value="{$fieldName}" class="form-control field-name" />
		<input type="text" value="{$fieldLabel}" class="form-control field-label" placeholder="" disabled="disabled" />
	</div>
	<div class="form-group col-xs-12 col-md-3">
		<select name="details[{$detailId}][fields][{$fieldName}][parametertype]" class="form-control parameter-type" title="" onchange="DataSharingUtils.setParameterType (this);">
			<option value="">No compartir</option>
			<option value="{DataSharingRuleDetailInterface::PARAMETER_TYPE_LITERAL}"{if ($parameterType == DataSharingRuleDetailInterface::PARAMETER_TYPE_LITERAL) && ($uiType == Field::UI_TYPE_EMAIL)} selected="selected"{/if} data-type="['EMAIL']"{if ($uiType != Field::UI_TYPE_EMAIL)} style="display: none;" disabled="disabled"{/if}>Direcciones de correo separadas por comas</option>
			<option value="{DataSharingRuleDetailInterface::PARAMETER_TYPE_LITERAL}"{if ($parameterType == DataSharingRuleDetailInterface::PARAMETER_TYPE_LITERAL) && (in_array ($uiType, array (Field::UI_TYPE_CREATED_TIME, Field::UI_TYPE_DATE, Field::UI_TYPE_DATETIME)))} selected="selected"{/if} data-type="['DATE']"{if (!in_array ($uiType, array (Field::UI_TYPE_CREATED_TIME, Field::UI_TYPE_DATE, Field::UI_TYPE_DATETIME)))} style="display: none;" disabled="disabled"{/if}>Selecciona la fecha</option>
			<option value="{DataSharingRuleDetailInterface::PARAMETER_TYPE_LITERAL}"{if ($parameterType == DataSharingRuleDetailInterface::PARAMETER_TYPE_LITERAL) && (in_array ($uiType, array (Field::UI_TYPE_CURRENCY, Field::UI_TYPE_NUMBER, Field::UI_TYPE_PERCENTAGE)))} selected="selected"{/if} data-type="['NUMBER']"{if (!in_array ($uiType, array (Field::UI_TYPE_CURRENCY, Field::UI_TYPE_NUMBER, Field::UI_TYPE_PERCENTAGE)))} style="display: none;" disabled="disabled"{/if}>Indica el valor</option>
			<option value="{DataSharingRuleDetailInterface::PARAMETER_TYPE_LITERAL}"{if ($parameterType == DataSharingRuleDetailInterface::PARAMETER_TYPE_LITERAL) && (in_array ($uiType, array (Field::UI_TYPE_PHONE, Field::UI_TYPE_SKYPE, Field::UI_TYPE_TEXT, Field::UI_TYPE_TEXTAREA, Field::UI_TYPE_TIME, Field::UI_TYPE_URL)))} selected="selected"{/if} data-type="['TEXT']"{if (!in_array ($uiType, array (Field::UI_TYPE_PHONE, Field::UI_TYPE_SKYPE, Field::UI_TYPE_TEXT, Field::UI_TYPE_TEXTAREA, Field::UI_TYPE_TIME, Field::UI_TYPE_URL)))} style="display: none;" disabled="disabled"{/if}>Indica el valor</option>
			<option value="{DataSharingRuleDetailInterface::PARAMETER_TYPE_LITERAL}"{if ($parameterType == DataSharingRuleDetailInterface::PARAMETER_TYPE_LITERAL) && (in_array ($uiType, array (Field::UI_TYPE_GLOBAL_PICKLIST, Field::UI_TYPE_MULTI_SELECT, Field::UI_TYPE_PICKLIST)))} selected="selected"{/if} data-type="['PICKLIST']"{if (!in_array ($uiType, array (Field::UI_TYPE_GLOBAL_PICKLIST, Field::UI_TYPE_MULTI_SELECT, Field::UI_TYPE_PICKLIST)))} style="display: none;" disabled="disabled"{/if}>Selecciona el valor</option>
			<option value="{DataSharingRuleDetailInterface::PARAMETER_TYPE_SOURCE_FIELD}"{if ($parameterType == DataSharingRuleDetailInterface::PARAMETER_TYPE_SOURCE_FIELD)} selected="selected"{/if} data-type="['DATE', 'EMAIL', 'NUMBER', 'PICKLIST', 'TEXT']"{if ($parameterType != DataSharingRuleDetailInterface::PARAMETER_TYPE_SOURCE_FIELD)} style="display: none;" disabled="disabled"{/if}>El valor de un campo</option>
			<option value="{DataSharingRuleDetailInterface::PARAMETER_TYPE_SOURCE_GRID_FIELD}"{if ($parameterType == DataSharingRuleDetailInterface::PARAMETER_TYPE_SOURCE_GRID_FIELD)} selected="selected"{/if} data-type="['GRID']"{if ($parameterType != DataSharingRuleDetailInterface::PARAMETER_TYPE_SOURCE_GRID_FIELD)} style="display: none;" disabled="disabled"{/if}>El valor de un campo de la tabla</option>
			<option value="{DataSharingRuleDetailInterface::PARAMETER_TYPE_VARIABLE}"{if ($parameterType == DataSharingRuleDetailInterface::PARAMETER_TYPE_VARIABLE)} selected="selected"{/if} data-type="['DATE', 'EMAIL', 'TEXT', 'USER']"{if ($parameterType != DataSharingRuleDetailInterface::PARAMETER_TYPE_VARIABLE)} style="display: none;" disabled="disabled"{/if}>Otras opciones</option>
			<option value="{DataSharingRuleDetailInterface::PARAMETER_TYPE_SHARING_RULE}"{if ($parameterType == DataSharingRuleDetailInterface::PARAMETER_TYPE_SHARING_RULE)} selected="selected"{/if} data-type="['MODULE REFERENCE']"{if ($parameterType != DataSharingRuleDetailInterface::PARAMETER_TYPE_SHARING_RULE)} style="display: none;" disabled="disabled"{/if}>Compartir</option>
		</select>
	</div>
	<div class="form-group col-xs-12 col-md-4 field-container">
		<input type="text" name="details[{$detailId}][fields][{$fieldName}][parameterformula]" value="{if ($parameterType == DataSharingRuleDetailInterface::PARAMETER_TYPE_LITERAL) && ($uiType == Field::UI_TYPE_EMAIL)}{$parameterFormula|escape: 'html'}{/if}" class="form-control parameter-formula" placeholder="" data-parameter-type="{DataSharingRuleDetailInterface::PARAMETER_TYPE_LITERAL}" data-type="['EMAIL']"{if ($parameterType != DataSharingRuleDetailInterface::PARAMETER_TYPE_LITERAL) || ($uiType != Field::UI_TYPE_EMAIL)} disabled="disabled" style="display: none;"{/if} />
		<input type="text" name="details[{$detailId}][fields][{$fieldName}][parameterformula]" value="{if ($parameterType == DataSharingRuleDetailInterface::PARAMETER_TYPE_LITERAL) && (in_array ($uiType, array (Field::UI_TYPE_CREATED_TIME, Field::UI_TYPE_DATE, Field::UI_TYPE_DATETIME)))}{$parameterFormula|escape: 'html'}{/if}" class="form-control parameter-formula date" placeholder="" readonly="readonly" data-parameter-type="{DataSharingRuleDetailInterface::PARAMETER_TYPE_LITERAL}" data-type="['DATE']"{if ($parameterType != DataSharingRuleDetailInterface::PARAMETER_TYPE_LITERAL) || (!in_array ($uiType, array (Field::UI_TYPE_CREATED_TIME, Field::UI_TYPE_DATE, Field::UI_TYPE_DATETIME)))} disabled="disabled" style="display: none;"{/if} />
		<input type="number" name="details[{$detailId}][fields][{$fieldName}][parameterformula]" value="{if ($parameterType == DataSharingRuleDetailInterface::PARAMETER_TYPE_LITERAL) && (in_array ($uiType, array (Field::UI_TYPE_CURRENCY, Field::UI_TYPE_NUMBER, Field::UI_TYPE_PERCENTAGE)))}{$parameterFormula|escape: 'html'}{/if}" class="form-control parameter-formula" placeholder="" data-parameter-type="{DataSharingRuleDetailInterface::PARAMETER_TYPE_LITERAL}" data-type="['NUMBER']"{if ($parameterType != DataSharingRuleDetailInterface::PARAMETER_TYPE_LITERAL) || (!in_array ($uiType, array (Field::UI_TYPE_CURRENCY, Field::UI_TYPE_NUMBER, Field::UI_TYPE_PERCENTAGE)))} disabled="disabled" style="display: none;"{/if} />
		<input type="text" name="details[{$detailId}][fields][{$fieldName}][parameterformula]" value="{if ($parameterType == DataSharingRuleDetailInterface::PARAMETER_TYPE_LITERAL) && (in_array ($uiType, array (Field::UI_TYPE_PHONE, Field::UI_TYPE_SKYPE, Field::UI_TYPE_TEXT, Field::UI_TYPE_TEXTAREA, Field::UI_TYPE_TIME, Field::UI_TYPE_URL)))}{$parameterFormula|escape: 'html'}{/if}" class="form-control parameter-formula" placeholder="" data-parameter-type="{DataSharingRuleDetailInterface::PARAMETER_TYPE_LITERAL}" data-type="['TEXT']"{if ($parameterType != DataSharingRuleDetailInterface::PARAMETER_TYPE_LITERAL) || (!in_array ($uiType, array (Field::UI_TYPE_PHONE, Field::UI_TYPE_SKYPE, Field::UI_TYPE_TEXT, Field::UI_TYPE_TEXTAREA, Field::UI_TYPE_TIME, Field::UI_TYPE_URL)))} disabled="disabled" style="display: none;"{/if} />
		<select name="details[{$detailId}][fields][{$fieldName}][parameterformula]" class="form-control parameter-formula" title="" data-parameter-type="{DataSharingRuleDetailInterface::PARAMETER_TYPE_LITERAL}" data-type="['PICKLIST']"{if ($parameterType != DataSharingRuleDetailInterface::PARAMETER_TYPE_LITERAL) || (!in_array ($uiType, array (Field::UI_TYPE_GLOBAL_PICKLIST, Field::UI_TYPE_MULTI_SELECT, Field::UI_TYPE_PICKLIST)))} disabled="disabled" style="display: none;"{/if}>
{if (!empty ($AVAILABLE_PICKLIST_VALUES[$fieldName]))}
			<option value=""></option>
	{foreach $AVAILABLE_PICKLIST_VALUES[$fieldName] as $availablePicklistValue}
			<option value="{$availablePicklistValue}"{if ($parameterFormula == $availablePicklistValue)} selected="selected"{/if}>{$availablePicklistValue}</option>
	{/foreach}
{/if}
		</select>
		<select name="details[{$detailId}][fields][{$fieldName}][parameterformula]" class="form-control parameter-formula" title="" data-parameter-type="{DataSharingRuleDetailInterface::PARAMETER_TYPE_SHARING_RULE}" data-type="['MODULE REFERENCE']"{if ($parameterType != DataSharingRuleDetailInterface::PARAMETER_TYPE_SHARING_RULE) || (!in_array ($uiType, array (Field::UI_TYPE_MODULE_RECORDS, Field::UI_TYPE_MODULE_REFERENCE)))} disabled="disabled" style="display: none;"{/if}>
			<option value=""></option>
			<option value="FULL"{if ($parameterFormula == 'FULL')} selected="selected"{/if}>El registro y sus registros relacionados</option>
			<option value="MINIMAL"{if ($parameterFormula == 'MINIMAL')} selected="selected"{/if}>Sólo el registro</option>
		</select>
		<select name="details[{$detailId}][fields][{$fieldName}][parameterformula]" class="form-control parameter-formula" title="" data-type="['DATE', 'EMAIL', 'NUMBER', 'PICKLIST', 'TEXT', 'USER']" data-parameter-type="{DataSharingRuleDetailInterface::PARAMETER_TYPE_SOURCE_FIELD}"{if ($parameterType != DataSharingRuleDetailInterface::PARAMETER_TYPE_SOURCE_FIELD)} disabled="disabled" style="display: none;"{/if}>
{if (!empty ($AVAILABLE_FIELDS))}
			<option value=""></option>
	{foreach $AVAILABLE_FIELDS as $availableField}
			<option value="{$availableField['name']}"{if ($parameterFormula == $availableField['name'])} selected="selected"{/if}>{$availableField['label']}</option>
	{/foreach}
{/if}
		</select>
		<select name="details[{$detailId}][fields][{$fieldName}][parameterformula]" class="form-control parameter-formula" title="" data-type="['DATE', 'EMAIL', 'TEXT', 'USER']" data-parameter-type="{DataSharingRuleDetailInterface::PARAMETER_TYPE_VARIABLE}"{if ($parameterType != DataSharingRuleDetailInterface::PARAMETER_TYPE_VARIABLE)} disabled="disabled" style="display: none;"{/if}>
{if (!empty ($SYSTEM_VARIABLES))}
			<option value=""></option>
	{foreach $SYSTEM_VARIABLES as $variableName => $variableLabel}
		{assign var='dummy' value='{'|cat: $variableName : '}'}
			<option value="{$dummy}" data-type="['{$SYSTEM_VARIABLE_TYPES[$variableName]}']"{if ($parameterFormula == $dummy)} selected="selected"{/if}>{$variableLabel}</option>
	{/foreach}
{else}
			<option value="">Imposible ejecutar la acción: no hay variables del tipo de campo</option>
{/if}
		</select>
	</div>
	<div class="form-group col-xs-12 col-md-2">
		<select name="details[{$detailId}][fields][{$fieldName}][actiontype]" class="form-control action-type" title=""{if (empty ($parameterType))} disabled="disabled" style="display: none;"{/if}>
			<option value=""></option>
			<option value="{DataSharingRuleDetailInterface::ACTION_SEND_AND_RECEIVE}"{if ($actionType == DataSharingRuleDetailInterface::ACTION_SEND_AND_RECEIVE)} selected="selected"{/if}>Enviar y recibir</option>
			<option value="{DataSharingRuleDetailInterface::ACTION_SEND_ONLY}"{if ($actionType == DataSharingRuleDetailInterface::ACTION_SEND_ONLY)} selected="selected"{/if}>Sólo enviar</option>
			<option value="{DataSharingRuleDetailInterface::ACTION_RECEIVE_ONLY}"{if ($actionType == DataSharingRuleDetailInterface::ACTION_RECEIVE_ONLY)} selected="selected"{/if}>Sólo recibir</option>
		</select>
	</div>
</div>
{/strip}