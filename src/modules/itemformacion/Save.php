<?php
	global $current_user, $currentModule;

	$plat = '';
	if (isset($_SESSION['plat'])) {
		$plat = $_SESSION['plat'] . '/';
	}
	if (file_exists ($plat."modules/$currentModule/ListView.php")) {
		checkFileAccessForInclusion($plat."modules/$currentModule/$currentModule.php");
		require_once($plat."modules/$currentModule/$currentModule.php");
	} else {
		checkFileAccessForInclusion("modules/$currentModule/$currentModule.php");
		require_once("modules/$currentModule/$currentModule.php");
	}

	$focus = new $currentModule();
	setObjectValuesFromRequest($focus);

	$mode = $_REQUEST['mode'];
	$record=$_REQUEST['record'];
	if($mode) {
		$focus->mode = $mode;
	}
	if($record) {
		$focus->id  = $record;
	}

	if($_REQUEST['assigntype'] == 'U') {
		$focus->column_fields['assigned_user_id'] = $_REQUEST['assigned_user_id'];
	} else if ($_REQUEST['assigntype'] == 'T') {
		$focus->column_fields['assigned_user_id'] = $_REQUEST['assigned_group_id'];
	}

	/** @noinspection PhpUndefinedMethodInspection */
	$focus->save($currentModule);
	$returnId = $focus->id;

	$search = vtlib_purify($_REQUEST['search_url']);

	$parentTab = getParentTab();
	if($_REQUEST['return_module'] != '') {
		$returnModule = vtlib_purify($_REQUEST['return_module']);
	} else {
		$returnModule = $currentModule;
	}

	if($_REQUEST['return_action'] != '') {
		$returnAction = vtlib_purify($_REQUEST['return_action']);
	} else {
		$returnAction = 'DetailView';
	}

	if($_REQUEST['return_id'] != '') {
		$returnId = vtlib_purify($_REQUEST['return_id']);
	}

	if (isset($_REQUEST['platdb']) && !empty($_REQUEST['platdb'])) {
		$urlPlatDb = '&platdb='.vtlib_purify($_REQUEST['platdb']);
	}

	header("Location: index.php?action=$returnAction&module=$returnModule&record=$returnId&parenttab=$parentTab&start=".vtlib_purify($_REQUEST['pagenumber']).$search.$urlPlatDb);
