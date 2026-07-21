{strip}
<div class="layerPopup" id="sharingRule">
	<form name="newGroupForm" action="index.php" method="post" onsubmit="VtigerJS_DialogBox.block ();">
		<input type="hidden" name="module" value="Settings" />
		<input type="hidden" name="parenttab" value="Settings" />
		<input type="hidden" name="action" value="SaveSharingRule" />
		<input type="hidden" name="sharing_module" value="{$SHARING_MODULE}" />
		<input type="hidden" name="shareId" value="{$SHARE_ID}" />
		<input type="hidden" name="mode" value="{$MODE}" />
		<input type="hidden" id="rel_module_lists" name="rel_module_lists" value="" />
		<table border="0" cellspacing="0" cellpadding="5" width="100%" class="layerHeadingULine">
			<tr>
				<td class="layerPopupHeading" align="left">{$DISPLAY_MODULE} - {if ($MODE == 'edit')}{$MOD.LBL_EDIT_CUSTOM_RULE}{else}{$MOD.LBL_ADD_CUSTOM_RULE}{/if}</td>
				<td align="right" class="small">
					<img src="{$IMAGE_CLOSE_URL}" border="0" alt="{$APP.LBL_CLOSE}" title="{$APP.LBL_CLOSE}" style="cursor: pointer;" onClick="hide ('sharingRule');" />
				</td>
			</tr>
		</table>
		<table border="0" cellspacing="0" cellpadding="5" width="95%" align="center">
			<tr>
				<td class="small">
					<table border="0" cellspacing="0" cellpadding="5" width="100%" align="center" bgcolor="white">
						<tr>
							<td><b>{$MOD.LBL_STEP} 1 : {$DISPLAY_MODULE} {$APP.LBL_LIST_OF}</b> ({$MOD.LBL_SELECT_ENTITY})</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td style="padding-left:20px; text-align:left;">';
								<select id="{$APP[$SHARING_MODULE]}_share" name="{$SHARING_MODULE}_share" onChange="fnwriteRules ('{APP[$SHARING_MODULE]}', '');" title="">
{foreach $FROM_OPTIONS as $option}
									<option value="{$option.value}{if (isset ($option.selected)) && ($option.selected)}" selected="selected"{/if}>{$option.text}</option>
{/foreach}
								</select>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td colspan="2">&nbsp;</td>
						</tr>
						<tr>
							<td style="text-align: left;">
								<b>{$MOD.LBL_STEP} 2 : {$MOD.LBL_CAN_BE_ACCESSED_BY}</b> ({$MOD.LBL_SELECT_ENTITY})
							</td>
							<td align="left"><b>{$MOD.LBL_PERMISSIONS}</b></td>
						</tr>
						<tr>
							<td style="padding-left: 20px; text-align: left;">
								<select id="{$APP[$SHARING_MODULE]}_access" name="{$APP[$SHARING_MODULE]}_access" onChange="fnwriteRules ('{$APP[$SHARING_MODULE]}', '')" title="">
{foreach $TO_OPTIONS as $option}
									<option value="{$option.value}{if (isset ($option.selected)) && ($option.selected)}" selected="selected"{/if}>{$option.text}</option>
{/foreach}
								</select>
							</td>
							<td>
								<select id="share_memberType" name="share_memberType" onChange="fnwriteRules ('{$APP[$SHARING_MODULE]}', '')" title="">
{foreach $SHARE_OPTIONS as $option}
									<option value="{$option.value}{if (isset ($option.selected)) && ($option.selected)}" selected="selected"{/if}>{$option.text}</option>
{/foreach}
								</select>
							</td>
						</tr>
						<tr>
							<td colspan="2">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="2" align="left">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="2" class="dvInnerHeader"><b>{$MOD.LBL_RULE_CONSTRUCTION}</b></td>
						</tr>
						<tr>
							<td style="white-space: normal;" colspan="2" id="rules">&nbsp;</td>
						</tr>
						<tr>
							<td style="white-space:normal;" colspan="2" id="relrules">&nbsp;</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<table border="0" cellspacing="0" cellpadding="5" width="100%" class="layerPopupTransport">
			<tr>
				<td colspan="2" align="center">
					<input type="submit" class="crmButton small save" name="add" value="{$MOD.LBL_ADD_RULE}">&nbsp;&nbsp;
				</td>
			</tr>
		</table>
	</form>
</div>
{/strip}