{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 ********************************************************************************/
-->*}

<div class="col-lg-12">
	<div class="table-responsive">
		<input type="hidden" name="field_mapping" id="field_mapping" value="" />
		<input type="hidden" name="default_values" id="default_values" value="" />
		{assign var="colspan" value=3}
		{if $HAS_HEADER eq true}
			{assign var="colspan" value=$colspan+1}
		{/if}
		<table class="table">
			<tr>
				<td colspan="{$colspan}">
					<div id="savedMapsContainer">
						{include file="modules/Import/Import_Saved_Maps.tpl"}
					</div>
				</td>
			</tr>
			<tr>
				{if $HAS_HEADER eq true}
				<td width="20%"><b>{'LBL_FILE_COLUMN_HEADER'|@getTranslatedString:$MODULE}</b></td>
				{/if}
				<td width="20%"><b>{'LBL_ROW_1'|@getTranslatedString:$MODULE}</b></td>
				<td width="30%"><b>{'LBL_CRM_FIELDS'|@getTranslatedString:$MODULE}</b></td>
				<td width="30%"><b>{'LBL_DEFAULT_VALUE'|@getTranslatedString:$MODULE}</b></td>
			</tr>
			{foreach key=_HEADER_NAME item=_FIELD_VALUE from=$ROW_1_DATA name="headerIterator"}
			{assign var="_COUNTER" value=$smarty.foreach.headerIterator.iteration}
			<tr class="fieldIdentifier" id="fieldIdentifier{$_COUNTER}">
				{if $HAS_HEADER eq true}
				<td>
					<span name="header_name">{$_HEADER_NAME}</span>
				</td>
				{/if}
				<td>
					<span>{$_FIELD_VALUE|@textlength_check}</span>
				</td>
				<td>
					<input type="hidden" name="row_counter" value="{$_COUNTER}" />
					<select name="mapped_fields" class="form-control" onchange="ImportJs.loadDefaultValueWidget('fieldIdentifier{$_COUNTER}')">
						<option value="">{'LBL_NONE'|@getTranslatedString:$FOR_MODULE}</option>
						{foreach key=_FIELD_NAME item=_FIELD_INFO from=$AVAILABLE_FIELDS}
						{assign var="_TRANSLATED_FIELD_LABEL" value=$_FIELD_INFO->getFieldLabelKey()|@getTranslatedString:$FOR_MODULE}
						<option value="{$_FIELD_NAME}" {if $_HEADER_NAME eq $_TRANSLATED_FIELD_LABEL} selected {/if} >
							{$_TRANSLATED_FIELD_LABEL}
							{if $_FIELD_INFO->isMandatory() eq 'true'}&nbsp; (*){/if}
						</option>
						{/foreach}
					</select>
				</td>
				<td name="default_value_container"></td>
			</tr>
			{/foreach}
			<tr>
				<td colspan="{$colspan}">
					<div class="form-group">
						<label for="save_map_as">{'LBL_SAVE_AS_CUSTOM_MAPPING'|@getTranslatedString:$MODULE}</label>
						<div class="input-group col-xs-6">
							<span class="input-group-addon">
								<input type="checkbox" name="save_map" id="save_map" />
							</span>
							<input type="text" name="save_map_as" id="save_map_as" class="form-control" />
						</div>
					</div>
				</td>
			</tr>
		</table>
	</div>
</div>

<div class="col-lg-12">
	<div class="pull-right">
		<button type="button" class="btn btn-warning btn-mini" onclick="window.history.back()"><i class="icon-arrow-left"></i>{'LBL_CANCEL_BUTTON_LABEL'|@getTranslatedString:$MODULE}</button>
		<button type="submit" class="btn btn-success btn-mini" onclick="return ImportJs.sanitizeAndSubmit();">Siguiente<i class="icon-arrow-right"></i></button>
	</div>
</div>


<div class="col-lg-12">
	{include file="modules/Import/Import_Default_Values_Widget.tpl"}
</div>