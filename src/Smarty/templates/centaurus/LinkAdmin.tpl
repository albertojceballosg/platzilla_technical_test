{strip}
<script type="text/javascript">
{literal}
	var iNumRows = -1;

	function addLinkRow () {
		var ctrlTable = document.getElementById ('linksAdmin'),
				str;

		if (ctrlTable) {
			if (iNumRows == -1) {
				iNumRows = (ctrlTable.rows.length) - 1;
			} else {
				iNumRows++;
			}
			var row = ctrlTable.insertRow (ctrlTable.rows.length - 1);
			var x1 = row.insertCell (0);
			var x2 = row.insertCell (1);
			var x3 = row.insertCell (2);
			var x4 = row.insertCell (3);
			row.id = 'row' + iNumRows;
			str = document.getElementById ('td_label0').innerHTML;
			x1.innerHTML = str.replace (/0/g, '' + iNumRows);
			str = document.getElementById ('td_url0').innerHTML;
			x2.innerHTML = str.replace (/0/g, '' + iNumRows);
			str = document.getElementById ('td_icon0').innerHTML;
			x3.innerHTML = str.replace (/0/g, '' + iNumRows);
			str = document.getElementById ('td_action0').innerHTML;
			x4.innerHTML = str.replace (/0/g, '' + iNumRows);
			x1.id = 'td_label' + iNumRows;
			x2.id = 'td_url' + iNumRows;
			x3.id = 'td_icon' + iNumRows;
			x4.id = 'td_action' + iNumRows;
			x1.className = 'dvtCellLabel';
			x2.className = 'dvtCellLabel';
			x3.className = 'dvtCellLabel';
			x4.className = 'dvtCellLabel';
		}
	}

	function deleteLink (iNumRow) {
		var ctrlTable = document.getElementById ('linksAdmin');
		if (ctrlTable) {
			alert ('row' + iNumRow);
			var x = document.getElementById ('row' + iNumRow);
			var tablepadre = x.parentNode;
			tablepadre.removeChild (x);
		}
	}
{/literal}
</script>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="95%">
	<tbody>
	<tr>
		<td>
			<table class="small" border="0" cellpadding="3" cellspacing="0" width="100%">
				<tbody>
				<tr>
					<td class="dvtTabCache" style="width: 10px;" nowrap="">&nbsp;</td>
					<td class="dvtSelectedCell" style="width: 100px;" align="center" nowrap="" id="mi">
						<b>{$MOD.LBL_LINK_ADMINISTRATION}</b>
					</td>
					<td class="dvtTabCache" nowrap="" style="width:55%;">&nbsp;</td>
				</tr>
				</tbody>
			</table>
		</td>
	</tr>
	<tr>
		<td align="left" valign="top">
			<div id="mnuTab2" style="display:block">
				<table width="100%" cellspacing="0" cellpadding="5" class="dvtContentSpace">
					<tbody>
					<tr>
						<td>
							<br />
							<table width="75%" border="0" cellpadding="5" cellspacing="0" align="center">
								<tbody>
								<tr>
									<td>
										<div style="overflow:auto;" id="links_admin" name="links_admin">
											<table class="small" border="0" cellpadding="5" cellspacing="0" width="100%" id="linksAdmin">
												<tbody>
												<tr>
													<td class="detailedViewHeader" align="left">
														<b>{$MOD.LBL_LABEL}</b>
													</td>
													<td class="detailedViewHeader" align="left">
														<b>{$MOD.LBL_URL}</b>
													</td>
													<td class="detailedViewHeader" align="left">
														<b>{$MOD.LBL_ICON}</b>
													</td>
													<td class="detailedViewHeader" align="left">
														&nbsp;
													</td>
												</tr>
												{$CURRENT_LINKS}
												<tr id="groupfooter_1">
													<td colspan="4" align="left">
														<input type="button" class="crmbutton edit small" value="{$MOD.LBL_NEW_LINK}" onclick="addLinkRow()">
													</td>
												</tr>
												</tbody>
											</table>
										</div>
									</td>
								</tr>
								</tbody>
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
{/strip}