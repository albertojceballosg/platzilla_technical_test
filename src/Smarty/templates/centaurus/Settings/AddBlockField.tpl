{strip}
<div id="orgLay" style="display: block;" class="layerPopup">
	<script language="javascript" type="text/javascript" src="include/js/customview.js"></script>
	<form action="index.php" method="post" name="addtodb" onsubmit="VtigerJS_DialogBox.block();">
		<input type="hidden" name="module" value="{$CURRENT_MODULE}" />
		<input type="hidden" name="fld_module" value="{$MODULE}" />
		<input type="hidden" name="parenttab" value="{$CURRENT_MODULE}" />
		<input type="hidden" name="action" value="AddBlockFieldToDB" />
		<input type="hidden" name="blockid" id="blockid" value="{$BLOCK_ID}" />
		<input type="hidden" name="tabid" id="tabid" value="{$TAB_ID}" />
		<input type="hidden" name="fieldselect" value="{$FIELD_SELECT}" />
		<input type="hidden" name="column" value="{$COLUMN_NAME}" />
		<input type="hidden" name="mode" id="cfedit_mode" value="{$MODE}" />
		<input type="hidden" name="cfcombo" id="selectedfieldtype" value="">
		<table width="100%" border="0" cellpadding="5" cellspacing="0" class="layerHeadingULine">
			<tr>
{if ($MODE == 'edit')}
				<td width="60%" align="left" class="layerPopupHeading">Edit Field</td>
{else}
				<td width="95%" align="left" class="layerPopupHeading">{$LBL_MOVE_BLOCK_FIELD} {$BLOCK_NAME}</td>
{/if}
				<td width="5%" align="right">
					<a href="javascript:fninvsh('orgLay');"><img src="{$URL_IMAGE_CLOSE}" border="0" align="absmiddle" /></a>
				</td>
			</tr>
		</table>
		<table border="0" cellspacing="0" cellpadding="0" width="95%" align="center">
			<tr>
				<td class="small">
					<table border="0" cellspacing="0" cellpadding="0" width="100%" align="center" bgcolor="white">
						<tr>
{if ($MODE == 'edit')}
							<td>
								<table>
									<tr>
										<td>{$LBL_SELECT_FIELD_TO_MOVE}</td>
									</tr>
									<tr>
										<td>
											<select name="field_assignid[]" style="width: 250px" size="10" multiple="multiple" title="">
{if ($FIELDS != null)}
	{foreach $FIELDS as $field}
												<option value="{$field.fieldid}">{$field.fieldlabel}</option>
	{/foreach}
{/if}
											</select>
										</td>
									</tr>
								</table>
							</td>
{/if}
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<table border="0" cellspacing="0" cellpadding="5" width="100%" class="layerPopupTransport">
			<tr>
				<td align="center">
					<input type="submit" name="save" value=" &nbsp; {$APPLICATION_STRINGS.LBL_ASSIGN_BUTTON_LABEL} &nbsp; " class="crmButton small save" />&nbsp;
					<input type="button" name="cancel" value=" {$APPLICATION_STRINGS.LBL_CANCEL_BUTTON_LABEL} " class="crmButton small cancel" onclick="fninvsh('orgLay')" />
				</td>
			</tr>
		</table>
		<input type="hidden" name="fieldType" id="fieldType" value="{$SELECTED_VALUE}">
	</form>
</div>
{/strip}