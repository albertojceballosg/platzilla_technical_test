<?php
	require_once ('modules/Settings/lib/AddFieldMatrixHelper.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $currentModule;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$moduleName = SettingsUtils::purify ($_REQUEST, 'fldmodule');
	$name       = SettingsUtils::purify ($_REQUEST, 'nombreMatrix');
	$label      = SettingsUtils::purify ($_REQUEST, 'etiquetaMatrix');
	$rows       = SettingsUtils::purify ($_REQUEST, 'field_rows');
	$cols       = SettingsUtils::purify ($_REQUEST, 'field_cols');

	AddFieldMatrixHelper::updateFields ($adb, $moduleName, $name, $label, $rows, $cols);
	exit ();
