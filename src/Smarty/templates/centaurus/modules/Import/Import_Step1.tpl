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

<input type="hidden" name="type" value="csv" />
<input type="hidden" name="is_scheduled" value="1" />

<div class="col-lg-6">
	<div class="form-group">
		<label for="import_file">{'LBL_IMPORT_STEP_1_DESCRIPTION'|@getTranslatedString:$MODULE}</label>
		<input type="file" class="form-control" name="import_file" id="import_file" onchange="ImportJs.checkFileType()" >
		<span class="help-block">{'LBL_IMPORT_SUPPORTED_FILE_TYPES'|@getTranslatedString:$MODULE}</span>
	</div>
	<div class="form-group">
		<div class="checkbox-nice">
			<input type="checkbox" id="has_header" name="has_header" checked />
			<label for="has_header">{'LBL_HAS_HEADER'|@getTranslatedString:$MODULE}</label>
		</div>
	</div>
</div>
<div class="col-lg-6">
	<div class="form-group">
		<label for="type">{'LBL_FILE_TYPE'|@getTranslatedString:$MODULE}</label>
		<select name="type" id="type" class="form-control" onchange="ImportJs.handleFileTypeChange();">
			{foreach item=_FILE_TYPE from=$SUPPORTED_FILE_TYPES}
			<option value="{$_FILE_TYPE}">{$_FILE_TYPE|@getTranslatedString:$MODULE}</option>
			{/foreach}
		</select>
	</div>
	<div class="form-group">
		<label for="file_encoding">{'LBL_CHARACTER_ENCODING'|@getTranslatedString:$MODULE}</label>
		<select name="file_encoding" id="file_encoding" class="form-control">
			{foreach key=_FILE_ENCODING item=_FILE_ENCODING_LABEL from=$SUPPORTED_FILE_ENCODING}
			<option value="{$_FILE_ENCODING}">{$_FILE_ENCODING_LABEL|@getTranslatedString:$MODULE}</option>
			{/foreach}
		</select>
	</div>
	<div class="form-group">
		<label for="delimiter">{'LBL_DELIMITER'|@getTranslatedString:$MODULE}</label>
		<select name="delimiter" id="delimiter" class="form-control">
			{foreach key=_DELIMITER item=_DELIMITER_LABEL from=$SUPPORTED_DELIMITERS}
			<option value="{$_DELIMITER}">{$_DELIMITER_LABEL|@getTranslatedString:$MODULE}</option>
			{/foreach}
		</select>
	</div>
	
</div>
<div class="col-lg-12">
	<div class="pull-right">
		<button type="button" class="btn btn-warning btn-mini" onclick="location.href='index.php?module={$FOR_MODULE}&action=index'"><i class="icon-arrow-left"></i>{'LBL_CANCEL_BUTTON_LABEL'|@getTranslatedString:$MODULE}</button>
		<button type="button" class="btn btn-success btn-mini btn-next">{'LBL_NEXT_BUTTON_LABEL'|@getTranslatedString:$MODULE}<i class="icon-arrow-right"></i></button>
	</div>
</div>
