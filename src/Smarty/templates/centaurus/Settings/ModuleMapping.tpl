{strip}
<table cellspacing="0" cellpadding="5" border="0" align="center" width="100%" class="crmTable">
	<tr valign="top">
		<td class="lvtCol">{$LBL_CAMPO_PLATAFORMA_HIJA}</td>
		<td class="lvtCol">{$LBL_CAMPO_PLATAFORMA_BASE}</td>
	</tr>
{foreach $SOURCE_FIELDS as $sourceField}
	<tr>
		<td class="crmTableRow small lineOnTop">
			<select name="campoPlatHija[]" id="campoPlatHija{$sourceField@index}" class="form-control" title="" style="width: 150px;">
				<option value="{$sourceField.name}">{$sourceField.label}</option>
			</select>
		</td>
		<td class="crmTableRow small lineOnTop">
			<select name="campoPlatBase[]" id="campoPlatBase{$sourceField@index}" class="form-control" title="">
				<option value="">{$TARGET_PLACEHOLDER}</option>
	{foreach $TARGET_FIELDS as $targetField}
				<option value="{$targetField.name}"{if (isset ($sourceField.related)) && ($targetField.name == $sourceField.related)} selected{/if}>{$targetField.label}</option>
	{/foreach}
			</select>
		</td>
	</tr>
{/foreach}
{/strip}