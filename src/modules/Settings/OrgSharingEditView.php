<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/Settings/lib/OrgSharingHelper.class.php');

	global $adb, $app_strings, $current_language, $current_user, $mod_strings, $theme;

	if (!is_admin ($current_user)) {
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		return;
	}

	$availableModules = OrgSharingHelper::getModulesDataByName ($adb);
	$availableModuleNames = array_keys ($availableModules);

	$accessPrivileges = OrgSharingHelper::getAccessPrivileges ($adb, $mod_strings, $availableModuleNames);
	usort (
		$accessPrivileges,
		function ($privilegeA, $privilegeB) use ($availableModules) {
			$moduleLabelA = $availableModules [ $privilegeA [0] ]['tablabel'];
			$moduleLabelB = $availableModules [ $privilegeB [0] ]['tablabel'];
			return strcmp ($moduleLabelA, $moduleLabelB);
		}
	);

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('AVAILABLE_MODULES', $availableModules);
	$smarty->assign ('CMOD', $mod_strings);
	$smarty->assign ('DEFAULT_SHARING', $accessPrivileges);
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	$smarty->display ('OrgSharingEditView.tpl');
