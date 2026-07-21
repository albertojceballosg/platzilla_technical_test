{strip}
<script type="text/javascript" src="modules/CustomView/CustomView.js"></script>
<script language="JavaScript" type="text/javascript" src="include/calculator/calc.js"></script>
<form enctype="multipart/form-data" name="CustomView" method="POST" action="index.php" onsubmit="{literal}if (mandatoryCheck ()) {VtigerJS_DialogBox.block (); } else { return false; }{/literal}">
	<input type="hidden" name="module" value="Settings" />
	<input type="hidden" name="action" value="LayoutPanelList" />
	<input type="hidden" name="function" value="SavePanelOrGraph" />
	<input type="hidden" name="fld_module" value="{$MODULE}">
	<input type="hidden" name="record" value="{$PANELID}">
	<table align="center" border="0" cellpadding="0" cellspacing="0" width="98%">
		<tbody>
		<tr>
			<td valign="top"><img src="{'showPanelTopLeft.gif'|@vtiger_imageurl:$THEME}"></td>
			<td class="showPanelBg" valign="top" width="100%">
				<div class="small" style="padding: 20px;">
					<span class="lvtHeaderText">
						{$MODULEPANELLABEL}&gt;
						<a class="hdrLink" href="index.php?module={$MODULE}&action=ListView">{$MODULELABEL}</a>&gt;
						{$MOD.LBL_PANEL_EDIT_PROPERTIES}
					</span>
					<br />
					<hr noshade="noshade" size="1">
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="95%">
						<tbody>
						<tr>
							<td align="left" valign="top">
								<table width="100%" border="0" cellspacing="0" cellpadding="5">
									<tr>
										<td colspan="4" class="detailedViewHeader"><strong>{$MOD.LBL_DETAILS}</strong></td>
									</tr>
									<tr>
										<td colspan="4">&nbsp;</td>
									</tr>
									<tr>
										<td colspan="4" class="detailedViewHeader"><b>{$MOD.LBL_SELECT_FIELDS} </b></td>
									</tr>
									<tr class="dvtCellLabel">
										<td>
{include file='Settings/PanelPropertiesSelect' COLUMN=$CHOOSE_COLUMNS[0] ROW=1}
										</td>
										<td>
{include file='Settings/PanelPropertiesSelect' COLUMN=$CHOOSE_COLUMNS[1] ROW=2}
										</td>
										<td>
{include file='Settings/PanelPropertiesSelect' COLUMN=$CHOOSE_COLUMNS[2] ROW=3}
										</td>
										<td>
{include file='Settings/PanelPropertiesSelect' COLUMN=$CHOOSE_COLUMNS[3] ROW=4}
										</td>
									</tr>
									<tr class="dvtCellInfo">
										<td>
{include file='Settings/PanelPropertiesSelect' COLUMN=$CHOOSE_COLUMNS[4] ROW=5}
										</td>
										<td>
{include file='Settings/PanelPropertiesSelect' COLUMN=$CHOOSE_COLUMNS[5] ROW=6}
										</td>
										<td>
{include file='Settings/PanelPropertiesSelect' COLUMN=$CHOOSE_COLUMNS[6] ROW=7}
										</td>
										<td>
{include file='Settings/PanelPropertiesSelect' COLUMN=$CHOOSE_COLUMNS[7] ROW=8}
										</td>
									</tr>
									<tr class="dvtCellLabel">
										<td>
{include file='Settings/PanelPropertiesSelect' COLUMN=$CHOOSE_COLUMNS[8] ROW=9}
										</td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>
									</tr>
									<tr>
										<td colspan="4">&nbsp;</td>
									</tr>
									<tr>
										<td colspan="4">
											<table align="center" border="0" cellpadding="0" cellspacing="0" width="95%">
												<tbody>
												<tr>
													<td>
														<table class="small" border="0" cellpadding="3" cellspacing="0" width="100%">
															<tbody>
															<tr>
																<td class="dvtTabCache" style="width: 10px;" nowrap>&nbsp;</td>
																<td class="dvtSelectedCell" style="width: 100px;" align="center" nowrap id="mi">
																	<b>{$MOD.LBL_STEP_4_TITLE}</b>
																</td>
																<td class="dvtTabCache" nowrap style="width:55%;">&nbsp;</td>
															</tr>
															</tbody>
														</table>
													</td>
												</tr>
												<tr>
													<td align="left" valign="top">
														<div id="mnuTab2" style="display: block;">
															<table width="100%" cellspacing="0" cellpadding="5" class="dvtContentSpace">
																<tr>
																	<td><br>
																		<table width="75%" border="0" cellpadding="5" cellspacing="0" align="center">
																			<tr>
																				<td>
{include file='AdvanceFilter.tpl' SOURCE='customview'}
																				</td>
																			</tr>
																		</table>
																	</td>
																</tr>
															</table>
														</div>
													</td>
												</tr>
{include file='LinkAdmin.tpl' SOURCE='customview'}
											</table>
										</td>
									</tr>
									<tr>
										<td colspan="4">&nbsp;</td>
									</tr>
									<tr>
										<td colspan="4" style="padding: 5px;">
											<div align="center">
												<input title="{$APP.LBL_SAVE_BUTTON_LABEL} [Alt+S]" accesskey="S" class="crmbutton small save" name="button2" value="{$APP.LBL_SAVE_BUTTON_LABEL}" style="width: 70px;" type="submit" onClick="return validateCV();" />
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
				</div>
			</td>
		</tr>
		</tbody>
	</table>
</form>
<script type="text/javascript">
{literal}
	function mandatoryCheck () {
		var mandatorycheck = false;
		var i, j;
		var manCheck = [];
		var showvalues = "";
		if (manCheck) {
			var isError = false;
			var errorMessage = "";
			if (trim (document.CustomView['viewName'].value) == "") {
				isError = true;
				errorMessage += {/literal}"\n{$MOD.LBL_VIEW_NAME}"{literal};
			}
			// Here we decide whether to submit the form.
			if (isError == true) {
				alert ({/literal}"{$MOD.Missing_required_fields}:"{literal} + errorMessage);
				return false;
			}
			for (i = 1; i <= 9; i++) {
				var columnvalue = document.getElementById ("column" + i).value;
				if (columnvalue != null) {
					for (j = 0; j < manCheck.length; j++) {
						if (columnvalue == manCheck[ j ]) {
							mandatorycheck = true;
						}
					}
					if (mandatorycheck == true) {
						if (($ ("jscal_field_date_start").value.replace (/^\s+/g, '').replace (/\s+$/g, '').length != 0) || ($ ("jscal_field_date_end").value.replace (/^\s+/g, '').replace (/\s+$/g, '').length != 0)) {
							return stdfilterdateValidate ();
						} else {
							return true;
						}
					} else {
						mandatorycheck = false;
					}
				}
			}
		}
		if (mandatorycheck == false) {
			alert ({/literal}"{$APP.MUSTHAVE_ONE_REQUIREDFIELD}"{literal} + showvalues);
		}
		return false;
	}

	var k;
	var colOpts;
	var manCheck = [];
	if (document.CustomView.record.value == '') {
		for (k = 0; k < manCheck.length; k++) {
			var selname = "column" + (k + 1);
			var selelement = document.getElementById (selname);
			if (selelement == null || typeof selelement == 'undefined') {
				continue;
			}
			colOpts = selelement.options;
			for (var l = 0; l < colOpts.length; l++) {
				if (colOpts[ l ].value == manCheck[ k ]) {
					colOpts[ l ].selected = true;
				}
			}
		}
	}

	function validateCV () {
		if (checkDuplicate ()) {
			return checkAdvancedFilter ();
		}
		return false;
	}

	function checkDuplicate () {
		if (getObj ('viewName').value.toLowerCase () == 'all') {
			alert (alert_arr.ALL_FILTER_CREATION_DENIED);
			return false;
		}
		var cvselect_array = ['column1', 'column2', 'column3', 'column4', 'column5', 'column6', 'column7', 'column8', 'column9'];
		for (var loop = 0; loop < cvselect_array.length - 1; loop++) {
			var selected_cv_columnvalue = $ (cvselect_array[ loop ]).options[ $ (cvselect_array[ loop ]).selectedIndex ].value;
			if (selected_cv_columnvalue != '') {
				for (var iloop = loop + 1; iloop < cvselect_array.length; iloop++) {
					var selected_cv_icolumnvalue = $ (cvselect_array[ iloop ]).options[ $ (cvselect_array[ iloop ]).selectedIndex ].value;
					if (selected_cv_columnvalue == selected_cv_icolumnvalue) {
						alert ({/literal}'{$APP.COLUMNS_CANNOT_BE_DUPLICATED}'{literal});
						$ (cvselect_array[ iloop ]).selectedIndex = 0;
						return false;
					}
				}
			}
		}
		return true;
	}

	function stdfilterdateValidate () {
		if (!dateValidate ("startdate", alert_arr.STDFILTER + " - " + alert_arr.STARTDATE, "OTH")) {
			getObj ("startdate").focus ();
			return false;
		}
		else if (!dateValidate ("enddate", alert_arr.STDFILTER + " - " + alert_arr.ENDDATE, "OTH")) {
			getObj ("enddate").focus ();
			return false;
		}
		else {
			if (!dateComparison ("enddate", alert_arr.STDFILTER + " - " + alert_arr.ENDDATE, "startdate", alert_arr.STDFILTER + " - " + alert_arr.STARTDATE, "GE")) {
				getObj ("enddate").focus ();
				return false
			} else {
				return true;
			}
		}
	}
	standardFilterDisplay ();
{/literal}
</script>
{/strip}