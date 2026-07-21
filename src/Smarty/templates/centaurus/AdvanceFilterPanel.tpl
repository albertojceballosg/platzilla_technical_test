<script language="JavaScript" type="text/javascript" src="include/js/advancefilter.js"></script>
{if $JS_DATEFORMAT eq ''}
	{assign var="JS_DATEFORMAT" value=$APP.NTC_DATE_FORMAT|@parse_calendardate}
{/if}
<input type="hidden" id="jscal_dateformat" name="jscal_dateformat" value="{$JS_DATEFORMAT}" />
<input type="hidden" id="image_path" name="image_path" value="{$IMAGE_PATH}" />
<script type="text/javascript">
	var advft_column_index_count = 0;
	function addColumnConditionGlue (columnIndex) {ldelim}
		var columnConditionGlueElement = document.getElementById ('columnconditionglue_' + columnIndex);
		if (columnConditionGlueElement) {ldelim}
			columnConditionGlueElement.innerHTML = "<select name='fcon" + columnIndex + "' id='fcon" + columnIndex + "' class='detailedViewTextBox'>" +
												   "<option value='and'>{'LBL_CRITERIA_AND'|@getTranslatedString:$MODULE}</option>" +
												   "<option value='or'>{'LBL_CRITERIA_OR'|@getTranslatedString:$MODULE}</option>" +
												   "</select>";
		{rdelim}
	{rdelim}

	function addConditionRowNew (groupIndex) {ldelim}
		var groupColumns = column_index_array[ groupIndex ];
		if (typeof(groupColumns) != 'undefined') {ldelim}
			for (var i = groupColumns.length - 1; i >= 0; --i) {ldelim}
				var prevColumnIndex = groupColumns[ i ];
				if (document.getElementById ('conditioncolumn_' + groupIndex + '_' + prevColumnIndex)) {ldelim}
					addColumnConditionGlue (prevColumnIndex);
					break;
				{rdelim}
			{rdelim}
		{rdelim}

		var columnIndex = advft_column_index_count + 1;
		var nextNode = document.getElementById ('groupfooter_' + groupIndex);

		var newNode = document.createElement ('tr');
		var newNodeId = 'conditioncolumn_' + groupIndex + '_' + columnIndex;
		newNode.setAttribute ('id', newNodeId);
		newNode.setAttribute ('name', 'conditionColumn');
		nextNode.parentNode.insertBefore (newNode, nextNode);

		var node1 = document.createElement ('td');
		node1.setAttribute ('class', 'dvtCellLabel');
		node1.setAttribute ('width', '25%');
		newNode.appendChild (node1);
		node1.innerHTML = "<select name='fcol" + columnIndex + "' id='fcol" + columnIndex + "' onchange='updatefOptions(this, \"fop" + columnIndex + "\");addRequiredElements(" + columnIndex + ");' class='detailedViewTextBox'>" +
						  "<option value=''>{'LBL_NONE'|@getTranslatedString:$MODULE}</option>" +
						  "{$COLUMNS_BLOCK}" +
						  "</select>" +
						  "<br/>" +
						  "<input type='text' name='ffie" + columnIndex + "' value=''>";
		var node2 = document.createElement ('td');
		node2.setAttribute ('class', 'dvtCellLabel');
		node2.setAttribute ('width', '25%');
		newNode.appendChild (node2);
		node2.innerHTML = '<select name="fop' + columnIndex + '" id="fop' + columnIndex + '" class="repBox" style="width:100px;" onchange="addRequiredElements(' + columnIndex + ');">' +
						  '<option value="">{'LBL_NONE'|@getTranslatedString:$MODULE}</option>' +
						  '{$FOPTION}' +
						  '</select>';

		var node3 = document.createElement ('td');
		node3.setAttribute ('class', 'dvtCellLabel');
		newNode.appendChild (node3);
		{if $SOURCE eq 'reports'}
		node3.innerHTML = '<input name="fval' + columnIndex + '" id="fval' + columnIndex + '" class="repBox" type="text" value="">' +
						  '<img height=20 width=20 align="absmiddle" style="cursor: pointer;" title="{$APP.LBL_FIELD_FOR_COMPARISION}" alt="{$APP.LBL_FIELD_FOR_COMPARISION}" src="themes/images/terms.gif" onClick="hideAllElementsByName(\'relFieldsPopupDiv\'); fnvshobj(this,\'show_val' + columnIndex + '\');"/>' +
						  '<input type="image" align="absmiddle" style="cursor: pointer;" onclick="document.getElementById(\'fval' + columnIndex + '\').value=\'\';return false;" language="javascript" title="{$APP.LBL_CLEAR}" alt="{$APP.LBL_CLEAR}" src="themes/images/clear_field.gif"/>' +
						  '<div class="layerPopup" id="show_val' + columnIndex + '" name="relFieldsPopupDiv" style="border:0; position: absolute; width:300px; z-index: 50; display: none;">' +
						  '<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" class="mailClient mailClientBg">' +
						  '<tr>' +
						  '<td>' +
						  '<table width="100%" cellspacing="0" cellpadding="0" border="0" class="layerHeadingULine">' +
						  '<tr background="themes/images/qcBg.gif" class="mailSubHeader">' +
						  '<td width=90% class="genHeaderSmall"><b>{$MOD.LBL_SELECT_FIELDS}</b></td>' +
						  '<td align=right>' +
						  '<img border="0" align="absmiddle" src="themes/images/close.gif" style="cursor: pointer;" alt="{$APP.LBL_CLOSE}" title="{$APP.LBL_CLOSE}" onclick="hideAllElementsByName(\'relFieldsPopupDiv\');"/>' +
						  '</td>' +
						  '</tr>' +
						  '</table>' +
						  '<table width="100%" cellspacing="0" cellpadding="0" border="0" class="small">' +
						  '<tr>' +
						  '<td>' +
						  '<table width="100%" cellspacing="0" cellpadding="5" border="0" bgcolor="white" class="small">' +
						  '<tr>' +
						  '<td width="30%" align="left" class="cellLabel small">{$MOD.LBL_RELATED_FIELDS}</td>' +
						  '<td width="30%" align="left" class="cellText">' +
						  '<select name="fval_' + columnIndex + '" id="fval_' + columnIndex + '" onChange="AddFieldToFilter(' + columnIndex + ',this);" class="detailedViewTextBox">' +
						  '<option value="">{$MOD.LBL_NONE}</option>' +
						  '{$REL_FIELDS}' +
						  '</select>' +
						  '</td>' +
						  '</tr>' +
						  '</table>' +
						  '<!-- save cancel buttons -->' +
						  '<table width="100%" cellspacing="0" cellpadding="5" border="0" class="layerPopupTransport">' +
						  '<tr>' +
						  '<td width="50%" align="center">' +
						  '<input type="button" style="width: 70px;" value="{$APP.LBL_DONE}" name="button" onclick="hideAllElementsByName(\'relFieldsPopupDiv\');" class="crmbutton small create" accesskey="X" title="{$APP.LBL_DONE}"/>' +
						  '</td>' +
						  '</tr>' +
						  '</table>' +
						  '</td>' +
						  '</tr>' +
						  '</table>' +
						  '</td>' +
						  '</tr>' +
						  '</table>' +
						  '</div>';
		{else}
		node3.innerHTML = '<input name="fval' + columnIndex + '" id="fval' + columnIndex + '" class="repBox" type="text" value="">' +
						  '<input type="image" align="absmiddle" style="cursor: pointer;" onclick="document.getElementById(\'fval' + columnIndex + '\').value=\'\';return false;" language="javascript" title="{$APP.LBL_CLEAR}" alt="{$APP.LBL_CLEAR}" src="themes/images/clear_field.gif"/>';
		{/if}

		var node4 = document.createElement ('td');
		node4.setAttribute ('class', 'dvtCellLabel');
		node4.setAttribute ('id', 'columnconditionglue_' + columnIndex);
		node4.setAttribute ('width', '60px');
		newNode.appendChild (node4);

		var node5 = document.createElement ('td');
		node5.setAttribute ('class', 'dvtCellLabel');
		node5.setAttribute ('width', '30px');
		newNode.appendChild (node5);
		node5.innerHTML = '<a onclick="deleteColumnRow(' + groupIndex + ',' + columnIndex + ');" href="javascript:;">' +
						  '<img src="themes/images/delete.gif" align="absmiddle" title="{$MOD.LBL_DELETE}..." border="0">' +
						  '</a>';

		if (document.getElementById ('fcol' + columnIndex)) {
			updatefOptions (document.getElementById ('fcol' + columnIndex), 'fop' + columnIndex);
		}
		if (typeof(column_index_array[ groupIndex ]) == 'undefined') {
			column_index_array[ groupIndex ] = [];
		}
		column_index_array[ groupIndex ].push (columnIndex);
		advft_column_index_count++;
	{rdelim}

	jQuery (document).ready (function () {ldelim}
		jQuery ('#fieldop').val ('{$SELECTED_VALUE}');
{if (is_array ($CRITERIA_GROUPS))}
	{assign var=i value=0}
	{foreach $CRITERIA_GROUPS as $key => $columns}
		{foreach $columns as $column}
			{foreach $column as $columnproperty}
				{assign var=val value=(count (explode (':', $columnproperty.columnname)) < 5) ? 'other:other:other:other:N' : $columnproperty.columnname}

		jQuery ('#fcol{$i}').val ('{$val}');
		updatefOptions (document.getElementById('fcol{$i}'), 'fop{$i}');
		addRequiredElements ('{$i}');

		jQuery ('#fop{$i}').val ('{$columnproperty.comparator}');
		jQuery ('#fval{$i}').val ('{$columnproperty.value}');
		jQuery ('#fcon{$i}').val ('{$columnproperty.column_condition}');
		jQuery ('#ffie{$i}').val ('{$columnproperty.columnname}');
				{assign var=i value=$i+1}
			{/foreach}
		{/foreach}
	{/foreach}
		advft_column_index_count = {$i};
{/if}
	{rdelim});
</script>
<table cellspacing="1" cellpadding="5" width="100%" border="0" id="conditiongrouptable_1" valign="top" class="small crmTable">
	<tbody>
{foreach key=GROUP_ID item=GROUP_CRITERIA from=$CRITERIA_GROUPS}
	{assign var=GROUP_COLUMNS value=$GROUP_CRITERIA.columns}
	{assign var=val value=0}
	{foreach key=COLUMN_INDEX item=COLUMN_CRITERIA from=$GROUP_COLUMNS}
	<tr id="conditioncolumn_1_{$val}">
		<td width="25%" class="dvtCellLabel">
			<select class="detailedViewTextBox" onchange="updatefOptions(this, 'fop{$val}');addRequiredElements({$val});" id="fcol{$val}" name="fcol{$val}" title="">
				<option value="">{'LBL_NONE'|@getTranslatedString:$MODULE}</option>
				{$COLUMNS_BLOCK}
			</select>
			<br />
			<input type="text" name="ffie{$val}" id="ffie{$val}" value="" placeholder="" />
		</td>
		<td width="25%" class="dvtCellLabel">
			<select onchange="addRequiredElements({$val});" style="width:100px;" class="repBox" id="fop{$val}" name="fop{$val}" title="">
				{$CONDITIONS}
			</select>
		</td>
		<td class="dvtCellLabel">
			<input type="text" value="" class="repBox" id="fval{$val}" name="fval{$val}" placeholder="">
			<input type="image" align="absmiddle" src="themes/images/clear_field.gif" alt="Borrar" title="Borrar" onclick="document.getElementById('fval0').value='';return false;" style="cursor: pointer;" />
		</td>
		<td width="60px" class="dvtCellLabel" id="columnconditionglue_0">
			<select class="detailedViewTextBox" id="fcon{$val}" name="fcon{$val}" title="">
				<option value="and">y</option>
				<option value="or">o</option>
			</select>
		</td>
		<td width="30px" class="dvtCellLabel">
			<a href="javascript:;" onclick="deleteColumnRow(1,{$val});">
				<img border="0" align="absmiddle" title="Borrar..." src="themes/images/delete.gif"></a>
		</td>
	</tr>
		{assign var=val value=$val+1}
	{/foreach}
{/foreach}
	<tr id="groupfooter_1">
		<td align="left" colspan="5">
			<input type="button" onclick="addConditionRowNew('1')" value="Nueva Condición" class="crmbutton edit small">
		</td>
	</tr>
	</tbody>
</table>