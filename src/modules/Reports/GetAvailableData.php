<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Reports/lib/ReportUtils.class.php');

	global $adb, $current_user;

	$moduleName         = PlatzillaUtils::purify ($_GET, 'modulename');
	$relatedModuleNames = PlatzillaUtils::purify ($_GET, 'relatedmodulenames');

	$data = array (
		'availablecolumns'      => ReportUtils::getAvailableColumns ($adb, $moduleName, $relatedModuleNames, $current_user),
		'standardfiltercolumns' => ReportUtils::getAvailableStandardFilterColumns ($adb, $moduleName, $relatedModuleNames, $current_user),
		'totalcolumns'          => ReportUtils::getAvailableTotalColumns ($adb, $moduleName, $relatedModuleNames, $current_user),
	);

	header ('HTTP/1.1 200 OK');
	header ('Content-Type: application/json');
	echo json_encode ($data);
	exit ();
