<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('modules/panelusuarios/lib/UsersHelper.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $app_strings, $current_user, $currentModule, $default_language, $mod_strings;

	$smarty = new vtigerCRM_Smarty();
	if (!is_admin ($current_user)) {
		$smarty->assign ('IS_ADMIN', false);
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}
	
	$smarty->assign ('ADVANCED_OPTION', SettingsUtils::checkAdvancedOptions ($adb));
	$smarty->assign ('IS_ADMIN', is_admin ($current_user));
	$smarty->assign ('LIMIT', UsersHelper::getUsersLimit (isset ($_SESSION ['platInstancia']) ? $_SESSION ['platInstancia'] : null));
	$smarty->assign ('MOD', return_module_language ($default_language, 'Settings'));
	$smarty->assign ('USERS', UsersHelper::getUsers ($adb));
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('modules/panelusuarios/ListView.tpl');
