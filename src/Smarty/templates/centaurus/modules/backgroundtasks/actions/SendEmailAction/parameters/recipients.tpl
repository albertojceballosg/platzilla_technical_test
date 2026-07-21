{strip}
{assign var='refreshOnChanges' value=$PARAMETER->refreshOnChanges ()}
{assign var='type' value=$PARAMETER->getType ()}
{assign var='valueFormula' value=$PARAMETER->getValueFormula ()}
<div class="col-xs-12 parameter mandatory">
	<div class="row">
		<label for="{$parameterName}-{$ACTION_SEQUENCE}" class="col-xs-12">{$MOD[$parameterName]}: <span class="required">*</span></label>
		<div class="form-group col-xs-12 col-md-3">
			<select id="{$parameterName}-{$ACTION_SEQUENCE}" name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][type]" class="form-control parametertype" data-uitype="{$uiType}@{$ACTION_SEQUENCE}" onchange="BackgroundTasksUtils.setParameterValue (this);">
				<option value=""></option>
				<option value="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_EMAIL_SOURCE_FIELD}"{if (in_array ($type, array (BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_EMAIL_SOURCE_FIELD, BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD)))} selected="selected"{/if}>Campos de correo del módulo donde comienza la tarea</option>
				<option value="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL}"{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL)} selected="selected"{/if}>Direcciones de correo separadas por comas</option>
				<option value="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_INSTANCE_EMAILS}"{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_INSTANCE_EMAILS)} selected="selected"{/if}>{$MOD[BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_INSTANCE_EMAILS]}</option>
				<option value="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_VARIABLE}"{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_VARIABLE)} selected="selected"{/if}>Otras opciones</option>
				<option value="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CUSTOM_SQL}"{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CUSTOM_SQL)} selected="selected"{/if} data-scope="SYSTEM"{if ($TASK_SCOPE != BackgroundTask::SCOPE_SYSTEM)} style="display: none;"{/if}>{$MOD[BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CUSTOM_SQL]}</option>
			</select>
		</div>
		<div class="form-group col-xs-12 col-md-9 field-container">
{assign var='availableEmailFields' value=array()}
{foreach $AVAILABLE_FIELDS as $field}
	{if ($field.uitype == Field::UI_TYPE_EMAIL)}
		{$dummy = array_push ($availableEmailFields, $field)}
	{/if}
{/foreach}
			<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][valueformula]" class="form-control parametervalue" title="" data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_EMAIL_SOURCE_FIELD}"{if (!in_array ($type, array (BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_EMAIL_SOURCE_FIELD, BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD)))} disabled="disabled" style="display: none;"{/if}{if ($refreshOnChanges)} onchange="BackgroundTasksUtils.refreshFields (this);"{/if}>
{if (!empty ($availableEmailFields))}
				<option value=""></option>
	{foreach $availableEmailFields as $field}
				<option value="{$field.fieldname}"{if ($valueFormula == $field.fieldname)} selected="selected"{/if}>{$field.fieldlabel}</option>
	{/foreach}
{else}
				<option value="">Imposible ejecutar la acción: el módulo {$SELECTED_MODULE_NAME} no tiene campos de correo</option>
{/if}
			</select>
			<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][valueformula]" class="form-control parametervalue" title="" data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_INSTANCE_EMAILS}"{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_INSTANCE_EMAILS)} disabled="disabled" style="display: none;"{/if}{if ($refreshOnChanges)} onchange="BackgroundTasksUtils.refreshFields (this);"{/if}>
				<option value=""></option>
{foreach $AVAILABLE_USERS as $availableUser}
				<option value="{$availableUser->getEmail ()}"{if ($valueFormula == $availableUser->getEmail ())} selected="selected"{/if}>{trim("{$availableUser->getFirstName ()} {$availableUser->getLastName ()}")}</option>
{/foreach}
			</select>
{assign var='availableEmailVariables' value=array()}
{foreach $SYSTEM_VARIABLES as $variableName => $variableLabel}
	{if ($SYSTEM_VARIABLE_TYPES[$variableName] == 'EMAIL')}
		{$availableEmailVariables[$variableName] = $variableLabel}
	{/if}
{/foreach}
			<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][valueformula]" class="form-control parametervalue" title="" data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_VARIABLE}"{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_VARIABLE)} disabled="disabled" style="display: none;"{/if}{if ($refreshOnChanges)} onchange="BackgroundTasksUtils.refreshFields (this);"{/if}>
{if (!empty ($availableEmailVariables))}
				<option value=""></option>
	{foreach $availableEmailVariables as $variableName => $variableLabel}
		{assign var='dummy' value='{'|cat: $variableName : '}'}
				<option value="{$dummy}"{if ($valueFormula == $dummy)} selected="selected"{/if}>{$variableLabel}</option>
	{/foreach}
{else}
				<option value="">Imposible ejecutar la acción: no existen opciones adicionales de tipo correo</option>
{/if}
			</select>
			<textarea name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][valueformula]" class="form-control parametervalue" placeholder="" data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CUSTOM_SQL}"{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CUSTOM_SQL)} disabled="disabled" style="display: none;"{/if}{if ($refreshOnChanges)} onchange="BackgroundTasksUtils.refreshFields (this);"{/if}>{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CUSTOM_SQL)}{$valueFormula|escape: 'html'}{/if}</textarea>
			<input type="text" name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][valueformula]" value="{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL)}{$valueFormula|escape: 'html'}{/if}" class="form-control parametervalue" placeholder="" data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL}"{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL)} disabled="disabled" style="display: none;"{/if}{if ($refreshOnChanges)} onchange="BackgroundTasksUtils.refreshFields (this);"{/if} />
		</div>
	</div>
</div>
{/strip}