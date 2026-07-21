{strip}
<tr valign="top">
	<td width="45%" class="crmTableRow small lineOnTop">
		<input type="hidden" name="numeroBloque[]" value="{$ROW}" class="block-number" />
		<input type="text" name="nombreBloque[]" value="{$BLOCK_NAME}" class="form-control block-name" maxlength="100" placeholder="" />
	</td>
	<td width="5%" align="left" class="crmTableRow small lineOnTop">
		<select name="visibilidadBloque[]" class="form-control" title="">
			<option value="1"{if ($VISIBILITY == 1)} selected="selected"{/if}>{'LBL_VISIBLE'|@getTranslatedString}</option>
			<option value="0"{if ($VISIBILITY == 0)} selected="selected"{/if}>{'LBL_OCULTO'|@getTranslatedString}</option>
		</select>
	</td>
	<td width="5%" align="left" class="crmTableRow small lineOnTop">
		<input width="16" type="image" height="16" title="Delete" src="themes/images/remove.png" onclick="WizardUtils.deleteBlock (this); return false;" />
	</td>
</tr>
{/strip}