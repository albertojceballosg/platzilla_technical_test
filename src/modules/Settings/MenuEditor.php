<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/utils.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $app_strings, $mod_strings, $theme;

	$disableModule = SettingsUtils::purify ($_REQUEST, 'module_disable');
	$enableModule  = SettingsUtils::purify ($_REQUEST, 'module_enable');
	$isAjaxRequest = SettingsUtils::purify ($_REQUEST, 'ajax') ? true : false;
	$moduleName    = SettingsUtils::purify ($_REQUEST, 'module_name');

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('MOD', $mod_strings);
	if ($isAjaxRequest) {
		$smarty->assign ('ALLMENUS', getAllMenuModules ());
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ASSIGNED_VALUES', getTopMenuModules ());
		$smarty->assign ('THEME', $theme);
		$smarty->display ('Settings/MenuEditorAssign.tpl');
	} else {
		$smarty->display ('Settings/MenuEditor.tpl');
	}
