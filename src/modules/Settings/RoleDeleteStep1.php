<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/utils.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $app_strings, $current_language, $mod_strings;

	$roleId   = SettingsUtils::purify ($_REQUEST, 'roleid');
	$roleName = isset ($roleId) ? getRoleName ($roleId) : null;

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('CMOD', $mod_strings);
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	$smarty->assign ('ROLEID', $roleId);
	$smarty->assign ('ROLENAME', $roleName);
	$smarty->display ('DeleteRole.tpl');
