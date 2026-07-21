{strip}
<div class="table-responsive">
	<table width="100%" border="0" cellspacing="0" cellpadding="5" class="table">
		<tr>
			<th nowrap>
				<strong>{$SELMODULE|@getTranslatedString} {$MOD.LBL_MODULE_NUMBERING}</strong>
			</th>
			<td width="100%">
				<b>
{if ($MODE == 'UPDATESETTINGS')}
	{if ($IS_ERROR)}
					<span style="color: red">{$MOD.LBL_UPDATE} {$MOD.LBL_FAILED}</span> {$RECORD_PREFIX}{$RECORD_NUMBER} {$MOD.LBL_IN_USE}
	{else}
					<span style="color: green">{$MOD.LBL_UPDATE} {$MOD.LBL_DONE}</span>
	{/if}
{elseif ($MODE == 'UPDATEBULKEXISTING')}
					<span style="color: {if ($TOTAL_RECORDS != $UPDATED_RECORDS)}red{else}green{/if};">{$MOD.LBL_TOTAL} {$TOTAL_RECORDS}, {$MOD.LBL_UPDATE} {$MOD.LBL_DONE}: {$UPDATED_RECORDS}</span>
{/if}
				</b>
			</td>
			<td width="80%" nowrap align="right">
				<b>{$MOD.LBL_MODULE_NUMBERING_FIX_MISSING}</b>
				<input type="button" class="btn btn-primary" value="{$APP.LBL_APPLY_BUTTON_LABEL}" onclick="updateModEntityExisting (this, this.form);" />
			</td>
		</tr>
		<tr>
			<th width="20%" nowrap><strong>{$MOD.LBL_USE_PREFIX}</strong></th>
			<td width="80%" colspan="2">
				<input type="text" name="recprefix" style="width:30%" value="{$RECORD_PREFIX}" placeholder="" />
			</td>
		</tr>
		<tr>
			<th width="20%" nowrap class="small cellLabel">
				<strong>{$MOD.LBL_START_SEQ} <span style="color: red;">*</span></strong>
			</th>
			<td width="80%" colspan=2>
				<input type="text" name="recnumber" style="width:30%" value="{$RECORD_NUMBER}" placeholder="" />
			</td>
		</tr>
		<tr>
			<td width="20%" nowrap colspan="3" align="center">
				<input type="button" name="Button" class="btn btn-primary" value="{$APP.LBL_SAVE_BUTTON_LABEL}" onclick="updateModEntityNoSetting (this, this.form);" />
				<input type="button" name="Button" class="btn btn-cancel" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" onclick="history.back ();" />
			</td>
		</tr>
	</table>
</div>
{/strip}