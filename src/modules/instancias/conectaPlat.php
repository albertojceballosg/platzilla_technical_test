<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $app_strings, $currentModule, $mod_strings;

	$recordId = SettingsUtils::purify ($_REQUEST, 'id');

	$result = $adb->pquery ('SELECT usuario, clave, url, code FROM vtiger_instancias WHERE instanciasid=?', array ($recordId));
	if (($result) && ($adb->num_rows ($result) > 0)) {
		$row      = $adb->fetchByAssoc ($result);
		$url      = "http://{$row ['code']}.platzilla.com";
		$username = $row ['usuario'];
		$password = $row ['clave'];
	} else {
		$url      = null;
		$username = null;
		$password = null;
	}

	$smarty = new vtigerCRM_Smarty;
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODULE', $currentModule);
	$smarty->assign ('URL', $url);
	$smarty->assign ('USER', $username);
	$smarty->assign ('PASS', $password);
	$smarty->display ("modules/{$currentModule}/conectaPlat.tpl");
