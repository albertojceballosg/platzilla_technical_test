<?php
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$recordId = SettingsUtils::purify ($_REQUEST, 'record');

	if ($adb->pquery ('DELETE FROM vtiger_kpisboxscore WHERE kpisboxscoreid=?', array ($recordId))) {
		header ('Location: index.php?module=Settings&action=kpisBoxscore&parenttab=Settings');
	} else {
		$_SESSION ['error_borrado'] = 'El botón no ha podido ser eliminado! Intente nuevamente!';
		header ("Location: index.php?module=Settings&action=DetailKpisBoxscore&parenttab=Settings&record={$recordId}");
	}
