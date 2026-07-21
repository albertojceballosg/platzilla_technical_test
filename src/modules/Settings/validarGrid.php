<?php
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$gridLabel  = SettingsUtils::purify ($_REQUEST, 'etiquetaGrid');
	$gridName   = SettingsUtils::purify ($_REQUEST, 'nombreGrid');
	$moduleName = SettingsUtils::purify ($_REQUEST, 'modulename');

	$result = $adb->pquery ('SELECT * FROM vtiger_field WHERE (fieldname=? OR fieldlabel=?) AND tablename=?', array ($gridName, $gridLabel, "vtiger_$moduleName"));

	echo ($result) && ($adb->num_rows ($result) > 0) ? 'field_exists' : 'dont_exists';
	exit ();
