<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/instancesdatasharing/lib/DataSharingUtils.class.php');

	global $adb;

	$moduleName = PlatzillaUtils::purify ($_GET, 'modulename');
	try {
		$fieldsData = DataSharingUtils::fetchAvailableFieldsData ($adb, $moduleName);
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ($fieldsData);
	} catch (Exception $e) {
		header ('HTTP/1.1 400 Baad request');
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();
