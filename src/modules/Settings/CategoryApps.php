<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/CategoryApplicationsHelper.class.php');

	global $adb, $app_strings, $current_language, $mod_strings, $theme;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$categories = CategoryApplicationsHelper::getApplicationCategories ($adb);

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('APPSIMAGE_PATH', 'storage/appsimages/');
	$smarty->assign ('CMOD', $mod_strings);
	$smarty->assign ('CONFIGCATAPPLICATION', $categories);
	$smarty->assign ('IMAGE_PATH', "themes/{$theme}/images/");
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	$smarty->assign ('THEME', $theme);
	// Si hay errores borrando, se notifica al usuario
	if ((isset ($_SESSION ['error_borrado'])) && ($_SESSION ['error_borrado'] != '')) {
		$smarty->assign ('MSG_ERROR', $_SESSION ['error_borrado']);
		unset ($_SESSION ['error_borrado']);
	}
	// Si hay errores actualizando, se notifica al usuario
	if ((isset ($_SESSION ['error_update'])) && ($_SESSION ['error_update'] != '')) {
		$smarty->assign ('MSG_ERROR', $_SESSION ['error_update']);
		unset ($_SESSION ['error_update']);
	}
	$smarty->display ('Settings/CategoryApps.tpl');
