{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
<table class="table" bgcolor="#ffffff" border="0" cellpadding="5" cellspacing="0" width="100%">
	<tbody>
		<tr>
			<td colspan="4">
				<span>{$MOD.LBL_SPECIFY_GROUPING}</span><br>
				{$MOD.LBL_SELECT_COLUMNS_TO_GROUP_REPORTS}
				<hr>
			</td>
		</tr>
	<tbody id="grouping-rows-container">
		{foreach from=$GROUPING_ROWS item=row key=index}
			<tr class="grouping-row" data-row-index="{$index}">
				<td style="padding-left: {if $index == 0}5px{elseif $index == 1}35px{else}65px{/if};" align="left"
					width="40%">
					{if $index == 0}
						{$MOD.LBL_GROUPING_SUMMARIZE}
					{elseif $index == 1}
						{$MOD.LBL_GROUPING_THEN_BY}
					{else}
						{$MOD.LBL_GROUPING_THEN_BY}
					{/if}<br>
					<select id="Group{$index+1}" name="Group{$index+1}" class="form-control grouping-select"
						onchange="getDateFieldGrouping('Group{$index+1}')">
						<option value="none">{$MOD.LBL_NONE}</option>
						{$row.BLOCK}
					</select>
				</td>
				<td style="padding-left: 5px;" align="left" width="25%">
					{$row.GROUPBYTIME}
				</td>
				<td style="padding-left: 5px;" align="left" width="25%">
					{$MOD.LBL_GROUPING_SORT}<br>
					<select name="Sort{$index+1}" class="form-control">
						{$row.ASCDESC}
					</select>
				</td>
				<td style="padding-left: 5px;" align="center" width="10%">
					{if $index > 0}
						<button type="button" class="btn btn-danger btn-sm" onclick="removeGroupingRow({$index+1})"
							title="Eliminar">
							<i class="fa fa-trash"></i>
						</button>
					{else}
						&nbsp;
					{/if}
				</td>
			</tr>
		{/foreach}
	</tbody>
	<tr>
		<td colspan="4" style="padding: 10px;">
			<button type="button" class="btn btn-primary btn-sm" onclick="addGroupingRow()" id="add-grouping-btn">
				<i class="fa fa-plus"></i> Agregar campo de agrupación
			</button>
			<span id="max-grouping-message" style="display:none; color: #999; margin-left: 10px;">Máximo 10 campos de
				agrupación</span>
		</td>
	</tr>
	<tr>
		<td colspan="4" height="100">&nbsp;</td>
	</tr>
	</tbody>
</table>

<script type="text/javascript">
	var maxGroupingRows = 10;
	var currentGroupingRows = {$GROUPING_COUNT};
	var groupingBlockHTML = '{$BLOCK_HTML|escape:javascript}';
	var groupingTimeHTML = '{$GROUPBYTIME_HTML|escape:javascript}';
	var ascDescHTML = '{$ASCDESC_HTML|escape:javascript}';

	function addGroupingRow() {
		if (currentGroupingRows >= maxGroupingRows) {
			document.getElementById('max-grouping-message').style.display = 'inline';
			return;
		}

		currentGroupingRows++;
		var rowIndex = currentGroupingRows;
		var paddingLeft = rowIndex <= 2 ? (rowIndex == 1 ? '5px' : '35px') : '65px';
		var label = rowIndex == 1 ? '{$MOD.LBL_GROUPING_SUMMARIZE|escape:javascript}' : '{$MOD.LBL_GROUPING_THEN_BY|escape:javascript}';

		var newRow = '<tr class="grouping-row" data-row-index="' + rowIndex + '">' +
			'<td style="padding-left: ' + paddingLeft + ';" align="left" width="40%">' +
			label + '<br>' +
			'<select id="Group' + rowIndex + '" name="Group' + rowIndex +
			'" class="form-control grouping-select" onchange="getDateFieldGrouping(\'Group' + rowIndex + '\')">' +
			'<option value="none">{$MOD.LBL_NONE|escape:javascript}</option>' +
			groupingBlockHTML +
			'</select>' +
			'</td>' +
			'<td style="padding-left: 5px;" align="left" width="25%">' +
			'<div id="Group' + rowIndex + 'time" style="display:none">{$MOD.LBL_GROUPING_TIME|escape:javascript}<br>' +
			'<select id="groupbytime' + rowIndex + '" name="groupbytime' + rowIndex + '" class="txtBox">' +
			'<option value="None">{$MOD.LBL_NONE|escape:javascript}</option>' +
			'<option value="Year">{$MOD.LBL_YEAR|escape:javascript}</option>' +
			'<option value="Month">{$MOD.LBL_MONTH|escape:javascript}</option>' +
			'<option value="Quarter">{$MOD.LBL_QUARTER|escape:javascript}</option>' +
			'</select>' +
			'</div>' +
			'</td>' +
			'<td style="padding-left: 5px;" align="left" width="25%">' +
			'{$MOD.LBL_GROUPING_SORT|escape:javascript}<br>' +
			'<select name="Sort' + rowIndex + '" class="form-control">' +
			ascDescHTML +
			'</select>' +
			'</td>' +
			'<td style="padding-left: 5px;" align="center" width="10%">' +
			'<button type="button" class="btn btn-danger btn-sm" onclick="removeGroupingRow(' + rowIndex +
			')" title="Eliminar">' +
			'<i class="fa fa-trash"></i>' +
			'</button>' +
			'</td>' +
			'</tr>';

		document.getElementById('grouping-rows-container').insertAdjacentHTML('beforeend', newRow);

		if (currentGroupingRows >= maxGroupingRows) {
			document.getElementById('add-grouping-btn').disabled = true;
			document.getElementById('max-grouping-message').style.display = 'inline';
		}
	}

	function removeGroupingRow(rowIndex) {
		var rows = document.querySelectorAll('.grouping-row');
		if (rows.length <= 1) {
			alert('Debe haber al menos un campo de agrupación');
			return;
		}

		var rowToRemove = document.querySelector('.grouping-row[data-row-index="' + rowIndex + '"]');
		if (rowToRemove) {
			rowToRemove.remove();
			currentGroupingRows--;

			// Renumerar las filas restantes
			var remainingRows = document.querySelectorAll('.grouping-row');
			remainingRows.forEach(function(row, index) {
				var newIndex = index + 1;
				row.setAttribute('data-row-index', newIndex);

				// Actualizar IDs y names de los elementos
				var select = row.querySelector('.grouping-select');
				if (select) {
					select.id = 'Group' + newIndex;
					select.name = 'Group' + newIndex;
					select.setAttribute('onchange', "getDateFieldGrouping('Group" + newIndex + "')");
				}

				var sortSelect = row.querySelector('select[name^="Sort"]');
				if (sortSelect) {
					sortSelect.name = 'Sort' + newIndex;
				}

				var timeDiv = row.querySelector('div[id^="Group"]');
				if (timeDiv) {
					timeDiv.id = 'Group' + newIndex + 'time';
					var timeSelect = timeDiv.querySelector('select');
					if (timeSelect) {
						timeSelect.id = 'groupbytime' + newIndex;
						timeSelect.name = 'groupbytime' + newIndex;
					}
				}

				var removeBtn = row.querySelector('button[onclick^="removeGroupingRow"]');
				if (removeBtn) {
					removeBtn.setAttribute('onclick', 'removeGroupingRow(' + newIndex + ')');
				}
			});

			if (currentGroupingRows < maxGroupingRows) {
				document.getElementById('add-grouping-btn').disabled = false;
				document.getElementById('max-grouping-message').style.display = 'none';
			}
		}
	}
</script>