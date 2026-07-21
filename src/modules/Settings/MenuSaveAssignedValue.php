<?php
	require_once ('include/Zend/Json.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $current_language;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$values = SettingsUtils::purify ($_REQUEST, 'values');
	if (!$values) {
		return;
	}

	$values = Zend_Json::decode ($values);
	$adb->pquery ('UPDATE vtiger_tab SET tabsequence=?', array (-1));
	foreach ($values as $value) {
		$adb->pquery ('UPDATE vtiger_tab SET tabsequence=? WHERE tabid=?', array ($value [1], $value [0]));
	}
