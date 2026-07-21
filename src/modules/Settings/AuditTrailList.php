<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/utils.php');
	require_once ('user_privileges/audit_trail.php');

	global $app_strings, $audit_trail, $current_language, $mod_strings, $theme;

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('CMOD', $mod_strings);
	$smarty->assign ('IMAGE_PATH', "themes/{$theme}/images");
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	$smarty->assign ('THEME', $theme);
	$smarty->assign ('USERLIST', getUserslist ());
	$smarty->assign ('AuditStatus', $audit_trail == 'true' ? 'enabled' : 'disabled');
	$smarty->display ('AuditTrailList.tpl');
