<?php
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$jsonFields = SettingsUtils::purify ($_REQUEST, 'jsonFields');

	if (!$jsonFields) {
		return;
	}

	$data     = json_decode ($jsonFields, true);
	$sequence = 1;
	$n        = count ($data);
	for ($i = 0; $i < $n; $i++) {
		$adb->pquery ('UPDATE vtiger_field SET sequence=? WHERE fieldid=?', array (($i + 1), $data [ $i ]['id']));
	}
