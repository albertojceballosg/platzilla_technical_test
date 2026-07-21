<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/utils.php');
	require_once ('user_privileges/default_module_view.php');

	global $app_strings, $current_language, $mod_strings, $singlepane_view, $theme;

	$smarty = new vtigerCRM_Smarty;
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('CMOD', $mod_strings);
	$smarty->assign ('IMAGE_PATH', "themes/{$theme}/images");
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	$smarty->assign ('THEME', $theme);
	$smarty->assign ('ViewStatus', $singlepane_view == 'true' ? 'enabled' : 'disabled');
	$smarty->display ('DefModuleView.tpl');
