<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/VtlibUtils.php');

	global $currentModule;

	checkFileAccessForInclusion ("modules/$currentModule/$currentModule.php");
	require_once ("modules/$currentModule/$currentModule.php");

	if(isset($_REQUEST['recordMode'])) {
		$duplicate = vtlib_purify($_REQUEST['recordMode']);
	} else{
		$duplicate = null;
	}

	if(isset($_REQUEST['recordDuplicate'])) {
		$duplicateRecord = vtlib_purify($_REQUEST['recordDuplicate']);
	} else{
		$duplicateRecord = null;
	}

	if(isset($_REQUEST['mode'])) {
		$mode = vtlib_purify($_REQUEST['mode']);
	} else{
		$mode = null;
	}

	if(isset($_REQUEST['record'])) {
		$record = vtlib_purify ($_REQUEST['record']);
	} else{
		$record = null;
	}

	if(isset($_REQUEST['assigntype'])) {
		$assignType = vtlib_purify($_REQUEST['assigntype']);
	} else{
		$assignType = null;
	}

	if(isset($_REQUEST['assigned_user_id'])) {
		$assignedUserId = vtlib_purify($_REQUEST['assigned_user_id']);
	} else{
		$assignedUserId = null;
	}

	if(isset($_REQUEST['assigned_group_id'])) {
		$assignedGroupId = vtlib_purify($_REQUEST['assigned_group_id']);
	} else{
		$assignedGroupId = null;
	}

	if(isset($_REQUEST['search_url'])) {
		$search = vtlib_purify($_REQUEST['search_url']);
	} else{
		$search = '';
	}

	if(isset($_REQUEST['return_action']) && $_REQUEST['return_action'] != '') {
		$returnAction = vtlib_purify($_REQUEST['return_action']);
	} else{
		$returnAction = 'DetailView';
	}

	if(isset($_REQUEST['return_module']) && $_REQUEST['return_module'] != '') {
		$returnModule = vtlib_purify($_REQUEST['return_module']);
	} else{
		$returnModule = $currentModule;
	}

	if(isset($_REQUEST['return_id']) && $_REQUEST['return_id'] != '') {
		$returnId = vtlib_purify($_REQUEST['return_id']);
	} else{
		$returnId = '';
	}

	if(isset($_REQUEST['platdb']) && !empty($_REQUEST ['platdb'])) {
		$urlPlatDb = '&platdb=' . vtlib_purify($_REQUEST['platdb']);
	} else{
		$urlPlatDb = '';
	}

	if(isset($_REQUEST['pagenumber'])) {
		$pageNumber = vtlib_purify($_REQUEST['pagenumber']);
	} else{
		$pageNumber = 1;
	}

	if($duplicate == 'DUPLICATE') {
		$duplicateUrlPart = "&recordMode=DUPLICATE&recordDuplicate={$duplicateRecord}";
	} else{
		$duplicateUrlPart = '';
	}

	$parentTab  = getParentTab ();

	/** @var boxscore|stdClass $focus */
	$focus = new $currentModule ();
	setObjectValuesFromRequest ($focus);
	if ($mode) {
		$focus->mode = $mode;
	}
	if ($record) {
		$focus->id = $record;
	}
	if ($assignType == 'U') {
		$focus->column_fields ['assigned_user_id'] = $assignedUserId;
	} else if ($assignType == 'T') {
		$focus->column_fields ['assigned_user_id'] = $assignedGroupId;
	}

	$focus->save ($currentModule);
	$oldUserId = $focus->getAssignedUserId ($record);
	if ($oldUserId != $assignedUserId) {
		$focus->updateBoxScorePermissions ($oldUserId, $assignedUserId, $record);
	}

	header ("Location: index.php?action=$returnAction&module=$returnModule&record=$returnId&parenttab=$parentTab&start={$pageNumber}{$search}{$urlPlatDb}{$duplicateUrlPart}");
