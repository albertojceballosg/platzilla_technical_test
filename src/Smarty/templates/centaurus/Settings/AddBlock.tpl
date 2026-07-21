{strip}
<div id="orgLay" style="display: block;" class="layerPopup">
	<script language="javascript" type="text/javascript" src="include/js/customview.js"></script>
	<form action="index.php" method="post" name="addtodb" onsubmit="VtigerJS_DialogBox.block();">
		<input type="hidden" name="module" value="{$CURRENT_MODULE}" />
		<input type="hidden" name="fld_module" value="{$MODULE}" />
		<input type="hidden" name="parenttab" value="{$CURRENT_MODULE}" />
		<input type="hidden" name="action" value="AddBlockToDB" />
		<input type="hidden" name="blockid" value="{$BLOCK_ID}" />
		<input type="hidden" name="tabid" value="{$TAB_ID}" />
		<input type="hidden" name="blockselect" value="{$BLOCK_SELECT}" />
		<input type="hidden" name="mode" id="cfedit_mode" value="{$MODE}" />
		<input type="hidden" name="cfcombo" id="selectedfieldtype" value="" />
		<table width="100%" border="0" cellpadding="5" cellspacing="0" class="layerHeadingULine">
			<tr>
{if ($MODE == 'edit')}
				<td width="60%" align="left" class="layerPopupHeading">Edit Block</td>
{else}
				<td width="95%" align="left" class="layerPopupHeading">Add Block</td>
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
										<td width="50%">
											<table width="100%" border="0" cellpadding="5" cellspacing="0">
												<tr>
													<td class="dataLabel" nowrap="nowrap" align="right" width="30%">
														<b>Block name</b>
													</td>
													<td align="left" width="70%">
														<input name="blocklabel" value="{$BLOCK_LABEL}" type="text" class="txtBox" placeholder="">
													</td>
												</tr>
												<tr>
													<td class="dataLabel" align="right" width="30%">
														<b>After</b>
													</td>
													<td align="left" width="70%">
														<select id="blockname" name="after_blockid" title="">
{foreach $BLOCKS as $block}
															<option value="{$block.blockid}">{$block.blocklabel}</option>
{/foreach}
														</select>
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
								<table border="0" cellspacing="0" cellpadding="5" width="100%">
									<tr>
										<td align="center">
											<input type="button" name="save" value=" &nbsp; {$APPLICATION_STRINGS.LBL_SAVE_BUTTON_LABEL}&nbsp; " class="crmButton small save" onclick="return check();" />&nbsp;
											<input type="button" name="cancel" value=" {$APPLICATION_STRINGS.LBL_CANCEL_BUTTON_LABEL} " class="crmButton small cancel" onclick="fninvsh('orgLay');" />
										</td>
									</tr>
								</table>
								<input type="hidden" name="fieldType" id="fieldType" value="" />
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</form>
</div>
{/strip}