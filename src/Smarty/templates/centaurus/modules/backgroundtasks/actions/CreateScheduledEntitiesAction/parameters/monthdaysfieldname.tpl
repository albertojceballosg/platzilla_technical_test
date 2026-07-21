{strip}
{assign var='type' value=$PARAMETER->getType ()}
{assign var='valueFormula' value=$PARAMETER->getValueFormula ()}
<div class="col-xs-12 parameter mandatory">
	<div class="row">
		<label for="{$parameterName}-{$ACTION_SEQUENCE}" class="col-xs-12">{$MOD[$parameterName]}: <span class="required">*</span></label>
		<div class="form-group col-xs-12 col-md-3">
			<select id="{$parameterName}-{$ACTION_SEQUENCE}" name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][type]" class="form-control parametertype" data-uitype="{$uiType}@{$ACTION_SEQUENCE}" onchange="BackgroundTasksUtils.setParameterValue (this);">
				<option value=""></option>
				<option value="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD}"{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD)} selected="selected"{/if}>{$MOD[BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD]}</option>
			</select>
		</div>
		<div class="form-group col-xs-12 col-md-9 field-container">
	{assign var='availableFields' value=array()}
	{foreach $AVAILABLE_FIELDS as $field}
		{if ($field.uitype == Field::UI_TYPE_GLOBAL_PICKLIST) && ($field.fieldname == 'sys_monthdays')}
			{$availableFields[] = $field}
		{/if}
	{/foreach}
			<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][valueformula]" class="form-control parametervalue" title="" data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD}"{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD)} disabled="disabled" style="display: none;"{/if}>
	{if (!empty ($availableFields))}
				<option value=""></option>
		{foreach $availableFields as $field}
				<option value="{$field.fieldname}"{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD) && ($valueFormula == $field.fieldname)} selected="selected"{/if}>{$field.fieldlabel}</option>
		{/foreach}
	{else}
				<option value="">Imposible ejecutar la acción: el módulo {$SELECTED_MODULE_NAME} no tiene campos de días del mes</option>
	{/if}
			</select>
		</div>
	</div>
</div>
{/strip}