<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('modules/Settings/lib/WizardUtils.class.php');
	require_once ('vtlib/Vtiger/Menu.php');

	global $adb, $app_strings, $mod_strings, $theme;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$data       = SettingsUtils::purify ($_REQUEST, 'data');
	$getJson    = SettingsUtils::purify ($_REQUEST, 'getJSON');
	$publicName = SettingsUtils::purify ($_REQUEST, 'nombrePublico');
	$moduleName = SettingsUtils::purify ($_REQUEST, 'module');
	$saveTree   = SettingsUtils::purify ($_REQUEST, 'saveTree');

	if ($saveTree) {
		if ($publicName) {
			$menu = new Vtiger_Menu ();
			$menu->createInstance ($publicName);
		} else if (($saveTree) && (WizardUtils::updateParentModulesTable ($adb))) {
			WizardUtils::rebuildParentTab ($adb, $data);
		}

		header ('Content-Type: text/html; charset=UTF-8');
		$smartyDlg = new vtigerCRM_Smarty ();
		$smartyDlg->assign ('MOD', $mod_strings);
		$smartyDlg->assign ('MODULE', $moduleName);
		$smartyDlg->assign ('THEME', $theme);
		$smartyDlg->assign ('PARENT_MODULES', WizardUtils::getFoldersAndModules ($adb));
		$smartyDlg->display ('Settings/ModuleManager/dlgAdminParentModules.tpl');
	} else if ($getJson) {
		header ('Content-Type: text/html; charset=UTF-8');
		echo WizardUtils::getFoldersAndModules ($adb);
		exit ();
	}
