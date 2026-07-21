{strip}
<style type="text/css">
{literal}
	.label-input > label {
		font-size:   1.11em;
		font-weight: 300;
		line-height: 20px;
		margin:      0;
		padding:     6px 0;
	}
	.btn {
		margin-left: 5px;
	}
	.filters > .panel-group {
		margin-bottom: 0;
		margin-top:    5px;
	}
	.filters > .panel-group > .panel-default > .panel-heading {
		background-color: transparent;
		border:           1px solid #dedede;
		color:            #333333;
		position: relative;
	}
	.filters > .panel-group > .panel-default > .panel-heading > .panel-title > a:focus {
		color: #333333;
	}
	.filters > .panel-group > .panel-default > .panel-collapse > .list-group > .conditiongroup {
		border: 1px solid #dedede;
		margin: 5px;
		padding-bottom: 0;
		padding-top: 0;
	}
	.filters > .panel-group > .panel-default > .panel-collapse > .list-group > .conditiongroupglue {
		border: 0;
		padding: 0 5px;
	}
	.filters > .panel-group > .panel-default > .panel-collapse > .list-group > .conditiongroup > .conditiongroupheader {
		border-bottom: 1px solid #dedede;
		list-style: none;
	}
	.filters > .panel-group > .panel-default > .panel-collapse > .list-group > .conditiongroup > .conditiongroupbody {
		list-style: none;
		padding: 10px 0;
	}
	.filters > .panel-group > .panel-default > .panel-collapse > .list-group > .conditiongroup > .conditiongroupbody > .condition {
		margin-bottom: 3px;
	}
	.conditiongroupfooter {
		margin: 10px 5px;
	}
	.btn-delete-group {
		position: absolute;
		right: 0;
		top: 0;
	}
	.table > tbody > tr:first-child > td {
		color:          #545e69 !important;
		text-transform: uppercase;
	}
	.table > tbody > tr:first-child > td:first-child {
		font-size: 0.875em;
		font-weight: 400 !important;
	}
	.main-box {
		box-shadow:    0px 0px 0px 0 #FFFFFF !important;
		border-radius: 0px !important;
	}
	.base-list-container {
		background-color: #ffffff;
		margin:           0px -13px!important;
		border-top:       1px solid #D8D8D8 !important;
		height:           auto;
		min-height:       1200px !important;
	}
{/literal}
</style>
<script type="text/javascript">
{literal}
	var rel_fields = {/literal}{$REL_FIELDS}{literal};
{/literal}
</script>
<script type="text/javascript" src="include/js/json.js"></script>
<script type="text/javascript" src="include/js/advancefilter.js"></script>
<script type="text/javascript" src="modules/Reports/Reports.js"></script>
<div class="row module-buttons">
	<div class="col-lg-12">
		<div class="pull-left" style="float: left;">
			<h1><a href="index.php?module={$MODULE}&action=index">{$MODULE|@getTranslatedString: $MODULE}</a></h1>
		</div>
		<div class="pull-right" align="margin-right">
			<button type="button" class="btn btn-primary" onclick="goToPrintReport ({$REPORTID});">{$MOD.LBL_PRINT_REPORT}</button>
			<div class="btn-group pull-right" style="right: -0.5em; margin-bottom: 10px;">
				<button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
					Acciones...
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu" role="menu">
					<li>
						<a href="javascript:;" title="{$MOD.LBL_SAVE_REPORT_AS}" onclick="saveReportAs (this, 'duplicateReportLayout');"><i class="fa fa-save"></i> {$MOD.LBL_SAVE_REPORT_AS}</a>
					</li>
					<li>
						<a href="javascript:;" title="{$MOD.LBL_EXPORTPDF_BUTTON}" onclick="goToURL (CrearEnlace ('CreatePDF', {$REPORTID}));"><i class="fa fa-file-pdf-o"></i> {$MOD.LBL_EXPORTPDF_BUTTON}</a>
					</li>
					<li>
						<a href="javascript:;" title="{$MOD.LBL_EXPORTXL_BUTTON}" onclick="goToURL (CrearEnlace ('CreateXL', {$REPORTID}));"><i class="fa fa-file-excel-o"></i> {$MOD.LBL_EXPORTXL_BUTTON}</a>
					</li>
{foreach from=$REPORT_LINKS item=link}
					<li>
						<a href="{$link->linkurl}"><img src="{$link->linkicon|@vtiger_imageurl:$THEME}" align="abmiddle" alt="{$link->linklabel|getTranslatedString}" title="{$link->linklabel|getTranslatedString}" border="0" style="width:22px;"></a>
					</li>
{/foreach}
				</ul>
			</div>
		</div>
	</div>
</div>
	<div class="container-fluid base-list-container">
<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<header class="main-box-header clearfix">
				<div class="row">
					<h2 style="margin: 0;" class="pull-left">{if ($MOD.$REPORTNAME != '')}{$MOD.$REPORTNAME}{else}{$REPORTNAME}{/if}: <span id='_reportrun_total'>{$REPORTHTML.1}</span> {$APP.LBL_RECORDS}</h2>
					<div class="pull-right">
						<form action="index.php" method="get">
							<input type="hidden" name="module" value="Reports" />
							<input type="hidden" name="action" value="SaveAndRun" />
							<select name="record" class="form-control" onchange="selectReport (this)" title="{$MOD.LBL_SELECT_ANOTHER_REPORT}">
								<option disabled="disabled" value="">{$MOD.LBL_SELECT_ANOTHER_REPORT}</option>
{foreach key=report_in_fld_id item=report_in_fld_name from=$REPINFOLDER}
								<option value="{$report_in_fld_id}"{if $report_in_fld_id == $REPORTID} selected="selected"{/if}>{if $MOD.$report_in_fld_name != ''}{$MOD.$report_in_fld_name}{else}{$report_in_fld_name}{/if}</option>
{/foreach}
							</select>
						</form>
					</div>
				</div>
				<div class="row filters">
					<div class="panel-group">
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title"><a data-toggle="collapse" href="#filters">Filtrar por...</a></h4>
							</div>
							<div id="filters" class="panel-collapse collapse">
								<ul id="conditiongroups" class="list-group"></ul>
								<div class="action-bar text-center">
									<button type="button" class="btn btn-link" onclick="addNewConditionGroup ('conditiongroups')"><i class="fa fa-plus"></i></button>
								</div>
								<div class="action-bar text-center" style="padding: 5px 0;">
									<button type="button" class="btn btn-primary" onclick="generateReport ({$REPORTID});">{$MOD.LBL_GENERATE_NOW}</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</header>
			<div id="Generate">
{include file="ReportRunContents.tpl"}
			</div>
		</div>
	</div>
</div>
	</div>
<div id="duplicateReportLayout" class="modal fade in" style="display: none;" role="dialog">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true" onclick="fninvsh ('duplicateReportLayout');">X</button>
			<h4 class="modal-title">{$MOD.LBL_SAVE_REPORT_AS}</h4>
		</div>
		<div class="row modal-body">
			<div class="col-md-12">
				<div class="col-md-4">
					<div class="label-input">
						<label for="newreportname">{$MOD.LBL_REPORT_NAME}</label>
					</div>
				</div>
				<div class="form-group col-md-8">
					<div class="input-group" style="width: 100%;">
						<input type="text" id="newreportname" name="newreportname" class="form-control" value="" />
					</div>
				</div>
			</div>
			<div class="col-md-12">
				<div class="col-md-4">
					<div class="label-input">
						<label for="reportfolder">{$MOD.LBL_REP_FOLDER}</label>
					</div>
				</div>
				<div class="form-group col-md-8">
					<div class="input-group" style="width: 100%;">
						<select name="reportfolder" id="reportfolder" class="form-control">
{foreach $REP_FOLDERS as $folder}
							<option value="{$folder.id}"{if ($FOLDERID == $folder.id)} selected="selected"{/if}>{$folder.name}</option>
{/foreach}
						</select>
					</div>
				</div>
			</div>
			<div class="col-md-12">
				<div class="col-md-4">
					<div class="label-input">
						<label for="newreportdescription">{$MOD.LBL_DESCRIPTION}</label>
					</div>
				</div>
				<div class="form-group col-md-8">
					<div class="input-group" style="width: 100%;">
						<textarea name="newreportdescription" id="newreportdescription" class="form-control" rows="5">{$REPORTDESC}</textarea>
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" name="save" class="btn btn-success" onclick="duplicateReport ({$REPORTID}); fninvsh ('duplicateReportLayout');">{$APP.LBL_SAVE_BUTTON_LABEL}</button>
			<button type="button" name="cancel" class="btn btn-warning" onclick="fninvsh ('duplicateReportLayout');">{$APP.LBL_CANCEL_BUTTON_LABEL}</button>
		</div>
	</div>
</div>
{if empty ($JS_DATEFORMAT)}
	{assign var="JS_DATEFORMAT" value=$APP.NTC_DATE_FORMAT|@parse_calendardate}
{/if}
<input type="hidden" id="jscal_dateformat" name="jscal_dateformat" value="{$JS_DATEFORMAT}" />
<input type="hidden" id="image_path" name="image_path" value="{$IMAGE_PATH}" />
<input type="hidden" name="advft_criteria" id="advft_criteria" value="" />
<input type="hidden" name="advft_criteria_groups" id="advft_criteria_groups" value="" />
<script type="text/javascript">
	function addColumnConditionGlue (columnIndex) {ldelim}
		var columnConditionGlueElement = document.getElementById ('columnconditionglue_' + columnIndex);
		if (columnConditionGlueElement) {ldelim}
			columnConditionGlueElement.innerHTML = '<select name="fcon' + columnIndex + '" id="fcon' + columnIndex + '" class="form-control">' +
												   		'<option value="and">{'LBL_CRITERIA_AND'|@getTranslatedString: $MODULE}</option>' +
														'<option value="or">{'LBL_CRITERIA_OR'|@getTranslatedString: $MODULE}</option>' +
												   '</select>';
		{rdelim}
	{rdelim}

	function addConditionRow (groupIndex) {ldelim}
		var i;
		var groupColumns = column_index_array[ groupIndex ];
		if (typeof(groupColumns) != 'undefined') {ldelim}
			for (i = groupColumns.length - 1; i >= 0; --i) {ldelim}
				var prevColumnIndex = groupColumns[ i ];
				if (document.getElementById ('conditioncolumn_' + groupIndex + '_' + prevColumnIndex)) {ldelim}
					addColumnConditionGlue (prevColumnIndex);
					break;
				{rdelim}
			{rdelim}
		{rdelim}

		var columnIndex = advft_column_index_count + 1;
		var nextNode = document.getElementById ('conditiongroupbody_' + groupIndex);

		var newNode = document.createElement ('li');
		newNode.setAttribute ('id', 'conditioncolumn_' + groupIndex + '_' + columnIndex);
		newNode.setAttribute ('name', 'conditionColumn');
		newNode.className = 'row condition';
		nextNode.appendChild (newNode, nextNode);

		var node1 = document.createElement ('div');
		node1.className = 'col-xs-3';
		newNode.appendChild (node1);
		node1.innerHTML = '<select name="fcol' + columnIndex + '" id="fcol' + columnIndex + '" onchange="updatefOptions (this, \'fop' + columnIndex + '\'); addRequiredElements (' + columnIndex + ');" class="form-control">' +
								'<option value="">{'LBL_NONE'|@getTranslatedString: $MODULE}</option>' +
								'{$COLUMNS_BLOCK}' +
						  '</select>';

		var node2 = document.createElement ('div');
		node2.className = 'col-xs-3';
		newNode.appendChild (node2);
		node2.innerHTML = '<select name="fop' + columnIndex + '" id="fop' + columnIndex + '" class="form-control" onchange="addRequiredElements (' + columnIndex + ');">' +
								'<option value="">{'LBL_NONE'|@getTranslatedString: $MODULE}</option>' +
								'{$FOPTION}' +
						  '</select>';

		var availableFilterFields = {if !empty ($AVAILABLE_FILTER_FIELDS)}{$AVAILABLE_FILTER_FIELDS|@json_encode nofilter}{else}null{/if};
		var availableFilterFieldsHtml = '';
		if (availableFilterFields) {
			for (var availableFilterFieldValue in availableFilterFields) {
				availableFilterFieldsHtml += '<li><a class="dropdown-item" href="javascript:;" onclick="document.getElementById (\'fval' + columnIndex + '\').value = \'' + availableFilterFieldValue + '\'">' + availableFilterFields[availableFilterFieldValue] + '</a></li>';
			}
		}

		var node3 = document.createElement ('div');
		node3.className = 'col-xs-4';
		newNode.appendChild (node3);
		node3.innerHTML = '<div class="input-group">' +
						  		'<div class="input-group-btn">' +
							  		'<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Campo <span class="caret"></span></button></button>' +
									'<ul class="dropdown-menu" role="menu">' +
						  				availableFilterFieldsHtml +
									'</ul>' +
								'</div>' +
								'<input name="fval' + columnIndex + '" id="fval' + columnIndex + '" class="form-control" type="text" value="" />' +
							'</div>';

		var node4 = document.createElement ('div');
		node4.setAttribute ('id', 'columnconditionglue_' + columnIndex);
		node4.className = 'col-xs-1';
		newNode.appendChild (node4);

		var node5 = document.createElement ('div');
		node5.className = 'col-xs-1 text-right';
		newNode.appendChild (node5);
		node5.innerHTML = '<button type="button" class="btn btn-link" onclick="deleteColumnRow (' + groupIndex + ', ' + columnIndex + ');">' +
						  		'<i class="fa fa-trash-o"></i>' +
						  '</button>';

		if (document.getElementById ('fcol' + columnIndex)) updatefOptions (document.getElementById ('fcol' + columnIndex), 'fop' + columnIndex);
		if (typeof(column_index_array[ groupIndex ]) == 'undefined') column_index_array[ groupIndex ] = [];
		column_index_array[ groupIndex ].push (columnIndex);
		advft_column_index_count++;
	{rdelim}

	function addGroupConditionGlue (groupIndex) {ldelim}
		var groupConditionGlueElement = document.getElementById ('groupconditionglue_' + groupIndex);
		if (groupConditionGlueElement) {ldelim}
			groupConditionGlueElement.innerHTML = '<select name="gpcon' + groupIndex + '" id="gpcon' + groupIndex + '" class="form-control">' +
												  		'<option value="and">{'LBL_CRITERIA_AND'|@getTranslatedString: $MODULE}</option>' +
												  		'<option value="or">{'LBL_CRITERIA_OR'|@getTranslatedString: $MODULE}</option>' +
												  '</select>';
		{rdelim}
	{rdelim}

	function addConditionGroup (parentNodeId) {ldelim}
		for (var i = group_index_array.length - 1; i >= 0; --i) {ldelim}
			var prevGroupIndex = group_index_array[ i ];
			if (document.getElementById ('conditiongroup_' + prevGroupIndex)) {ldelim}
				addGroupConditionGlue (prevGroupIndex);
				break;
			{rdelim}
		{rdelim}

		var groupIndex = advft_group_index_count + 1;
		var parentNode = document.getElementById (parentNodeId);

		var newNode = document.createElement ('li');
		newNode.setAttribute ('id', 'conditiongroup_' + groupIndex);
		newNode.setAttribute ('name', 'conditionGroup');
		newNode.className = 'list-group-item conditiongroup';
		newNode.innerHTML = '<div id="conditiongroupheader_' + groupIndex + '" class="row conditiongroupheader">' +
								'<h4 class="pull-left">Grupo de condiciones</h4>' +
								'<button type="button" class="btn btn-link pull-right" onclick="deleteGroup (' + groupIndex + ');"><i class="fa fa-trash-o"></i></button>' +
							'</div>' +
							'<ul id="conditiongroupbody_' + groupIndex + '" class="conditiongroupbody"></ul>' +
							'<div id="conditiongroupfooter_' + groupIndex + '" class="row conditiongroupfooter text-center">' +
								'<button type="button" class="btn btn-link" onclick="addConditionRow (' + groupIndex + ')"><i class="fa fa-plus"></i></button>' +
							'</div>';
		parentNode.appendChild (newNode);

		var glueNode = document.createElement ('li');
		glueNode.setAttribute ('id', 'groupconditionglue_' + groupIndex);
		glueNode.className = 'list-group-item conditiongroupglue';
		parentNode.appendChild (glueNode);

		group_index_array.push (groupIndex);
		advft_group_index_count++;
	{rdelim}
</script>
{foreach key=GROUP_ID item=GROUP_CRITERIA from=$CRITERIA_GROUPS}
	{assign var=GROUP_COLUMNS value=$GROUP_CRITERIA.columns}
<script type="text/javascript">
	addConditionGroup ('conditiongroups');
</script>
	{foreach key=COLUMN_INDEX item=COLUMN_CRITERIA from=$GROUP_COLUMNS}
<script type="text/javascript">
	addConditionRow ('{$GROUP_ID}');
	var conditionColumnRowElement = document.getElementById ('fcol' + advft_column_index_count);
	conditionColumnRowElement.value = '{$COLUMN_CRITERIA.columnname}';
	updatefOptions (conditionColumnRowElement, 'fop' + advft_column_index_count, '{$COLUMN_CRITERIA.columnname}');
	document.getElementById ('fop' + advft_column_index_count).value = '{$COLUMN_CRITERIA.comparator}';
	addRequiredElements (advft_column_index_count);

	var columnvalue = "{$COLUMN_CRITERIA.value|@addslashes}";
	if ('{$COLUMN_CRITERIA.comparator}' == 'bw' && columnvalue != '') {ldelim}
		var values = columnvalue.split (",");
		document.getElementById ('fval' + advft_column_index_count).value = values[ 0 ];
		if (values.length == 2 && document.getElementById ('fval_ext' + advft_column_index_count)) {ldelim}
			document.getElementById ('fval_ext' + advft_column_index_count).value = values[ 1 ];
			{rdelim}
		{rdelim} else {ldelim}
		document.getElementById ('fval' + advft_column_index_count).value = columnvalue;
		{rdelim}
</script>
	{/foreach}
	{foreach key=COLUMN_INDEX item=COLUMN_CRITERIA from=$GROUP_COLUMNS}
<script type="text/javascript">
	if (document.getElementById ('fcon{$COLUMN_INDEX}')) document.getElementById ('fcon{$COLUMN_INDEX}').value = '{$COLUMN_CRITERIA.column_condition}';
</script>
	{/foreach}
{foreachelse}
<script type="text/javascript">
	addNewConditionGroup ('conditiongroups');
</script>
{/foreach}
{foreach key=GROUP_ID item=GROUP_CRITERIA from=$CRITERIA_GROUPS}
<script type="text/javascript">
	if (document.getElementById ('gpcon{$GROUP_ID}')) document.getElementById ('gpcon{$GROUP_ID}').value = '{$GROUP_CRITERIA.condition}';
</script>
{/foreach}
{/strip}