<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Reports/lib/ReportUtils.class.php');

	global $adb;

	$moduleName = PlatzillaUtils::purify ($_GET, 'modulename');

	if (!empty ($moduleName)) {
		$relatedModules = ReportUtils::getRelatedModulesByName ($adb, $moduleName);
	} else {
		$relatedModules = null;
	}

	header ('HTTP/1.1 200 OK');
	header ('Content-Type: application/json');
	echo json_encode ($relatedModules);
	exit ();