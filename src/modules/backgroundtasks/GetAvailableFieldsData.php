<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksUtils.class.php');

	global $adb;

	$moduleName = PlatzillaUtils::purify ($_GET, 'modulename');
	$fields     = BackgroundTasksUtils::getAvailableFieldsData ($adb, $moduleName);

	header ('HTTP/1.1 200 OK');
	header ('Content-Type: application/json');
	echo json_encode ($fields);
	exit ();
