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
{foreach $availableTypes as $availableType}
				<option value="{$availableType|escape: 'html'}"{if ($availableType == $type)} selected="selected"{/if}{if (in_array ($availableType, array (BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CUSTOM_SQL, BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_FORMULA)))} data-scope="SYSTEM"{/if}{if (in_array ($availableType, array (BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CUSTOM_SQL, BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_FORMULA))) && ($TASK_SCOPE != BackgroundTask::SCOPE_SYSTEM)} style="display: none;"{/if}>{$MOD[$availableType]|escape: 'html'}</option>
{/foreach}
			</select>
		</div>
		<div class="form-group col-xs-12 col-md-9 field-container">
{foreach $availableTypes as $availableType}
	{if ($availableType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD)}
			<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][valueformula]" class="form-control parametervalue" title="" data-type="{$availableType}"{if ($availableType != $type)} disabled="disabled" style="display: none;"{/if}{if ($refreshOnChanges)} onchange="BackgroundTasksUtils.refreshFields (this);"{/if}>
				<option value=""></option>
		{foreach $AVAILABLE_FIELDS as $field}
				<option value="{$field.fieldname}"{if ($availableType == $type) && ($valueFormula == $field.fieldname)} selected="selected"{/if}>{$field.fieldlabel}</option>
		{/foreach}
			</select>
	{elseif ($availableType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_INSTANCE_EMAILS)}
			<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][valueformula]" class="form-control parametervalue" title="" data-type="{$availableType}"{if ($availableType != $type)} disabled="disabled" style="display: none;"{/if}{if ($refreshOnChanges)} onchange="BackgroundTasksUtils.refreshFields (this);"{/if}>
				<option value=""></option>
		{foreach $AVAILABLE_USERS as $availableUser}
				<option value="{$availableUser->getEmail ()}"{if ($valueFormula == $availableUser->getEmail ())} selected="selected"{/if}>{trim("{$availableUser->getFirstName ()} {$availableUser->getLastName ()}")}</option>
		{/foreach}
			</select>
	{elseif ($availableType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_RECORD_ID_SOURCE_FIELD)}
			<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][valueformula]" class="form-control parametervalue" title="" data-type="{$availableType}"{if ($availableType != $type)} disabled="disabled" style="display: none;"{/if}{if ($refreshOnChanges)} onchange="BackgroundTasksUtils.refreshFields (this);"{/if}>
				<option value=""></option>
				<option value="record_id"{if ($availableType == $type) && ($valueFormula == 'record_id')} selected="selected"{/if}>(El registro que se está procesando)</option>
		{foreach $AVAILABLE_FIELDS as $field}
			{if ($field.uitype == Field::UI_TYPE_MODULE_REFERENCE)}
				<option value="{$field.fieldname}"{if ($availableType == $type) && ($valueFormula == $field.fieldname)} selected="selected"{/if}>{$field.fieldlabel}</option>
			{/if}
		{/foreach}
			</select>
	{elseif ($availableType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_EMAIL_SOURCE_FIELD)}
			<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][valueformula]" class="form-control parametervalue" title="" data-type="{$availableType}"{if ($availableType != $type)} disabled="disabled" style="display: none;"{/if}{if ($refreshOnChanges)} onchange="BackgroundTasksUtils.refreshFields (this);"{/if}>
				<option value=""></option>
		{foreach $AVAILABLE_FIELDS as $field}
			{if ($field.uitype == Field::UI_TYPE_EMAIL)}
				<option value="{$field.fieldname}"{if ($availableType == $type) && ($valueFormula == $field.fieldname)} selected="selected"{/if}>{$field.fieldlabel}</option>
			{/if}
		{/foreach}
			</select>
	{elseif ($availableType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_EMAIL_SOURCE_FIELD)}
			<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][valueformula]" class="form-control parametervalue" title="" data-type="{$availableType}"{if ($availableType != $type)} disabled="disabled" style="display: none;"{/if}{if ($refreshOnChanges)} onchange="BackgroundTasksUtils.refreshFields (this);"{/if}>
				<option value=""></option>
		{foreach $AVAILABLE_FIELDS as $field}
			{if ($field.uitype == Field::UI_TYPE_EMAIL)}
				<option value="{$field.fieldname}"{if ($availableType == $type) && ($valueFormula == $field.fieldname)} selected="selected"{/if}>{$field.fieldlabel}</option>
			{/if}
		{/foreach}
			</select>
	{elseif ($availableType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_VARIABLE)}
			<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][valueformula]" class="form-control parametervalue" title="" data-type="{$availableType}"{if ($availableType != $type)} disabled="disabled" style="display: none;"{/if}{if ($refreshOnChanges)} onchange="BackgroundTasksUtils.refreshFields (this);"{/if}>
				<option value=""></option>
		{foreach $SYSTEM_VARIABLES as $variableName => $variableLabel}
			{if (($PARAMETER->getActionType () == 'SEND EMAIL') && ($parameterName == 'recipients') && ($SYSTEM_VARIABLE_TYPES[$variableName] != 'EMAIL'))}
				{continue}
			{/if}
			{assign var='dummy' value='{'|cat: $variableName : '}'}
				<option value="{$dummy}"{if ($value == $dummy)} selected="selected"{/if}>{$variableLabel}</option>
		{/foreach}
			</select>
	{elseif ($availableType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_PREVIOUS_OUTPUT)}
			<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][valueformula]" class="form-control parametervalue previousoutput" title="" data-type="{$availableType}"{if ($availableType != $type)} disabled="disabled" style="display: none;"{/if}{if ($refreshOnChanges)} onchange="BackgroundTasksUtils.refreshFields (this);"{/if}>
				<option value=""></option>
		{if (!empty ($TASK_ACTIONS))}
			{foreach $TASK_ACTIONS as $taskAction}
				{if ($taskAction->getName () == $ACTION->getName ())}
					{break}
				{/if}
				<option value="{$taskAction->getName ()}"{if ($availableType == $type) && ($valueFormula == $taskAction->getName ())} selected="selected"{/if}>{$taskAction->getName ()}</option>
			{/foreach}
		{/if}
			</select>
	{elseif ($availableType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_FORMULA)}
			<input type="text" name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][valueformula]" value="{if ($availableType == $type)}{$valueFormula|escape: 'html'}{/if}" class="form-control parametervalue" placeholder="" data-type="{$availableType}"{if ($availableType != $type)} disabled="disabled" style="display: none;"{/if}{if ($refreshOnChanges)} onchange="BackgroundTasksUtils.refreshFields (this);"{/if} />
    {elseif ($availableType eq BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_NOTIFICATIONS)}
			<select id="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_NOTIFICATIONS}-{$ACTION_SEQUENCE}" name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][valueformula]" class="form-control parametervalue {if $NOTIFICATIONS eq NULL}hide{/if}">
    	{if $NOTIFICATIONS eq NULL}
				<option value=""></option>
		{else}
		{$NOTIFICATIONS[$ACTION_SEQUENCE]}
		{/if}
			</select>
	{elseif ($availableType != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CALCULATED_DATE)}
			<input type="text" name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][valueformula]" value="{if ($availableType == $type)}{$valueFormula|escape: 'html'}{/if}" class="form-control parametervalue" placeholder="" data-type="{$availableType}"{if ($availableType != $type)} disabled="disabled" style="display: none;"{/if}{if ($refreshOnChanges)} onchange="BackgroundTasksUtils.refreshFields (this);"{/if} />
	{/if}
	{/foreach}
		</div>
	</div>
</div>
{/strip}