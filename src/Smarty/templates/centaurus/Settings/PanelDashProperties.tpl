{strip}
<script type="text/javascript" src="modules/CustomView/CustomView.js"></script>
<script type="text/javascript" src="include/calculator/calc.js"></script>
<form enctype="multipart/form-data" name="CustomView" method="POST" action="index.php" onsubmit="if (mandatoryCheck ()) {ldelim} VtigerJS_DialogBox.block (); {rdelim} else {ldelim} return false; {rdelim}">
	<input type="hidden" name="module" value="Settings" />
	<input type="hidden" name="action" value="LayoutPanelList" />
	<input type="hidden" name="function" value="SavePanelOrGraph" />
	<input type="hidden" name="fld_module" value="{$MODULE}" />
	<input type="hidden" name="record" value="{$PANELID}" />
	<table align="center" border="0" cellpadding="0" cellspacing="0" width="98%">
		<tbody>
		<tr>
			<td valign="top"><img src="{'showPanelTopLeft.gif'|@vtiger_imageurl:$THEME}"></td>
			<td class="showPanelBg" valign="top" width="100%">
				<div class="small" style="padding: 20px;">
					<span class="lvtHeaderText">{$MODULEPANELLABEL}&gt;<a class="hdrLink" href="index.php?module={$MODULE}&action=ListView&">{$MODULELABEL}</a>&gt;{$MOD.LBL_PANEL_EDIT_PROPERTIES}</span>
					<br />
					<hr noshade="noshade" size="1">
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="95%">
						<tbody>
						<tr>
							<td align="left" valign="top">
								<table width="100%" border="0" cellspacing="0" cellpadding="5">
									<tr>
										<td colspan="3" class="detailedViewHeader">
											<b>{$MOD.LBL_LIST_COLUMNS} </b>
											<div style="float: right;">
												<input
													type="button"
													class="crmButton create small"
													onclick="
														jQuery.ajax ({ldelim}
															type: 'POST',
															url: 'index.php',
															data: {ldelim}
																panelid: '{$PANELID}',
																module: '{$CURRENT_MODULE}',
																action: 'LayoutPanelList',
																'function': 'panelColumnProperties',
																Ajax: 'true',
																related_module: '{$RELATED_MODULE}',
																fld_module: '{$MODULE}'
															{rdelim}
														{rdelim}).done (function (html) {ldelim}
															jQuery ('#textodlgPanelGraphProperties').html (html);
														{rdelim});"
													alt="{'LBL_NEW_COLUMN'|@getTranslatedString}"
													title="{'LBL_NEW_COLUMN'|@getTranslatedString}"
													value="{'LBL_NEW_COLUMN'|@getTranslatedString}"
												/>
											</div>
										</td>
									</tr>
									<tr>
										<td class="detailedViewHeader">
											{$MOD.LBL_LABEL_COLUMN}
										</td>
										<td class="detailedViewHeader">
											{$MOD.LBL_GRAPH_COLUMN}
										</td>
										<td class="detailedViewHeader">
											{$MOD.LBL_EDIT_PROPERTIES}
										</td>
									</tr>
{foreach $_LIST_COLUMNS as $column}
									<tr>
										<td>
											{$column->titulo}
										</td>
										<td>
											{$column->graficar}
										</td>
										<td>
											{$column->action}
										</td>
									</tr>
{/foreach}
								</table>
							</td>
						</tr>
						<tr>
							<td colspan="4">&nbsp;</td>
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
		var showvalues = '';
		if (manCheck) {
			var isError = false;
			var errorMessage = '';
			if (trim (document.CustomView['viewName'].value) == '') {
				isError = true;
				errorMessage += {/literal}"\n{$MOD.LBL_VIEW_NAME}"{literal};
			}
			// Here we decide whether to submit the form.
			if (isError == true) {
				alert ({/literal}'{$MOD.Missing_required_fields}:'{literal} + errorMessage);
				return false;
			}

			for (i = 1; i <= 9; i++) {
				var columnvalue = document.getElementById ('column' + i).value;
				if (columnvalue != null) {
					for (j = 0; j < manCheck.length; j++) {
						if (columnvalue == manCheck[ j ]) {
							mandatorycheck = true;
						}
					}
					if (mandatorycheck == true) {
						if (($ ('jscal_field_date_start').value.replace (/^\s+/g, '').replace (/\s+$/g, '').length != 0) || ($ ('jscal_field_date_end').value.replace (/^\s+/g, '').replace (/\s+$/g, '').length != 0)) {
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
			alert ({/literal}'{$APP.MUSTHAVE_ONE_REQUIREDFIELD}'{literal} + showvalues);
		}
		return false;
	}

	var k;
	var colOpts;
	var manCheck = [];

	if (document.CustomView.record.value == '') {
		for (k = 0; k < manCheck.length; k++) {
			var selname = 'column' + (k + 1);
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
		if (!dateValidate ('startdate', alert_arr.STDFILTER + ' - ' + alert_arr.STARTDATE, 'OTH')) {
			getObj ('startdate').focus ();
			return false;
		}
		else if (!dateValidate ('enddate', alert_arr.STDFILTER + ' - ' + alert_arr.ENDDATE, 'OTH')) {
			getObj ('enddate').focus ();
			return false;
		}
		else {
			if (!dateComparison ('enddate', alert_arr.STDFILTER + ' - ' + alert_arr.ENDDATE, 'startdate', alert_arr.STDFILTER + ' - ' + alert_arr.STARTDATE, 'GE')) {
				getObj ('enddate').focus ();
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