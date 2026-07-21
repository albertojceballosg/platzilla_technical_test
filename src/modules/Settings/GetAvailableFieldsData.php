<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/GetFieldPropertiesHelper.class.php');

	global $adb;

	$moduleName = PlatzillaUtils::purify ($_GET, 'modulename');
	$fields     = GetFieldPropertiesHelper::getAvailableFieldsData ($adb, $moduleName);

	header ('HTTP/1.1 200 OK');
	header ('Content-Type: application/json');
	echo json_encode ($fields);
	exit ();
