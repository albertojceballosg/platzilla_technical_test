<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $app_strings, $current_language, $mod_strings, $theme;

	$parentRoleId = SettingsUtils::purify ($_REQUEST, 'parent');
	$roleId       = SettingsUtils::purify ($_REQUEST, 'roleid');
	$mode         = SettingsUtils::purify ($_REQUEST, 'mode', '');
	$returnAction = SettingsUtils::purify ($_REQUEST, 'returnaction');

	$profileDetails = getAllProfileInfo ();
	if ($roleId !== null) {
		$roleInfo        = getRoleInformation ($roleId);
		$roleName        = $roleInfo [ $roleId ][0];
		$isCustomer      = $roleInfo [ $roleId ][4] == '1' ? 'checked="checked"' : '';
		$isPartner       = $roleInfo [ $roleId ][5] == '1' ? 'checked="checked"' : '';
		$parentRoleId    = $roleInfo [ $roleId ][3];
		$parentRoleName  = getRoleName ($parentRoleId);
		$relatedProfiles = getRoleRelatedProfiles ($roleId);
	} else if ($parentRoleId !== null) {
		$roleName        = '';
		$isCustomer      = '';
		$isPartner       = '';
		$mode            = 'create';
		$parentRoleName  = getRoleName ($parentRoleId);
		$relatedProfiles = null;
	} else {
		$roleName        = '';
		$isCustomer      = '';
		$isPartner       = '';
		$parentRoleName  = '';
		$relatedProfiles = null;
	}

	if ($mode == 'edit') {
		$selectedProfiles = array ();
		foreach ($relatedProfiles as $profileId => $profileName) {
			$selectedProfiles [] = $profileId;
			$selectedProfiles [] = $profileName;
		}
		$selectedProfiles = array_chunk ($selectedProfiles, 2);
	} else {
		$selectedProfiles = null;
	}

	$profileEntries = array ();
	foreach ($profileDetails as $profileId => $profileName) {
		$profileEntries [] = $profileId;
		$profileEntries [] = $profileName;
	}
	$profileEntries = array_chunk ($profileEntries, 2);

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('CMOD', $mod_strings);
	$smarty->assign ('CUSTOMER_PARTNER', $isPartner);
	$smarty->assign ('CUSTOMER_ROLE', $isCustomer);
	$smarty->assign ('IMAGE_PATH', "themes/{$theme}/images/");
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	$smarty->assign ('MODE', $mode);
	$smarty->assign ('PARENT', $parentRoleId);
	$smarty->assign ('PARENTNAME', $parentRoleName);
	$smarty->assign ('PROFILELISTS', $profileEntries);
	$smarty->assign ('RETURN_ACTION', $returnAction);
	$smarty->assign ('ROLEID', $roleId);
	$smarty->assign ('ROLENAME', $roleName);
	$smarty->assign ('THEME', $theme);
	if ($selectedProfiles) {
		$smarty->assign ('SELPROFILELISTS', $selectedProfiles);
	}
	$smarty->display ('RoleEditView.tpl');
