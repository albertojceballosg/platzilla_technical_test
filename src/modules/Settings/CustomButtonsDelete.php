<?php
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;
	if ((!isset ($adb)) || (!$adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$recordId = SettingsUtils::purify ($_REQUEST, 'record');

	if ($adb->pquery ('DELETE FROM vtiger_custombuttons WHERE custombuttonid=?', array ($recordId))) {
		header ('Location: index.php?parenttab=Settings&module=Settings&action=CustomButtons');
	} else {
		$_SESSION ['error_borrado'] = 'El botón no ha podido ser eliminado! Intente nuevamente!';
		header ("Location: index.php?parenttab=Settings&module=Settings&action=DetailCustomButtons&record={$recordId}");
	}
	exit ();
