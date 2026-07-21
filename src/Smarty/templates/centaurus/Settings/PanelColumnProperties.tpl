<script type="text/javascript" src="modules/CustomView/CustomView.js"></script>
<script type="text/javascript" src="include/calculator/calc.js"></script>
<form name="CustomView" method="post" enctype="multipart/form-data" action="index.php">
	<input type="hidden" name="module" value="Settings" />
	<input type="hidden" name="action" value="LayoutPanelList" />
	<input type="hidden" name="function" value="SavePanelColumnProperties" />
	<input type="hidden" name="record" value="{$PANELID}" />
	<input type="hidden" name="columnindex" value="{$COLUMNINDEX}" />
	<input type="hidden" name="fld_module" value="{$MODULE}" />
	<table align="center" border="0" cellpadding="0" cellspacing="0" width="98%">
		<tbody>
		<tr>
			<td valign="top"><img src="{'showPanelTopLeft.gif'|@vtiger_imageurl:$THEME}"></td>
			<td class="showPanelBg" valign="top" width="100%">
				<div class="small" style="padding: 20px;">
					<span class="lvtHeaderText">
						{$MODULEPANELLABEL}&gt;
						<a class="hdrLink" href="index.php?action=ListView&module={$MODULE}&parenttab={$CATEGORY}">{$MODULELABEL}</a>&gt;
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
										<td colspan="2" class="detailedViewHeader"><strong>{$MOD.LBL_DETAILS}</strong>
										</td>
									</tr>
									<tr>
										<td>
											{$MOD.LBL_FIELD_COLUMN}
										</td>
										<td>
											<select class="detailedViewTextBox" id="fieldop" name="fieldop" title="">
												<option value="">{'LBL_NONE'|@getTranslatedString:$MODULE}</option>
												{$COLUMNS_BLOCK}
											</select>
											<select name="opcolumn" id="opcolumn" class="small" title="">
												{$OPERATIONS}
											</select>
										</td>
									</tr>
									<tr>
										<td>
											{$MOD.LBL_LABEL_COLUMN}
										</td>
										<td>
											<input name="label" id="label" class="detailedViewTextBox" onfocus="this.className='detailedViewTextBoxOn'" onblur="this.className='detailedViewTextBox'" type="text" value="{$LABEL}" placeholder="" />
										</td>
									</tr>
									<tr>
										<td>
											{$MOD.LBL_GRAPH_COLUMN}
										</td>
										<td>
											<input name="graficar" id="graficar" class="detailedViewTextBox" onfocus="this.className='detailedViewTextBoxOn'" onblur="this.className='detailedViewTextBox'" type="checkbox" value="1" placeholder=""{if ($GRAFICAR != 'no')} checked="checked"{/if} />
										</td>
									</tr>
									<tr>
										<td class="detailedViewHeader">
											{$MOD.LBL_AXIS_COLUMN}
										</td>
										<td class="detailedViewHeader">
											<input name="axiscolumn" id="axiscolumn" class="detailedViewTextBox" onfocus="this.className='detailedViewTextBoxOn'" onblur="this.className='detailedViewTextBox'" type="checkbox" value="1" placeholder=""{if ($COLUMNINDEX == -1)} checked="checked"{/if} {$DISABLED_AXIS_COLUMN} />
										</td>
									</tr>
								</table>
							</td>
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
{include file='AdvanceFilterPanel.tpl' SOURCE='customview'}
																	</td>
																</tr>
															</table>
														</td>
													</tr>
												</table>
											</div>
										</td>
									</tr>
								</table>
							</td>
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
						<tr>
							<td colspan="4">&nbsp;</td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
	</table>
</form>
<script type="text/javascript">
	function mandatoryCheck () {ldelim}

		var mandatorycheck = false;
		var i, j;
		var manCheck = new Array ({$MANDATORYCHECK});
		var showvalues = "{$SHOWVALUES}";
		if (manCheck) {ldelim}
			var isError = false;
			var errorMessage = "";
			if (trim (document.CustomView.viewName.value) == "") {ldelim}
				isError = true;
				errorMessage += "\n{$MOD.LBL_VIEW_NAME}";
				{rdelim}
			// Here we decide whether to submit the form.
			if (isError == true) {ldelim}
				alert ("{$MOD.Missing_required_fields}:" + errorMessage);
				return false;
				{rdelim}

			for (i = 1; i <= 9; i++) {ldelim}
				var columnvalue = document.getElementById ("column" + i).value;
				if (columnvalue != null) {ldelim}
					for (j = 0; j < manCheck.length; j++) {ldelim}
						if (columnvalue == manCheck[ j ]) {ldelim}
							mandatorycheck = true;
							{rdelim}
						{rdelim}
					if (mandatorycheck == true) {ldelim}
						if (($ ("jscal_field_date_start").value.replace (/^\s+/g, '').replace (/\s+$/g, '').length != 0) || ($ ("jscal_field_date_end").value.replace (/^\s+/g, '').replace (/\s+$/g, '').length != 0)) {
							return stdfilterdateValidate ();
						} else {
							return true;
						}
						{rdelim} else {ldelim}
						mandatorycheck = false;
						{rdelim}
					{rdelim}
				{rdelim}
			{rdelim}
		if (mandatorycheck == false) {ldelim}
			alert ("{$APP.MUSTHAVE_ONE_REQUIREDFIELD}" + showvalues);
			{rdelim}

		return false;
		{rdelim}
	var k;
	var colOpts;
	var manCheck = new Array ({$MANDATORYCHECK});
	{literal}
	if (document.CustomView.record.value == '') {
		for (k = 0; k < manCheck.length; k++) {
			selname = "column" + (k + 1);
			selelement = document.getElementById (selname);
			if (selelement == null || typeof selelement == 'undefined') {
				continue;
			}
			colOpts = selelement.options;
			for (l = 0; l < colOpts.length; l++) {
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
		var cvselect_array = new Array ('column1', 'column2', 'column3', 'column4', 'column5', 'column6', 'column7', 'column8', 'column9')
		for (var loop = 0; loop < cvselect_array.length - 1; loop++) {
			selected_cv_columnvalue = $ (cvselect_array[ loop ]).options[ $ (cvselect_array[ loop ]).selectedIndex ].value;
			if (selected_cv_columnvalue != '') {
				for (var iloop = loop + 1; iloop < cvselect_array.length; iloop++) {
					selected_cv_icolumnvalue = $ (cvselect_array[ iloop ]).options[ $ (cvselect_array[ iloop ]).selectedIndex ].value;
					if (selected_cv_columnvalue == selected_cv_icolumnvalue) {
						{/literal}
						alert ('{$APP.COLUMNS_CANNOT_BE_DUPLICATED}');
						$ (cvselect_array[ iloop ]).selectedIndex = 0;
						return false;
						{literal}
					}

				}
			}
		}
		return true;
	}

	function stdfilterdateValidate () {
		if (!dateValidate ("startdate", alert_arr.STDFILTER + " - " + alert_arr.STARTDATE, "OTH")) {
			getObj ("startdate").focus ()
			return false;
		}
		else if (!dateValidate ("enddate", alert_arr.STDFILTER + " - " + alert_arr.ENDDATE, "OTH")) {
			getObj ("enddate").focus ()
			return false;
		}
		else {
			if (!dateComparison ("enddate", alert_arr.STDFILTER + " - " + alert_arr.ENDDATE, "startdate", alert_arr.STDFILTER + " - " + alert_arr.STARTDATE, "GE")) {
				getObj ("enddate").focus ()
				return false
			} else {
				return true;
			}
		}
	}
	standardFilterDisplay ();
	{/literal}
</script>
