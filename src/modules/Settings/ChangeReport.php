<?php
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$tabId = SettingsUtils::purify ($_REQUEST, 'tabid');
	if (!$tabId) {
		return;
	}

	$isAvailable = SettingsUtils::purify ($_REQUEST, 'availableReport') === 'true' ? true : false;

	$result = $adb->pquery ('SELECT tabid FROM vtiger_module_report WHERE tabid=?', array ($tabId));
	if (($result) && ($adb->num_rows ($result) > 0)) {
		$adb->pquery ('UPDATE vtiger_module_report SET reportavailable=? WHERE tabid=?', array ($isAvailable, $tabId));
	} else {
		$adb->pquery ('INSERT INTO vtiger_module_report (tabid, reportavailable) VALUES (?, ?)', array ($tabId, $isAvailable));
	}

	echo 'status_change';
