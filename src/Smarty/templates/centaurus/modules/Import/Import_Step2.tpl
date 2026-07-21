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
	<div class="form-group">
		<label for="auto_merge">{'LBL_IMPORT_STEP_3_DESCRIPTION'|@getTranslatedString:$MODULE}</label>
		<div class="checkbox-nice">
			<input type="checkbox" id="auto_merge" name="auto_merge" onclick="ImportJs.toogleMergeConfiguration();">
			<label for="auto_merge">
				{'LBL_IMPORT_STEP_3_DESCRIPTION_DETAILED'|@getTranslatedString:$MODULE}
			</label>
		</div>
	</div>
</div>
<div class="col-lg-12">
	<div class="table-responsive">
		
		<table class="table" id="duplicates_merge_configuration" style="display:none;">
			<tr>
				<td width="50%">
					{'LBL_SPECIFY_MERGE_TYPE'|@getTranslatedString:$MODULE}
				</td>
				<td>
					<select name="merge_type" id="merge_type" class="form-control">
						{foreach key=_MERGE_TYPE item=_MERGE_TYPE_LABEL from=$AUTO_MERGE_TYPES}
						<option value="{$_MERGE_TYPE}">{$_MERGE_TYPE_LABEL|@getTranslatedString:$MODULE}</option>
						{/foreach}
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2">{'LBL_SELECT_MERGE_FIELDS'|@getTranslatedString:$MODULE}</td>
			</tr>
			<tr>
				<td colspan="2">
					<div class="form-group col-xs-5">
						<label for="import_file">{'LBL_AVAILABLE_FIELDS'|@getTranslatedString:$MODULE}</label>
						<select id="available_fields" multiple size="10" name="available_fields" class="form-control">
							{foreach key=_FIELD_NAME item=_FIELD_INFO from=$AVAILABLE_FIELDS}
							<option value="{$_FIELD_NAME}">{$_FIELD_INFO->getFieldLabelKey()|@getTranslatedString:$FOR_MODULE}</option>
							{/foreach}
						</select>
					</div>
					<div class="form-group col-xs-2">
						<div class="icon-box" style="margin-left: 24%;margin-top: 75%;">
							<a href="javascript:void(0)" name="Button" class="btn pull-left" onClick="removeSelectedOptions('selected_merge_fields')">
								<i class="fa fa-chevron-left"></i>
							</a>
							<a href="javascript:void(0)" name="Button1" class="btn pull-left" onClick="copySelectedOptions('available_fields', 'selected_merge_fields')">
								<i class="fa fa-chevron-right"></i>
							</a>
						</div>
					</div>
					<div class="form-group col-xs-5">
						<label for="import_file">{'LBL_SELECTED_FIELDS'|@getTranslatedString:$MODULE}</label>
						<input type="hidden" id="merge_fields" size="10" name="merge_fields" value="" />
						<select id="selected_merge_fields" size="10" name="selected_merge_fields" multiple class="form-control">
							{foreach key=_FIELD_NAME item=_FIELD_INFO from=$ENTITY_FIELDS}
							<option value="{$_FIELD_NAME}">{$_FIELD_INFO->getFieldLabelKey()|@getTranslatedString:$FOR_MODULE}</option>
							{/foreach}
						</select>
					</div>
					
				</td>
			</tr>
		</table>
		
	</div>
</div>

<div class="col-lg-12">
	<div class="pull-right">
		<button type="button" class="btn btn-warning btn-mini btn-prev"> <i class="icon-arrow-left"></i>Atras</button>
		<button type="submit" class="btn btn-success btn-mini" onclick="return ImportJs.uploadAndParse();">Siguiente<i class="icon-arrow-right"></i></button>
	</div>
</div>
