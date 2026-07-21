{strip}
<script type="text/javascript" src="modules/CustomView/CustomView.js"></script>
<script language="JavaScript" type="text/javascript" src="include/calculator/calc.js"></script>
<form enctype="multipart/form-data" name="CustomView" method="POST" action="index.php">
	<input type="hidden" name="module" value="Settings" />
	<input type="hidden" name="action" value="LayoutPanelList" />
	<input type="hidden" name="function" value="newPanelOrGraph" />
	<input type="hidden" name="fld_module" value="{$MODULE}" />
	<input type="hidden" name="record" value="{$PANELID}" />
	<table align="center" border="0" cellpadding="0" cellspacing="0" width="98%">
		<tbody>
		<tr>
			<td valign="top"><img src="{'showPanelTopLeft.gif'|@vtiger_imageurl:$THEME}"></td>
			<td class="showPanelBg" valign="top" width="100%">
				<div class="small" style="padding: 20px;">
					<span class="lvtHeaderText">
						<a class="hdrLink" href="index.php?action=ListView&module={$MODULE}">{$MODULELABEL}</a>&gt;
						{$MOD.LBL_ADD_PANEL_OR_GRAPH}
					</span>
					<br>
					<hr noshade="noshade" size="1">
					<form name="EditView" method="post" enctype="multipart/form-data" action="index.php">
						<table align="center" border="0" cellpadding="0" cellspacing="0" width="95%">
							<tbody>
							<tr>
								<td align="left" valign="top">
									<table width="100%" border="0" cellspacing="0" cellpadding="5">
										<tr>
											<td colspan="4" class="detailedViewHeader">
												<strong>{$MOD.LBL_DETAILS}</strong></td>
										</tr>
										<tr>
											<td colspan="2">{$MOD.LBL_LABEL}</td>
											<td colspan="2">
												<input type="text" name="label" tabindex="" value="" class="detailedViewTextBox" onfocus="this.className='detailedViewTextBoxOn'" onblur="this.className='detailedViewTextBox'" placeholder="" />
											</td>
										</tr>
										<tr>
											<td colspan="2">{$MOD.LBL_TYPE}</td>
											<td colspan="2">
												<select name="type" id="type" class="small" onchange="onChangeType (this.value);" title="">
													<option value="Panel">Panel</option>
													<option value="Graph">Graph</option>
												</select>
											</td>
										</tr>
										<tr id="tr_subtype">
											<td colspan="2">{$MOD.LBL_SUBTYPE}</td>
											<td colspan="2">
												<select name="subtype" id="subtype" class="small" title="">
													<option value="" id="subtype1">{$MOD.Normal}</option>
													<option value="Dash" id="subtype2">{$MOD.Dashboard}</option>
													<option value="Bar" id="subtype3" style="display:none">{$MOD.Bar}</option>
													<option value="Pie" id="subtype4" style="display:none">{$MOD.Pie}</option>
												</select>
											</td>
										</tr>
										<tr>
											<td colspan="2">{$MOD.LBL_MODULE}</td>
											<td colspan="2">
												{$LISTAMODULOS}
											</td>
										</tr>
										<tr>
											<td colspan="4">&nbsp;</td>
										</tr>
										<tr>
											<td colspan="4" style="padding: 5px;">
												<div align="center">
													<input title="{$APP.LBL_SAVE_BUTTON_LABEL} [Alt+S]" accesskey="S" class="crmbutton small save" name="button2" value="{$APP.LBL_SAVE_BUTTON_LABEL}" style="width: 70px;" type="submit" />
													<input title="{$APP.LBL_CANCEL_BUTTON_LABEL} [Alt+X]" accesskey="X" class="crmbutton small cancel" name="button2" onclick='window.history.back()' value="{$APP.LBL_CANCEL_BUTTON_LABEL}" style="width: 70px;" type="button" />
												</div>
											</td>
										</tr>
										<tr>
											<td colspan="4">&nbsp;</td>
										</tr>
									</table>
								</td>
							</tr>
							</tbody>
						</table>
					</form>
				</div>
			</td>
		</tr>
		</tbody>
	</table>
<script language="javascript" type="text/javascript">
{literal}
	function onChangeType (value) {
		if (value == 'Graph') {
			jQuery ('#subtype1').hide ();
			jQuery ('#subtype2').hide ();
			jQuery ('#subtype3').show ();
			jQuery ('#subtype4').show ();
		} else {
			jQuery ('#subtype1').show ();
			jQuery ('#subtype2').show ();
			jQuery ('#subtype3').hide ();
			jQuery ('#subtype4').hide ();
		}
	}
{/literal}
</script>
{/strip}