{strip}
	{assign var='defaultOptions' value=$PARAMETER->getDefaultOptions ()}
	{assign var='isMandatory' value=$PARAMETER->isMandatory ()}
	{assign var='refreshOnChanges' value=$PARAMETER->refreshOnChanges ()}
	{assign var='translationModule' value=$PARAMETER->getTranslationModule ()}
	{assign var='types' value=$PARAMETER->getType ()}
	{assign var='valueFormulas' value=$PARAMETER->getValueFormula ()}
	{if (!empty ($defaultOptions))}
		<div class="col-xs-12 parameter{if ($isMandatory)} mandatory{/if}">
			<label>{$MOD[$parameterName]}{if ($isMandatory)}<span class="required">*</span>{/if}</label>
			{foreach $defaultOptions as $expandedKey => $optionData}
				{if (!empty ($optionData['attributes']))}
					{assign var='optionAttributes' value=array()}
					{foreach $optionData['attributes'] as $attributeName => $attributeValue}
						{$optionAttributes[] = "data-{$attributeName}=\"{$attributeValue|escape: 'html'}\""}
					{/foreach}
				{else}
					{assign var='optionAttributes' value=null}
				{/if}
				{if (!empty ($optionData['attributes']['uitype']))}
					{assign var='uiType' value=$optionData['attributes']['uitype']}
				{else}
					{assign var='uiType' value=null}
				{/if}
				{assign var='type' value=$types[$expandedKey]}
				{assign var='value' value=$valueFormulas[$expandedKey]}
				<div class="row">
					<div class="form-group col-xs-12 col-md-3">
						<input type="text" value="{$optionData['label']|getTranslatedString}" disabled="disabled"
							class="form-control parametername" placeholder="" {if (!empty ($optionAttributes))}
							{join(' ', $optionAttributes)}{/if} />
					</div>
					<div class="form-group col-xs-12 col-md-3">
						<select id="{$parameterName}-{$ACTION_SEQUENCE}"
							name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$expandedKey}][type]"
							class="form-control parametertype" title="" data-uitype="{$uiType}@{$ACTION_SEQUENCE}"
							onchange="BackgroundTasksUtils.setParameterValue (this);">
							<option value=""></option>
							{foreach $availableTypes as $availableType}
								<option value="{$availableType|escape: 'html'}" {if ($availableType == $type)} selected="selected"
										{/if}{if (in_array ($availableType, array (BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CUSTOM_SQL, BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_FORMULA)))}
										data-scope="SYSTEM"
										{/if}{if (in_array ($availableType, array (BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CUSTOM_SQL, BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_FORMULA))) && ($TASK_SCOPE != BackgroundTask::SCOPE_SYSTEM)}
									style="display: none;" {/if}>{$MOD[$availableType]|escape: 'html'}</option>
							{/foreach}
						</select>
					</div>
					<div class="form-group col-xs-12 col-md-6 field-container">
						{foreach $availableTypes as $availableType}
							{if ($availableType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL) && (in_array ($uiType, array (Field::UI_TYPE_MODIFIED_BY, Field::UI_TYPE_OWNER)))}
								<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$expandedKey}][valueformula]"
									class="form-control parametervalue users" title="" data-type="{$availableType}"
									{if ($availableType != $type)} disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
								onchange="BackgroundTasksUtils.refreshFields (this);" {/if}>
								<option value=""></option>
								{foreach $AVAILABLE_USERS as $availableUser}
									<option value="{$availableUser->getId ()}" {if ($valueFormula == $availableUser->getId ())}
										selected="selected" {/if}>{trim("{$availableUser->getFirstName ()}
					{$availableUser->getLastName ()}")}</option>
								{/foreach}
							</select>
						{elseif ($availableType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL) && (in_array ($uiType, array (Field::UI_TYPE_GLOBAL_PICKLIST, Field::UI_TYPE_MULTI_SELECT, Field::UI_TYPE_PICKLIST)))}
							<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$expandedKey}][valueformula]"
								class="form-control parametervalue" title="" data-type="{$availableType}" {if ($availableType != $type)}
									disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
								onchange="BackgroundTasksUtils.refreshFields (this);" {/if}>
								<option value=""></option>
								{foreach $AVAILABLE_PICKLIST_VALUES[{$ACTION_SEQUENCE}][$expandedKey] as $availablePicklistValue}
									<option value="{$availablePicklistValue}"
										{if ($value|html_entity_decode == $availablePicklistValue|html_entity_decode)} selected="selected"
										{/if}>{$availablePicklistValue}</option>
								{/foreach}
							</select>
						{elseif ($availableType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_RECORD_ID_SOURCE_FIELD) || (($availableType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD) && (in_array ($uiType, array (Field::UI_TYPE_MODULE_REFERENCE))))}
							<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$expandedKey}][valueformula]"
								class="form-control parametervalue" title="" data-type="{$availableType}" {if ($availableType != $type)}
									disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
								onchange="BackgroundTasksUtils.refreshFields (this);" {/if}>
								<option value=""></option>
								<option value="record_id" {if ($availableType == $type) && ($value == 'record_id')} selected="selected"
									{/if}>(El registro que se está procesando)</option>
								{foreach $AVAILABLE_FIELDS as $field}
									{if ($field.uitype == Field::UI_TYPE_MODULE_REFERENCE)}
										<option value="{$field.fieldname}" {if ($availableType == $type) && ($value == $field.fieldname)}
											selected="selected" {/if}>{$field.fieldlabel}</option>
									{/if}
								{/foreach}
							</select>
						{elseif ($availableType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_INSTANCE_EMAILS)}
							<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$expandedKey}][valueformula]"
								class="form-control parametervalue" title="" data-type="{$availableType}" {if ($availableType != $type)}
									disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
								onchange="BackgroundTasksUtils.refreshFields (this);" {/if}>
								<option value=""></option>
								{foreach $AVAILABLE_USERS as $availableUser}
									<option value="{$availableUser->getEmail ()}" {if ($valueFormula == $availableUser->getEmail ())}
										selected="selected" {/if}>{trim("{$availableUser->getFirstName ()}
					{$availableUser->getLastName ()}")}</option>
								{/foreach}
							</select>
						{elseif ($availableType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_EMAIL_SOURCE_FIELD)}
							<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$expandedKey}][valueformula]"
								class="form-control parametervalue" title="" data-type="{$availableType}" {if ($availableType != $type)}
									disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
								onchange="BackgroundTasksUtils.refreshFields (this);" {/if}>
								<option value=""></option>
								{foreach $AVAILABLE_FIELDS as $field}
									{if ($field.uitype == Field::UI_TYPE_EMAIL)}
										<option value="{$field.fieldname}" {if ($availableType == $type) && ($value == $field.fieldname)}
											selected="selected" {/if}>{$field.fieldlabel}</option>
									{/if}
								{/foreach}
							</select>
						{elseif ($availableType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD) && (in_array ($uiType, array (Field::UI_TYPE_MODIFIED_BY, Field::UI_TYPE_OWNER)))}
							<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$expandedKey}][valueformula]"
								class="form-control parametervalue" title="" data-type="{$availableType}" {if ($availableType != $type)}
									disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
								onchange="BackgroundTasksUtils.refreshFields (this);" {/if}>
								<option value=""></option>
								{foreach $AVAILABLE_FIELDS as $field}
									{if (in_array ($field.uitype, array (Field::UI_TYPE_MODIFIED_BY, Field::UI_TYPE_OWNER)))}
										<option value="{$field.fieldname}" {if ($availableType == $type) && ($value == $field.fieldname)}
											selected="selected" {/if}>{$field.fieldlabel}</option>
									{/if}
								{/foreach}
							</select>
						{elseif ($availableType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD)}
							<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$expandedKey}][valueformula]"
								class="form-control parametervalue" title="" data-type="{$availableType}" {if ($availableType != $type)}
									disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
								onchange="BackgroundTasksUtils.refreshFields (this);" {/if}>
								<option value=""></option>
								{foreach $AVAILABLE_FIELDS as $field}
									{if (!in_array ($field.uitype, array (Field::UI_TYPE_ATTACHMENTS, Field::UI_TYPE_GRID)))}
										<option value="{$field.fieldname}" {if ($availableType == $type) && ($value == $field.fieldname)}
											selected="selected" {/if}>{$field.fieldlabel}</option>
									{/if}
								{/foreach}
							</select>
						{elseif ($availableType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_GRID_FIELD)}
							<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$expandedKey}][valueformula]"
								class="form-control parametervalue" title="" data-type="{$availableType}" {if ($availableType != $type)}
									disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
								onchange="BackgroundTasksUtils.refreshFields (this);" {/if}>
								<option value=""></option>
								{foreach $AVAILABLE_GRID_FIELDS as $gridName => $gridFields}
									{foreach $gridFields as $gridField}
										{assign var='selectedField' value=$gridName|cat:'.'|cat:$gridField.name}
										<option value="{$gridName}.{$gridField.name}"
											{if ($availableType eq $type) && ($value eq $selectedField)} selected="selected" {/if}>
											{$gridField.gridlabel}.{$gridField.label}</option>
									{/foreach}
								{/foreach}
							</select>
						{elseif ($availableType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_VARIABLE)}
							<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$expandedKey}][valueformula]"
								class="form-control parametervalue" title="" data-type="{$availableType}" {if ($availableType != $type)}
									disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
								onchange="BackgroundTasksUtils.refreshFields (this);" {/if}>
								<option value=""></option>
								{foreach $SYSTEM_VARIABLES as $variableName => $variableLabel}
									{if
																					((in_array ($uiType, array (Field::UI_TYPE_MODIFIED_BY, Field::UI_TYPE_OWNER))) && ($SYSTEM_VARIABLE_TYPES[$variableName] != 'USER')) ||
																					((in_array ($uiType, array (Field::UI_TYPE_EMAIL))) && ($SYSTEM_VARIABLE_TYPES[$variableName] != 'EMAIL')) ||
																					((in_array ($uiType, array (Field::UI_TYPE_MODULE_RECORDS, Field::UI_TYPE_MODULE_REFERENCE))) && ($SYSTEM_VARIABLE_TYPES[$variableName] != 'RECORD')) ||
																					((in_array ($uiType, array (Field::UI_TYPE_DATE, Field::UI_TYPE_DATETIME))) && ($SYSTEM_VARIABLE_TYPES[$variableName] != 'DATE'))
																				}
									{continue}
								{/if}
								{assign var='dummy' value='{'|cat: $variableName : '}'}
								<option value="{$dummy}" {if ($value == $dummy)} selected="selected" {/if}>{$variableLabel}</option>
							{/foreach}
						</select>
					{elseif ($availableType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_PREVIOUS_OUTPUT)}
						<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$expandedKey}][valueformula]"
							class="form-control parametervalue previousoutput" title="" data-type="{$availableType}"
							{if ($availableType != $type)} disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
						onchange="BackgroundTasksUtils.refreshFields (this);" {/if}>
						<option value=""></option>
						{if (!empty ($TASK_ACTIONS))}
							{foreach $TASK_ACTIONS as $taskAction}
								{if ($taskAction->getName () == $ACTION->getName ())}
									{break}
								{/if}
								<option value="{$taskAction->getName ()}"
									{if ($availableType == $type) && ($valueFormula == $taskAction->getName ())} selected="selected"
									{/if}>{$taskAction->getName ()}</option>
							{/foreach}
						{/if}
					</select>
				{elseif ($availableType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_FORMULA)}
					<input type="text"
						name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$expandedKey}][valueformula]"
						value="{if ($availableType == $type)}{$value|escape: 'html'}{/if}" class="form-control parametervalue"
						placeholder="" data-type="{$availableType}" {if ($availableType != $type)} disabled="disabled"
							style="display: none;" {/if}{if ($refreshOnChanges)}
						onchange="BackgroundTasksUtils.refreshFields (this);" {/if} />
				{elseif ($availableType != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CALCULATED_DATE)}
					<input type="text"
						name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$expandedKey}][valueformula]"
						value="{if ($availableType == $type)}{$value|escape: 'html'}{/if}"
						class="form-control parametervalue{if (in_array ($uiType, array (Field::UI_TYPE_DATE, Field::UI_TYPE_DATETIME)))} date{/if}"
						placeholder="" data-type="{$availableType}" {if ($availableType != $type)} disabled="disabled"
							style="display: none;" {/if}{if ($refreshOnChanges)}
						onchange="BackgroundTasksUtils.refreshFields (this);" {/if} />
				{/if}
			{/foreach}
		</div>
	</div>
	{/foreach}
</div>
{/if}
{/strip}