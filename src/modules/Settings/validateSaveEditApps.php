<?php
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$validation = SettingsUtils::purify ($_REQUEST, 'validation');
	$code       = SettingsUtils::purify ($_REQUEST, 'app_code');
	$id         = SettingsUtils::purify ($_REQUEST, 'appid');
	$name       = SettingsUtils::purify ($_REQUEST, 'app_name');

	if (!in_array ($validation, array ('norepeatnameapp', 'norepeatnameappEdit'))) {
		return;
	}

	if ($validation == 'norepeatnameapp') {
		// Validando nombre de App repetido (creación)
		$sql        = 'SELECT * FROM vtiger_config_applications WHERE app_code=? OR app_name=?';
		$parameters = array ($code, $name);
	} else {
		// Validando nombre de App repetido (modificación de registro)
		$sql        = 'SELECT * FROM vtiger_config_applications WHERE (app_code=? OR app_name=?) AND config_applicationsid<>?';
		$parameters = array ($code, $name, $id);
	}
	$result = $adb->pquery ($sql, $parameters);
	echo ($result) && ($adb->num_rows ($result) > 0) ? 'repeated' : 'norepeat';
	return;
