<?php
	require_once('modules/Reports/Reports.php');
	require_once('include/logging.php');
	require_once('include/database/PearDatabase.php');

	global $adb;
	$secondarymodule_req = array();

	/** @noinspection PhpUndefinedClassInspection */
	$local_log = LoggerManager::getLogger('index');

	$primarymodule_req = vtlib_purify($_REQUEST['primarymodule']);
	$sec_module_name = $_REQUEST['primarymodule'].'relatedmodule';
	if (isset($_REQUEST[$sec_module_name])) {
	$secondarymodule_req = vtlib_purify($_REQUEST[$sec_module_name]);
	}

	$countSecondaryModuleReq = count($secondarymodule_req);
	if ($countSecondaryModuleReq > 0) {
	$secondarymodule_req = implode(':',$secondarymodule_req);
	}

	if ($primarymodule_req!='') {
	header('Location: index.php?action=NewReport1&module=Reports&primarymodule=$primarymodule_req&secondarymodule=$secondarymodule_req');
	}
