<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/ProfileManager.php');

	global $adb, $app_strings, $current_language, $mod_strings;

	$profiles = ProfileManager::getInstance ($adb)->fetchProfiles (true);

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('CMOD', $mod_strings);
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	$smarty->assign ('PROFILES', $profiles);
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('Settings/ProfileListView.tpl');
