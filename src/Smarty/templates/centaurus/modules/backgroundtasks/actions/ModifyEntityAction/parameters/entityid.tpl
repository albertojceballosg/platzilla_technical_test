{strip}
{assign var='isMandatory' value=$PARAMETER->isMandatory ()}
{assign var='refreshOnChanges' value=$PARAMETER->refreshOnChanges ()}
{assign var='type' value=$PARAMETER->getType ()}
{assign var='valueFormula' value=$PARAMETER->getValueFormula ()}
<div class="col-xs-12 parameter{if ($isMandatory)} mandatory{/if}">
	<div class="row">
		<label for="{$parameterName}-{$ACTION_SEQUENCE}" class="col-xs-12">{$MOD[$parameterName]}:{if ($isMandatory)} <span class="required">*</span>{/if}</label>
		<div class="form-group col-xs-12 col-md-3">
			<select id="{$parameterName}-{$ACTION_SEQUENCE}" name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][type]" class="form-control parametertype" data-uitype="{$uiType}@{$ACTION_SEQUENCE}" onchange="BackgroundTasksUtils.setParameterValue (this);">
				<option value=""></option>
{if (!empty ($SELECTED_PARAMETER_VALUES['modulename']))}
				<option value="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_RECORD_ID_SOURCE_FIELD}"{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_RECORD_ID_SOURCE_FIELD)} selected="selected"{/if}>{if ($SELECTED_PARAMETER_VALUES['modulename'] == $SELECTED_MODULE_NAME)}El registro que se está procesando{else}El registro relacionado{/if}</option>
				<option value="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CUSTOM_SQL}"{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CUSTOM_SQL)} selected="selected"{/if} data-scope="SYSTEM"{if ($TASK_SCOPE != BackgroundTask::SCOPE_SYSTEM)} style="display: none;"{/if}>{$MOD[BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CUSTOM_SQL]|escape: 'html'}</option>
{/if}
			</select>
		</div>
		<div class="form-group col-xs-12 col-md-9 field-container">
{if (!empty ($SELECTED_PARAMETER_VALUES['modulename'])) && ($SELECTED_PARAMETER_VALUES['modulename'] != $SELECTED_MODULE_NAME)}
	{assign var='availableRelatedModuleFields' value=array()}
	{foreach $AVAILABLE_FIELDS as $field}
		{if ($field.uitype == Field::UI_TYPE_MODULE_REFERENCE) && ($field.relatedmodulename == $SELECTED_PARAMETER_VALUES['modulename'])}
			{$availableRelatedModuleFields[] = $field}
		{/if}
	{/foreach}
			<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][valueformula]" class="form-control parametervalue" title="" data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_RECORD_ID_SOURCE_FIELD}"{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_RECORD_ID_SOURCE_FIELD) && ($SELECTED_PARAMETER_VALUES['modulename'] == $SELECTED_MODULE_NAME)} disabled="disabled" style="display: none;"{/if}>
	{if (!empty ($availableRelatedModuleFields))}
				<option value=""></option>
		{foreach $availableRelatedModuleFields as $field}
				<option value="{$field.fieldname}"{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_RECORD_ID_SOURCE_FIELD) && ($valueFormula == $field.fieldname)} selected="selected"{/if}>{$field.fieldlabel}</option>
		{/foreach}
	{else}
				<option value="">Imposible ejecutar la acción: el módulo {$SELECTED_MODULE_NAME} no tiene campos relacionados al módulo {$SELECTED_PARAMETER_VALUES['modulename']}</option>
	{/if}
			</select>
{elseif (!empty ($SELECTED_PARAMETER_VALUES['modulename'])) && ($SELECTED_PARAMETER_VALUES['modulename'] == $SELECTED_MODULE_NAME)}
			<input type="hidden" name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][valueformula]" value="record_id" class="form-control parametervalue" placeholder="" data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_RECORD_ID_SOURCE_FIELD}"{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_RECORD_ID_SOURCE_FIELD) && ($SELECTED_PARAMETER_VALUES['modulename'] != $SELECTED_MODULE_NAME)} disabled="disabled" style="display: none;"{/if} />
{/if}
			<textarea name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][valueformula]" class="form-control parametervalue" placeholder="" data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CUSTOM_SQL}"{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CUSTOM_SQL)} disabled="disabled" style="display: none;"{/if}>{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CUSTOM_SQL)}{$valueFormula|escape: 'html'}{/if}</textarea>
		</div>
	</div>
</div>
{/strip}