<input type="hidden" name="advft_criteria_color" id="advft_criteria_color" value="" />
<input type="hidden" name="advft_criteria_groups_color" id="advft_criteria_groups_color" value="" />

<script language="JavaScript" type="text/JavaScript">
	function addColumnConditionGlueColor(columnIndex) {ldelim}
		var columnConditionGlueElement = document.getElementById('ccolumnconditionglue_'+columnIndex);

		if(columnConditionGlueElement) {ldelim}
			columnConditionGlueElement.innerHTML = "<select name='fccon"+columnIndex+"' id='fccon"+columnIndex+"' class='form-control' style='padding: 0px;'>"+
				"<option value='and'>{'LBL_CRITERIA_AND'|@getTranslatedString:$MODULE}</option>"+
				"<option value='or'>{'LBL_CRITERIA_OR'|@getTranslatedString:$MODULE}</option>"+
				"</select>";
			{rdelim}
	{rdelim}

function addConditionRowColor(groupIndex) {ldelim}

    var groupColumns = ccolumn_index_array[groupIndex];
    if(typeof(groupColumns) != 'undefined') {ldelim}
        for(var i=groupColumns.length - 1; i>=0; --i) {ldelim}
            var prevColumnIndex = groupColumns[i];
            if(document.getElementById('colorconditioncolumn_'+groupIndex+'_'+prevColumnIndex)) {ldelim}
                addColumnConditionGlueColor(prevColumnIndex);
                break;
                {rdelim}
            {rdelim}
        {rdelim}

	var columnIndex = cadvft_column_index_count+1;
	var nextNode = document.getElementById('cgroupfooter_'+groupIndex);

	var newNode = document.createElement('tr');
	newNodeId = 'colorconditioncolumn_'+groupIndex+'_'+columnIndex;
  	newNode.setAttribute('id',newNodeId);
  	newNode.setAttribute('name','colorconditioncolumn');
	nextNode.parentNode.insertBefore(newNode, nextNode);

	node1 = document.createElement('td');
	node1.setAttribute('class', 'dvtCellLabel');
	node1.setAttribute('width', '25%');
	newNode.appendChild(node1);
	{if $SOURCE eq 'reports'}
		node1.innerHTML = '<select name="fccol'+columnIndex+'" id="fccol'+columnIndex+'" onchange="updatefOptions(this, \'fcop'+columnIndex+'\');addRequiredElements('+columnIndex+');updateRelFieldOptions(this, \'fcval_'+columnIndex+'\');" class="form-control">'+
							'<option value="">{'LBL_NONE'|@getTranslatedString:$MODULE}</option>'+
	        				'{$COLUMNS_BLOCK}'+
						'</select>';
	{else}
		node1.innerHTML = "<select name='fccol"+columnIndex+"' id='fccol"+columnIndex+"' onchange='updatefOptions(this, \"fcop"+columnIndex+"\");addRequiredElements("+columnIndex+");' class='form-control'>"+
							"<option value=''>{'LBL_NONE'|@getTranslatedString:$MODULE}</option>"+
	        				"{$COLUMNS_BLOCK}"+
						"</select>";
	{/if}
    "</select>";
	node2 = document.createElement('td');
	node2.setAttribute('class', 'dvtCellLabel');
	node2.setAttribute('width', '25%');
	newNode.appendChild(node2);
	node2.innerHTML = '<select name="fcop'+columnIndex+'" id="fcop'+columnIndex+'" class="form-control" onchange="addRequiredElements('+columnIndex+');">'+
							'<option value="">{'LBL_NONE'|@getTranslatedString:$MODULE}</option>'+
							'{$FOPTION}'+
						'</select>';

	node3 = document.createElement('td');
	node3.setAttribute('class', 'dvtCellLabel');
	newNode.appendChild(node3);
	{if $SOURCE eq 'reports'}
		node3.innerHTML = '<input name="fcval'+columnIndex+'" id="fcval'+columnIndex+'" class="form-control" type="text" value="">'+
						'<img height=20 width=20 align="absmiddle" style="cursor: pointer;" title="{$APP.LBL_FIELD_FOR_COMPARISION}" alt="{$APP.LBL_FIELD_FOR_COMPARISION}" src="themes/images/terms.gif" onClick="hideAllElementsByName(\'relFieldsPopupDiv\'); fnvshobj(this,\'show_val'+columnIndex+'\');"/>'+
						'<input type="image" align="absmiddle" style="cursor: pointer;" onclick="document.getElementById(\'fcval'+columnIndex+'\').value=\'\';return false;" language="javascript" title="{$APP.LBL_CLEAR}" alt="{$APP.LBL_CLEAR}" src="themes/images/clear_field.gif"/>'+
							'<div id="show_val'+columnIndex+'" name="relFieldsPopupDiv" style="display:none;" class="modal fade in" role="dialog">'+
								'<div class="modal-content">'+
									'<div class="modal-header">'+
										'<button type="button" class="close" data-dismiss="modal" aria-hidden="true" onclick="fninvsh(\'show_val'+columnIndex+'\');">X</button>'+
										'<h4 class="modal-title">{$MOD.LBL_SELECT_FIELDS}</h4>'+
									'</div>'+
									'<div class="modal-body">'+
										'<table border=0 cellspacing=0 cellpadding=5 width=95% align=center class="table">'+ 
											'<tr>'+
												'<td>'+
													'<table border=0 celspacing=0 cellpadding=5 width=100% align=center class="table">'+
														'<tr>'+
															'<td align="right" nowrap><b>{$MOD.LBL_RELATED_FIELDS} </b></td>'+
															'<td align="left">'+
																'<select name="fcval_'+columnIndex+'" id="fcval_'+columnIndex+'" onChange="AddFieldToFilter('+columnIndex+',this);" class="form-control">'+
																	'<option value="">{$MOD.LBL_NONE}</option>'+
																	'{$REL_FIELDS}'+
																'</select>'+
															'</td>'+
														'</tr>'+
													'</table>'+
												'</td>'+
											'</tr>'+
										'</table>'+
										'<table border=0 cellspacing=0 cellpadding=5 width=100%>'+
											'<tr>'+
												'<td class="small" align="center">'+
													'<input name="button" value=" &nbsp;{$APP.LBL_DONE}&nbsp; " class="btn btn-success btn-sm" onclick="hideAllElementsByName(\'relFieldsPopupDiv\');" type="button" title="{$APP.LBL_DONE}">&nbsp;&nbsp;'+
													'<input name="cancel" value=" {$APP.LBL_CANCEL_BUTTON_LABEL} " class="btn btn-warning btn-sm" onclick="fninvsh(\'show_val'+columnIndex+'\');" type="button">'+
												'</td>'+
											'</tr>'+
										'</table>'+
									'</div>'+
								'</div>'+
							'</div>';
	{else} 
		node3.innerHTML ='<div class="input-group">'+ 
						'<input name="fcval'+columnIndex+'" id="fcval'+columnIndex+'" class="form-control" type="text" value="">'+
						'<div class="input-group-addon" onclick="document.getElementById(\'fcval'+columnIndex+'\').value=\'\';return false;" title="{$APP.LBL_CLEAR}" alt="{$APP.LBL_CLEAR}"><i class="fa fa-eraser"></i></div>'+
						'</div>';
	{/if}

	node4 = document.createElement('td');
	node4.setAttribute('class', 'dvtCellLabel');
	node4.setAttribute('id', 'ccolumnconditionglue_'+columnIndex);
	node4.setAttribute('width', '60px');
	newNode.appendChild(node4);

	node5 = document.createElement('td');
	node5.setAttribute('class', 'dvtCellLabel');
	node5.setAttribute('width', '30px');
	newNode.appendChild(node5);
	node5.innerHTML = '<a onclick="deleteColumnRow('+groupIndex+','+columnIndex+',\'color\');" href="javascript:;">'+
							'<img src="themes/images/delete.gif" align="absmiddle" title="{$MOD.LBL_DELETE}..." border="0">'+
						'</a>';

	if(document.getElementById('fccol'+columnIndex)) updatefOptions(document.getElementById('fccol'+columnIndex), 'fcop'+columnIndex);
	if(typeof(ccolumn_index_array[groupIndex]) == 'undefined') ccolumn_index_array[groupIndex] = [];
	ccolumn_index_array[groupIndex].push(columnIndex);
	cadvft_column_index_count++;

{rdelim}

    function addGroupConditionGlueColor(groupIndex) {ldelim}

        var groupConditionGlueElement = document.getElementById('cgroupconditionglue_'+groupIndex);
        if(groupConditionGlueElement) {ldelim}
            groupConditionGlueElement.innerHTML = "<div class='form-group'><label>{'LBL_CRITERIA_COLOR'|@getTranslatedString:$MODULE}</label><input name='gcpcon"+groupIndex+"' id='gcpcon"+groupIndex+"' type='color' class='form-control' value='#f3f3f3'/></div>";
            {rdelim}
        {rdelim}



function addConditionGroupColor(parentNodeId) {ldelim}

	var iniColor = "";

    var groupIndex = cadvft_group_index_count+1;
    var parentNode = document.getElementById(parentNodeId);

    var newNode = document.createElement('div');
    newNodeId = 'cconditiongroup_'+groupIndex;
    newNode.setAttribute('id',newNodeId);
    newNode.setAttribute('name','cconditionGroup');

    iniColor = "<table class='table' border='0' cellpadding='5' cellspacing='1' width='100%' valign='top'>"+
            "<tr><td align='center' id='cgroupconditionglue_"+(groupIndex)+"'>"+
            "</td></tr>"+
            "</table>";

    newNode.innerHTML =iniColor+
		"<table class='table' border='0' cellpadding='5' cellspacing='1' width='100%' valign='top' id='cconditiongrouptable_"+groupIndex+"'>"+
        "<tr id='cgroupheader_"+groupIndex+"'>"+
        "<td colspan='5' align='right'>"+
        "<a href='javascript:void(0);' onclick='deleteGroupColor(\""+groupIndex+"\");'><img border=0 src={'close.gif'|@vtiger_imageurl:$THEME} alt='{$APP.LBL_DELETE_GROUP}' title='{$APP.LBL_DELETE_GROUP}'/></a>"+
        "</td>"+
        "</tr>"+
        "<tr id='cgroupfooter_"+groupIndex+"'>"+
        "<td colspan='5' align='left'>"+
        "<input type='button' class='btn btn-success btn-sm' value='{$APP.LBL_NEW_CONDITION}' onclick='addConditionRowColor(\""+groupIndex+"\")' />"+
        "</td>"+
        "</tr>"+
        "</table>"+
        "<table class='table' border='0' cellpadding='5' cellspacing='1' width='100%' valign='top'>"+
        "<tr><td align='center' id='cgroupconditionglue_"+groupIndex+"'>"+
        "</td></tr>"+
        "</table>";

    parentNode.appendChild(newNode);

    addGroupConditionGlueColor((groupIndex));

    cgroup_index_array.push(groupIndex);
    cadvft_group_index_count++;
    {rdelim}

</script>

<div id='adv_filter_div_color' name='adv_filter_div_color'>
	<table class="table">
		<tr>
			<td class="detailedViewHeader" align="left"><b>{'LBL_ADVANCED_COLOR'|@getTranslatedString:$MODULE}</b></td>
		</tr>
        {if $FUNCTION neq 'panelProperties' && $FUNCTION neq 'panelColumnProperties'}
			<tr>
				<td colspan="2" align="right">
					<input type="button" class="btn btn-success btn-sm" value="{'LBL_NEW_GROUP'|@getTranslatedString:$MODULE}" onclick="addNewConditionGroupColor('adv_filter_div_color')" />
				</td>
			</tr>
        {/if}
	</table>
	{foreach key=GROUP_ID item=GROUP_CRITERIA from=$CRITERIA_GROUPS}
		{assign var=GROUP_COLUMNS value=$GROUP_CRITERIA.columns}
		{foreach key=COLUMN_INDEX item=COLUMN_CRITERIA from=$GROUP_COLUMNS}
		<script type="text/javascript">
			addConditionRowColor('{$GROUP_ID}');
			var conditionColumnRowElement = document.getElementById('fccol'+cadvft_column_index_count);
			conditionColumnRowElement.value = '{$COLUMN_CRITERIA.columnname}';
			updatefOptions(conditionColumnRowElement, 'fcop'+cadvft_column_index_count,'{$COLUMN_CRITERIA.columnname}');
			document.getElementById('fcop'+cadvft_column_index_count).value = '{$COLUMN_CRITERIA.comparator}';
			addRequiredElements(cadvft_column_index_count);
			{if $SOURCE eq 'reports'}
				updateRelFieldOptions(conditionColumnRowElement, 'fcval_'+cadvft_column_index_count);
			{/if}

			var columnvalue = "{$COLUMN_CRITERIA.value|@addslashes}";
			if('{$COLUMN_CRITERIA.comparator}' == 'bw' && columnvalue != '') {ldelim}
				var values = columnvalue.split(",");
				document.getElementById('fcval'+cadvft_column_index_count).value = values[0];
				if(values.length == 2 && document.getElementById('fcval_ext'+cadvft_column_index_count))
					document.getElementById('fcval_ext'+cadvft_column_index_count).value = values[1];
			{rdelim} else {ldelim}
				document.getElementById('fcval'+cadvft_column_index_count).value = columnvalue;
			{rdelim}
		</script>
		{/foreach}
		{foreach key=COLUMN_INDEX item=COLUMN_CRITERIA from=$GROUP_COLUMNS}
		<script type="text/javascript">
			if(document.getElementById('fccon{$COLUMN_INDEX}')) document.getElementById('fccon{$COLUMN_INDEX}').value = '{$COLUMN_CRITERIA.column_condition}';
		</script>
		{/foreach}
	{foreachelse}
		<script type="text/javascript">
            addNewConditionGroupColor('adv_filter_div_color');
		</script>
	{/foreach}
	{foreach key=GROUP_ID item=GROUP_CRITERIA from=$CRITERIA_GROUPS}
	<script type="text/javascript">
		if(document.getElementById('gcpcon{$GROUP_ID}')) document.getElementById('gcpcon{$GROUP_ID}').value = '{$GROUP_CRITERIA.condition}';
	</script>
	{/foreach}
</div>
