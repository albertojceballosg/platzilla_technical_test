{strip}
	{assign var='fieldsData' value=$PARAMETER->getDefaultOptions ()}
	{assign var='refreshOnChanges' value=$PARAMETER->refreshOnChanges ()}
	{assign var='translationModule' value=$PARAMETER->getTranslationModule ()}
	{assign var='types' value=$PARAMETER->getType ()}
	{assign var='valueFormulas' value=$PARAMETER->getValueFormula ()}
	{if (!empty ($fieldsData))}
		<div class="col-xs-12">
			<label>{$MOD[$parameterName]}</label>
			{foreach $fieldsData as $fieldName => $fieldData}
				{if (!empty ($fieldData['attributes']['uitype']))}
					{assign var='uiType' value=$fieldData['attributes']['uitype']}
				{else}
					{assign var='uiType' value=null}
				{/if}
				{assign var='type' value=$types[$fieldName]}
				{assign var='value' value=$valueFormulas[$fieldName]}
				<div class="row parameter">
					<div class="form-group col-xs-12 col-md-3">
						<input type="text"
							value="{if !empty($fieldData['label'])}{$fieldData['label']|getTranslatedString}{else}{$fieldName|getTranslatedString}{/if}"
							class="form-control parametername" placeholder="" disabled="disabled" />
					</div>
					<div class="form-group col-xs-12 col-md-3">
						<select id="{$parameterName}-{$ACTION_SEQUENCE}"
							name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$fieldName}][type]"
							class="form-control parametertype" title="" data-uitype="{$uiType}@{$ACTION_SEQUENCE}"
							onchange="BackgroundTasksUtils.setParameterValue (this);">
							<option value=""></option>
							<option value="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD}"
								{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD)}
								selected="selected" {/if}>
								{$MOD[BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD]}</option>
							{if (in_array ($uiType, array (Field::UI_TYPE_EMAIL)))}
								<option value="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL}"
									{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL)}
									selected="selected" {/if}>Direcciones de correo separadas por comas</option>
							{elseif (!in_array ($uiType, array (Field::UI_TYPE_MODULE_REFERENCE)))}
								<option value="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL}"
									{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL)}
									selected="selected" {/if}>Indica el valor</option>
							{/if}
							{if (!in_array ($uiType, array (Field::UI_TYPE_CURRENCY, Field::UI_TYPE_GLOBAL_PICKLIST, Field::UI_TYPE_MULTI_SELECT, Field::UI_TYPE_NUMBER, Field::UI_TYPE_PERCENTAGE,Field::UI_TYPE_PICKLIST)))}
								<option value="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_VARIABLE}"
									{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_VARIABLE)}
									selected="selected" {/if}>Otras opciones</option>
							{/if}
							{if (in_array ($uiType, array (Field::UI_TYPE_DATE, Field::UI_TYPE_DATETIME)))}
								<option value="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_FORMULA}"
									{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_FORMULA)}
									selected="selected" {/if}>
									{$MOD[BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_FORMULA]}</option>
							{/if}
							<option value="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CUSTOM_SQL}"
								{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CUSTOM_SQL)}
									selected="selected" {/if} data-scope="SYSTEM" {if ($TASK_SCOPE != BackgroundTask::SCOPE_SYSTEM)}
								style="display: none;" {/if}>
								{$MOD[BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CUSTOM_SQL]}</option>
						</select>
					</div>
					<div class="form-group col-xs-12 col-md-6 field-container">
						{if (in_array ($uiType, array (Field::UI_TYPE_MODIFIED_BY, Field::UI_TYPE_OWNER)))}
							{assign var='availableOwnerFields' value=array()}
							{foreach $AVAILABLE_FIELDS as $field}
								{if (in_array ($field.uitype, array (Field::UI_TYPE_MODIFIED_BY, Field::UI_TYPE_OWNER)))}
									{$availableOwnerFields[] = $field}
								{/if}
							{/foreach}
							<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$fieldName}][valueformula]"
								class="form-control parametervalue" title=""
								data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD}"
								{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD)}
									disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
								onchange="BackgroundTasksUtils.refreshFields (this);" {/if}>
								{if (!empty ($availableOwnerFields))}
									<option value=""></option>
									{foreach $availableOwnerFields as $field}
										<option value="{$field.fieldname}"
											{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD) && ($value == $field.fieldname)}
											selected="selected" {/if}>{$field.fieldlabel}</option>
									{/foreach}
								{else}
									<option value="">Imposible ejecutar la acción: el módulo {$SELECTED_MODULE_NAME} no tiene campos de
										usuario</option>
								{/if}
							</select>
							<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$fieldName}][valueformula]"
								class="form-control parametervalue users" title=""
								data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL}"
								{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL)}
									disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
								onchange="BackgroundTasksUtils.refreshFields (this);" {/if}>
								{if (!empty ($AVAILABLE_USERS))}
									<option value=""></option>
									{foreach $AVAILABLE_USERS as $availableUser}
										<option value="{$availableUser->getId ()}" {if ($valueFormula == $availableUser->getId ())}
											selected="selected" {/if}>{trim("{$availableUser->getFirstName ()}
					{$availableUser->getLastName ()}")}</option>
									{/foreach}
								{else}
									<option value="">Imposible ejecutar la acción: no hay usuarios registrados en el sistema</option>
								{/if}
							</select>
							{assign var='availableOwnerVariables' value=array()}
							{foreach $SYSTEM_VARIABLES as $variableName => $variableLabel}
								{if ($SYSTEM_VARIABLE_TYPES[$variableName] == 'USER')}
									{$availableOwnerVariables[$variableName] = $variableLabel}
								{/if}
							{/foreach}
							<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$fieldName}][valueformula]"
								class="form-control parametervalue" title=""
								data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_VARIABLE}"
								{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_VARIABLE)}
									disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
								onchange="BackgroundTasksUtils.refreshFields (this);" {/if}>
								{if (!empty ($availableOwnerVariables))}
									<option value=""></option>
									{foreach $availableOwnerVariables as $variableName => $variableLabel}
										{assign var='dummy' value='{'|cat: $variableName : '}'}
										<option value="{$dummy}" {if ($value == $dummy)} selected="selected" {/if}>{$variableLabel}</option>
									{/foreach}
								{else}
									<option value="">Imposible ejecutar la acción: no hay opciones adicionales</option>
								{/if}
							</select>
						{elseif (in_array ($uiType, array (Field::UI_TYPE_GLOBAL_PICKLIST, Field::UI_TYPE_MULTI_SELECT, Field::UI_TYPE_PICKLIST)))}
							{assign var='availablePicklistFields' value=array()}
							{foreach $AVAILABLE_FIELDS as $field}
								{if (in_array ($field.uitype, array (Field::UI_TYPE_GLOBAL_PICKLIST, Field::UI_TYPE_MULTI_SELECT, Field::UI_TYPE_PICKLIST)))}
									{$availablePicklistFields[] = $field}
								{/if}
							{/foreach}
							<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$fieldName}][valueformula]"
								class="form-control parametervalue" title=""
								data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD}"
								{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD)}
									disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
								onchange="BackgroundTasksUtils.refreshFields (this);" {/if}>
								{if (!empty ($availablePicklistFields))}
									<option value=""></option>
									{foreach $availablePicklistFields as $field}
										<option value="{$field.fieldname}"
											{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD) && ($value == $field.fieldname)}
											selected="selected" {/if}>{$field.fieldlabel}</option>
									{/foreach}
								{else}
									<option value="">Imposible ejecutar la acción: el módulo {$SELECTED_MODULE_NAME} no tiene campos de
										listas de valores</option>
								{/if}
							</select>
							<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$fieldName}][valueformula]"
								class="form-control parametervalue" title=""
								data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL}"
								{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL)}
									disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
								onchange="BackgroundTasksUtils.refreshFields (this);" {/if}>
								{if (!empty ($AVAILABLE_PICKLIST_VALUES[$ACTION_SEQUENCE][$fieldName]))}
									<option value=""></option>
									{foreach $AVAILABLE_PICKLIST_VALUES[{$ACTION_SEQUENCE}][$fieldName] as $availablePicklistValue}
										<option value="{$availablePicklistValue}"
											{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL) && ($value|html_entity_decode == $availablePicklistValue|html_entity_decode)}
											selected="selected" {/if}>{$availablePicklistValue}</option>
									{/foreach}
								{else}
									<option value="">Imposible ejecutar la acción: el módulo {$SELECTED_MODULE_NAME} no tiene valores
										configurados para el campo</option>
								{/if}
							</select>
							<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$fieldName}][valueformula]"
								class="form-control parametervalue" title=""
								data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_VARIABLE}"
								{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_VARIABLE)}
									disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
								onchange="BackgroundTasksUtils.refreshFields (this);" {/if}>
								{if (!empty ($SYSTEM_VARIABLES))}
									<option value=""></option>
									{foreach $SYSTEM_VARIABLES as $variableName => $variableLabel}
										{if ($SYSTEM_VARIABLE_TYPES[$variableName] == 'SYSTEM')}
											{continue}
										{/if}
										{assign var='dummy' value='{'|cat: $variableName : '}'}
										<option value="{$dummy}" {if ($value == $dummy)} selected="selected" {/if}>{$variableLabel}</option>
									{/foreach}
								{else}
									<option value="">Imposible ejecutar la acción: no hay opciones adicionales</option>
								{/if}
							</select>
						{elseif (in_array ($uiType, array (Field::UI_TYPE_MODULE_REFERENCE)))}
							{assign var='availableModuleReferenceFields' value=array()}
							{foreach $AVAILABLE_FIELDS as $field}
								{if (in_array ($field.uitype, array (Field::UI_TYPE_MODULE_REFERENCE)))}
									{$availableModuleReferenceFields[] = $field}
								{/if}
							{/foreach}
							<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$fieldName}][valueformula]"
								class="form-control parametervalue" title=""
								data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD}"
								{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD)}
									disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
								onchange="BackgroundTasksUtils.refreshFields (this);" {/if}>
								{if (!empty ($availableModuleReferenceFields))}
									<option value=""></option>
									<option value="record_id" {if ($value == 'record_id')} selected="selected" {/if}>(El registro que se
										está procesando)</option>
									{foreach $availableModuleReferenceFields as $field}
										<option value="{$field.fieldname}"
											{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD) && ($value == $field.fieldname)}
											selected="selected" {/if}>{$field.fieldlabel}</option>
									{/foreach}
								{else}
									<option value="">Imposible ejecutar la acción: el módulo {$SELECTED_MODULE_NAME} no tiene campos de
										referencia a módulo</option>
								{/if}
							</select>
							{assign var='availableModuleReferenceVariables' value=array()}
							{foreach $SYSTEM_VARIABLES as $variableName => $variableLabel}
								{if ($SYSTEM_VARIABLE_TYPES[$variableName] == 'RECORD')}
									{$availableModuleReferenceVariables[$variableName] = $variableLabel}
								{/if}
							{/foreach}
							<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$fieldName}][valueformula]"
								class="form-control parametervalue" title=""
								data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_VARIABLE}"
								{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_VARIABLE)}
									disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
								onchange="BackgroundTasksUtils.refreshFields (this);" {/if}>
								{if (!empty ($availableModuleReferenceVariables))}
									<option value=""></option>
									{foreach $availableModuleReferenceVariables as $variableName => $variableLabel}
										{assign var='dummy' value='{'|cat: $variableName : '}'}
										<option value="{$dummy}" {if ($value == $dummy)} selected="selected" {/if}>{$variableLabel}</option>
									{/foreach}
								{else}
									<option value="">Imposible ejecutar la acción: el módulo {$SELECTED_MODULE_NAME} no tiene opciones
										adicionales de referencia a módulo</option>
								{/if}
							</select>
						{elseif (in_array ($uiType, array (Field::UI_TYPE_DATE, Field::UI_TYPE_DATETIME)))}
							<input type="text"
								name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$fieldName}][valueformula]"
								value="{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL)}{$value|escape: 'html'}{/if}"
								class="form-control parametervalue date" readonly="readonly" placeholder=""
								data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL}"
								{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL)}
									disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
								onchange="BackgroundTasksUtils.refreshFields (this);" {/if} />
							{assign var='availableDateFields' value=array()}
							{foreach $AVAILABLE_FIELDS as $field}
								{if (in_array ($field.uitype, array (Field::UI_TYPE_DATE, Field::UI_TYPE_DATETIME)))}
									{$availableDateFields[] = $field}
								{/if}
							{/foreach}
							<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$fieldName}][valueformula]"
								class="form-control parametervalue" title=""
								data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD}"
								{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD)}
									disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
								onchange="BackgroundTasksUtils.refreshFields (this);" {/if}>
								{if (!empty ($availableDateFields))}
									<option value=""></option>
									{foreach $availableDateFields as $field}
										<option value="{$field.fieldname}"
											{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD) && ($value == $field.fieldname)}
											selected="selected" {/if}>{$field.fieldlabel}</option>
									{/foreach}
								{else}
									<option value="">Imposible ejecutar la acción: el módulo {$SELECTED_MODULE_NAME} no tiene campos de
										fecha</option>
								{/if}
							</select>
							{assign var='availableDateVariables' value=array()}
							{foreach $SYSTEM_VARIABLES as $variableName => $variableLabel}
								{if ($SYSTEM_VARIABLE_TYPES[$variableName] == 'DATE')}
									{$availableDateVariables[$variableName] = $variableLabel}
								{/if}
							{/foreach}
							<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$fieldName}][valueformula]"
								class="form-control parametervalue" title=""
								data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_VARIABLE}"
								{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_VARIABLE)}
									disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
								onchange="BackgroundTasksUtils.refreshFields (this);" {/if}>
								{if (!empty ($availableDateVariables))}
									<option value=""></option>
									{foreach $availableDateVariables as $variableName => $variableLabel}
										{assign var='dummy' value='{'|cat: $variableName : '}'}
										<option value="{$dummy}" {if ($value == $dummy)} selected="selected" {/if}>{$variableLabel}</option>
									{/foreach}
								{else}
									<option value="">Imposible ejecutar la acción: el módulo {$SELECTED_MODULE_NAME} no tiene opciones
										adicionales de fecha</option>
								{/if}
							</select>
							<div class="variable"
								style="position: relative; display: {if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_FORMULA)}inline-block{else}none{/if}; width: 100%;">
								<textarea name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$fieldName}][valueformula]"
									class="form-control parametervalue formula-input" placeholder="Ej: |date_start| + 7 días"
									data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_FORMULA}" rows="1"
									style="padding-right: 35px;"
									{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_FORMULA)}
										disabled="disabled" {/if}{if ($refreshOnChanges)}
										onchange="BackgroundTasksUtils.refreshFields (this);"
										{/if}>{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_FORMULA)}{$value|escape: 'html'}{/if}</textarea>
									<span
										style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; z-index: 10;"
										data-modulename="{$SELECTED_MODULE_NAME}" data-fields='{$availableDateFields|json_encode}'
										data-variables='{$availableDateVariables|json_encode}'
										onclick="BackgroundTasksUtils.showFormulaHelpModal(jQuery(this).data('modulename'), jQuery(this).data('fields'), jQuery(this).data('variables'))"
										title="Ver ayuda de fórmulas de fecha">
										<i class="fa fa-question-circle" style="color: #5bc0de; font-size: 14px;"></i>
									</span>
								</div>
							{elseif (in_array ($uiType, array (Field::UI_TYPE_CURRENCY, Field::UI_TYPE_NUMBER, Field::UI_TYPE_PERCENTAGE)))}
								<input type="number"
									name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$fieldName}][valueformula]"
									value="{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL)}{$value|escape: 'html'}{/if}"
									class="form-control parametervalue number" step="0.01" placeholder=""
									data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL}"
									{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL)}
										disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
									onchange="BackgroundTasksUtils.refreshFields (this);" {/if} />
								{assign var='availableNumberFields' value=array()}
								{foreach $AVAILABLE_FIELDS as $field}
									{if (in_array ($field.uitype, array (Field::UI_TYPE_CURRENCY, Field::UI_TYPE_NUMBER, Field::UI_TYPE_PERCENTAGE)))}
										{$availableNumberFields[] = $field}
									{/if}
								{/foreach}
								<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$fieldName}][valueformula]"
									class="form-control parametervalue" title=""
									data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD}"
									{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD)}
										disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
									onchange="BackgroundTasksUtils.refreshFields (this);" {/if}>
									{if (!empty ($availableNumberFields))}
										<option value=""></option>
										{foreach $availableNumberFields as $field}
											<option value="{$field.fieldname}"
												{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD) && ($value == $field.fieldname)}
												selected="selected" {/if}>{$field.fieldlabel}</option>
										{/foreach}
									{else}
										<option value="">Imposible ejecutar la acción: el módulo {$SELECTED_MODULE_NAME} no tiene campos
											numéricos</option>
									{/if}
								</select>
							{elseif (in_array ($uiType, array (Field::UI_TYPE_EMAIL)))}
								<input type="text"
									name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$fieldName}][valueformula]"
									value="{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL)}{$value|escape: 'html'}{/if}"
									class="form-control parametervalue" placeholder=""
									data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL}"
									{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL)}
										disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
									onchange="BackgroundTasksUtils.refreshFields (this);" {/if} />
								{assign var='availableEmailFields' value=array()}
								{foreach $AVAILABLE_FIELDS as $field}
									{if (in_array ($field.uitype, array (Field::UI_TYPE_EMAIL)))}
										{$availableEmailFields[] = $field}
									{/if}
								{/foreach}
								<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$fieldName}][valueformula]"
									class="form-control parametervalue" title=""
									data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD}"
									{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD)}
										disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
									onchange="BackgroundTasksUtils.refreshFields (this);" {/if}>
									{if (!empty ($availableEmailFields))}
										<option value=""></option>
										{foreach $availableEmailFields as $field}
											<option value="{$field.fieldname}"
												{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD) && ($value == $field.fieldname)}
												selected="selected" {/if}>{$field.fieldlabel}</option>
										{/foreach}
									{else}
										<option value="">Imposible ejecutar la acción: el módulo {$SELECTED_MODULE_NAME} no tiene campos de
											correo</option>
									{/if}
								</select>
								{assign var='availableEmailVariables' value=array()}
								{foreach $SYSTEM_VARIABLES as $variableName => $variableLabel}
									{if ($SYSTEM_VARIABLE_TYPES[$variableName] == 'EMAIL')}
										{$availableEmailVariables[$variableName] = $variableLabel}
									{/if}
								{/foreach}
								<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$fieldName}][valueformula]"
									class="form-control parametervalue" title=""
									data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_VARIABLE}"
									{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_VARIABLE)}
										disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
									onchange="BackgroundTasksUtils.refreshFields (this);" {/if}>
									{if (!empty ($availableEmailVariables))}
										<option value=""></option>
										{foreach $availableEmailVariables as $variableName => $variableLabel}
											{assign var='dummy' value='{'|cat: $variableName : '}'}
											<option value="{$dummy}" {if ($value == $dummy)} selected="selected" {/if}>{$variableLabel}</option>
										{/foreach}
									{else}
										<option value="">Imposible ejecutar la acción: el módulo {$SELECTED_MODULE_NAME} no tiene opciones
											adicionales de correo</option>
									{/if}
								</select>
							{elseif ($SELECTED_PARAMETER_VALUES['modulename'] != 'Calendar' && in_array ($uiType, array (Field::UI_TYPE_TEXT, Field::UI_TYPE_TEXTAREA)))}
								{assign var='availableTextFields' value=array()}
								{foreach $AVAILABLE_FIELDS as $field}
									{if (!in_array ($field.uitype, array (Field::UI_TYPE_MODIFIED_BY, Field::UI_TYPE_OWNER)))}
										{$availableTextFields[] = $field}
									{/if}
								{/foreach}
								{assign var='availableTextVariables' value=array()}
								{foreach $SYSTEM_VARIABLES as $variableName => $variableLabel}
									{if (!in_array ($SYSTEM_VARIABLE_TYPES[$variableName], array ('SYSTEM', 'USER'))) && (!in_array ($variableName, array ('ASSIGNED_USER_FULLNAME', 'CURRENT_USER_FULLNAME')))}
										{$availableTextVariables[$variableName] = $variableLabel}
									{/if}
								{/foreach}
								<div class="variable"
									style="position: relative; display: {if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL)}inline-block{else}none{/if}; width: 100%;">
									<input type="text"
										name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$fieldName}][valueformula]"
										value="{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL)}{$value|escape: 'html'}{/if}"
										class="form-control parametervalue" placeholder=""
										data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL}"
										style="padding-right: 35px;"
										{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL)}
											disabled="disabled" {/if}{if ($refreshOnChanges)}
										onchange="BackgroundTasksUtils.refreshFields (this);" {/if} />
									<span
										style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; z-index: 10;"
										data-modulename="{$SELECTED_MODULE_NAME}" data-fields='{$availableTextFields|json_encode}'
										data-variables='{$availableTextVariables|json_encode}'
										onclick="BackgroundTasksUtils.showConcatHelpModal(jQuery(this).data('modulename'), jQuery(this).data('fields'), jQuery(this).data('variables'))"
										title="Ver variables disponibles para concatenación">
										<i class="fa fa-question-circle" style="color: #5bc0de; font-size: 14px;"></i>
									</span>
								</div>
								<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$fieldName}][valueformula]"
									class="form-control parametervalue" title=""
									data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD}"
									{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD)}
										disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
									onchange="BackgroundTasksUtils.refreshFields (this);" {/if}>
									{if (!empty ($availableTextFields))}
										<option value=""></option>
										{foreach $availableTextFields as $field}
											<option value="{$field.fieldname}"
												{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD) && ($value == $field.fieldname)}
												selected="selected" {/if}>{$field.fieldlabel}</option>
										{/foreach}
									{else}
										<option value="">Imposible ejecutar la acción: el módulo {$SELECTED_MODULE_NAME} no tiene campos de
											texto</option>
									{/if}
								</select>
								{assign var='availableTextVariables' value=array()}
								{foreach $SYSTEM_VARIABLES as $variableName => $variableLabel}
									{if (!in_array ($SYSTEM_VARIABLE_TYPES[$variableName], array ('SYSTEM', 'USER'))) && (!in_array ($variableName, array ('ASSIGNED_USER_FULLNAME', 'CURRENT_USER_FULLNAME')))}
										{$availableTextVariables[$variableName] = $variableLabel}
									{/if}
								{/foreach}
								<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$fieldName}][valueformula]"
									class="form-control parametervalue" title=""
									data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_VARIABLE}"
									{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_VARIABLE)}
										disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
									onchange="BackgroundTasksUtils.refreshFields (this);" {/if}>
									{if (!empty ($availableTextVariables))}
										<option value=""></option>
										{foreach $availableTextVariables as $variableName => $variableLabel}
											{assign var='dummy' value='{'|cat: $variableName : '}'}
											<option value="{$dummy}" {if ($value == $dummy)} selected="selected" {/if}>{$variableLabel}</option>
										{/foreach}
									{else}
										<option value="">Imposible ejecutar la acción: no hay opciones adicionales</option>
									{/if}
								</select>
							{else}
								<input type="text"
									name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$fieldName}][valueformula]"
									value="{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL)}{$value|escape: 'html'}{/if}"
									class="form-control parametervalue" placeholder=""
									data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL}"
									{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL)}
										disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
									onchange="BackgroundTasksUtils.refreshFields (this);" {/if} />
								{assign var='availableTextFields' value=array()}
								{foreach $AVAILABLE_FIELDS as $field}
									{if (!in_array ($field.uitype, array (Field::UI_TYPE_MODIFIED_BY, Field::UI_TYPE_OWNER)))}
										{$availableTextFields[] = $field}
									{/if}
								{/foreach}
								<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$fieldName}][valueformula]"
									class="form-control parametervalue" title=""
									data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD}"
									{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD)}
										disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
									onchange="BackgroundTasksUtils.refreshFields (this);" {/if}>
									{if (!empty ($availableTextFields))}
										<option value=""></option>
										{foreach $availableTextFields as $field}
											<option value="{$field.fieldname}"
												{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD) && ($value == $field.fieldname)}
												selected="selected" {/if}>{$field.fieldlabel}</option>
										{/foreach}
									{else}
										<option value="">Imposible ejecutar la acción: el módulo {$SELECTED_MODULE_NAME} no tiene campos de
											texto</option>
									{/if}
								</select>
								{assign var='availableTextVariables' value=array()}
								{foreach $SYSTEM_VARIABLES as $variableName => $variableLabel}
									{if (!in_array ($SYSTEM_VARIABLE_TYPES[$variableName], array ('SYSTEM', 'USER'))) && (!in_array ($variableName, array ('ASSIGNED_USER_FULLNAME', 'CURRENT_USER_FULLNAME')))}
										{$availableTextVariables[$variableName] = $variableLabel}
									{/if}
								{/foreach}
								<select name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$fieldName}][valueformula]"
									class="form-control parametervalue" title=""
									data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_VARIABLE}"
									{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_VARIABLE)}
										disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
									onchange="BackgroundTasksUtils.refreshFields (this);" {/if}>
									{if (!empty ($availableTextVariables))}
										<option value=""></option>
										{foreach $availableTextVariables as $variableName => $variableLabel}
											{assign var='dummy' value='{'|cat: $variableName : '}'}
											<option value="{$dummy}" {if ($value == $dummy)} selected="selected" {/if}>{$variableLabel}</option>
										{/foreach}
									{else}
										<option value="">Imposible ejecutar la acción: no hay opciones adicionales</option>
									{/if}
								</select>
							{/if}
							<textarea name="actions[{$ACTION_SEQUENCE}][parameters][{$parameterName}][{$fieldName}][valueformula]"
								class="form-control parametervalue" placeholder=""
								data-type="{BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CUSTOM_SQL}"
								{if ($type != BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CUSTOM_SQL)}
									disabled="disabled" style="display: none;" {/if}{if ($refreshOnChanges)}
									onchange="BackgroundTasksUtils.refreshFields (this);"
									{/if}>{if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CUSTOM_SQL)}{$value|escape: 'html'}{/if}</textarea>
							</div>
						</div>
					{/foreach}
				</div>
			{/if}
		{/strip}