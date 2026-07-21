<?php
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$clickAction = SettingsUtils::purify ($_REQUEST, 'clickaction');
	$linkAction  = SettingsUtils::purify ($_REQUEST, 'linkaction');
	$moduleLabel = SettingsUtils::purify ($_REQUEST, 'title');
	$moduleName  = SettingsUtils::purify ($_REQUEST, 'modulo');
	$recordId    = SettingsUtils::purify ($_REQUEST, 'record');
	$validation  = SettingsUtils::purify ($_REQUEST, 'validation');

	$linkAction = !empty ($linkAction) ? str_replace ('|', '?', str_replace ('!', '&', $linkAction)) : null;

	if (!in_array ($validation, array ('norepeatnamebutton', 'norepeatnamebuttonEdit'))) {
		return;
	}

	if ($validation == 'norepeatnamebutton') {
		// Validando nombre de botón repetido (creación)
		$sql        = 'SELECT * FROM vtiger_custombuttons WHERE module=? AND (label=? OR onclick=? OR link=?)';
		$parameters = array ($moduleName, $moduleLabel, $clickAction, $linkaction);
	} else {
		// Validando nombre de botón repetido (modificación de registro)
		$sql        = 'SELECT * FROM vtiger_custombuttons WHERE module=? AND custombuttonid<>? AND (label=? OR onclick=? OR link=?)';
		$parameters = array ($moduleName, $recordId, $moduleLabel, $clickAction, $linkaction);
	}
	$result = $adb->pquery ($sql, $parameters);
	echo ($result) && ($adb->num_rows ($result) > 0) ? 'repeated' : 'norepeat';
	return;
