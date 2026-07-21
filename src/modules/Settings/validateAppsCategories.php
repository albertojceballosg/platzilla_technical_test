<?php
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;

	$code       = SettingsUtils::purify ($_REQUEST, 'app_code');
	$name       = SettingsUtils::purify ($_REQUEST, 'app_name');
	$sql        = 'SELECT * FROM vtiger_category_apps WHERE (code=? OR name=?)';
	$parameters = array ($code, $name);
	$result     = $adb->pquery ($sql, $parameters);
	echo json_encode ($adb->num_rows ($result) > 0 ? 0 : 1);
	exit ();
