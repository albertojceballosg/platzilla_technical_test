<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/ModuleManagerHelper.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $app_strings, $current_language, $mod_strings, $theme;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$categoryId = SettingsUtils::purify ($_REQUEST, 'record');

	$result = $adb->pquery ('SELECT * FROM vtiger_category_apps WHERE catappid=?', array ($categoryId));
	$row    = ($result) && ($adb->num_rows ($result) > 0) ? $adb->fetchByAssoc ($result) : null;

	$smarty = new vtigerCRM_smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('APPSIMAGE_PATH', 'storage/appsimages/');
	$smarty->assign ('AVAILABLE_MENUS', ModuleManagerHelper::fetchAvailableMenus ($adb));
	$smarty->assign ('CMOD', $mod_strings);
	$smarty->assign ('CONFIGAPPLICATION', $row);
	$smarty->assign ('IMAGE_PATH', "themes/{$theme}/images/");
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	$smarty->assign ('RECORD', $categoryId);
	$smarty->assign ('THEME', $theme);
	$smarty->display ('Settings/EditCatApp.tpl');
