<?php
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$subMode = SettingsUtils::purify ($_REQUEST, 'sub_mode');
	if ($subMode != 'priceQueryApps') {
		return;
	}

	$result = $adb->query ("SELECT varvalue FROM vtiger_variables_instancias WHERE varname='module_price'");
	if ((!$result) || ($adb->num_rows ($result) == 0)) {
		return;
	}

	$row = $adb->fetchByAssoc ($result);
	if ($row ['varvalue'] > 0) {
		echo $row ['varvalue'];
	}
