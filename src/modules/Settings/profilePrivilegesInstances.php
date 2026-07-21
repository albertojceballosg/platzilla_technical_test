<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('modules/Settings/lib/ProfileHelper.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $app_strings, $current_language, $mod_strings, $theme;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$mode               = SettingsUtils::purify ($_REQUEST, 'mode');
	$parentProfileId    = SettingsUtils::purify ($_REQUEST, 'parentprofile');
	$profileId          = SettingsUtils::purify ($_REQUEST, 'profileid');
	$profileDescription = SettingsUtils::purify ($_REQUEST, 'profile_description');
	$profileName        = SettingsUtils::purify ($_REQUEST, 'profile_name');
	$radioButton        = SettingsUtils::purify ($_REQUEST, 'radiobutton');
	$returnAction       = SettingsUtils::purify ($_REQUEST, 'return_action');

	// Se verifica y se corrige problemas con modulos creados que no esten registrados en todos los perfiles
	// Este problema se muestra como modulos visibles para perfiles no validos
	$adb->query (
		'INSERT INTO vtiger_profile2tab
			SELECT
				t1.profileid,
				t1.tabid,
				1,
				NULL
			FROM
				(SELECT tabid, profileid FROM vtiger_tab, vtiger_profile) AS t1
				LEFT JOIN vtiger_profile2tab t2 ON t2.tabid=t1.tabid AND t2.profileid=t1.profileid
			WHERE
				t2.tabid IS NULL'
	);

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('CMOD', $mod_strings);
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	$smarty->assign ('MODE', $mode);
	$smarty->assign ('PARENTPROFILEID', $parentProfileId);
	$smarty->assign ('PROFILEID', $profileId);
	$smarty->assign ('PROFILE_DESCRIPTION', $profileId ? getProfileDescription ($profileId) : $profileDescription);
	$smarty->assign ('PROFILE_NAME', getProfileName ($profileId));
	$smarty->assign ('RADIOBUTTON', $radioButton);
	$smarty->assign ('THEME', $theme);
	if ($mode != 'create') {
		$smarty->assign ('GLOBAL_PRIV', ProfileHelper::getGlobalPrivileges ($theme, $mode, $profileId, $parentProfileId));
		$smarty->assign ('FIELD_PRIVILEGES', ProfileHelper::getPrivilegeFields ($adb, $theme, $current_language, $mode, $profileId, $parentProfileId));
		$smarty->assign ('STANDARD_PRIV', ProfileHelper::getStandardPrivileges ($theme, $mode, $profileId, $parentProfileId));
		$smarty->assign ('TAB_PRIV', ProfileHelper::getModulePrivileges ($adb, $theme, $mode, $profileId, $parentProfileId));
		$smarty->assign ('UTILITIES_PRIV', ProfileHelper::getUtilitiesPrivileges ($theme, $mode, $profileId, $parentProfileId));
	}
	if ($mode == 'edit') {
		$smarty->assign ('ACTION', 'UpdateProfileChanges');
	}
	if ($returnAction) {
		$smarty->assign ('RETURN_ACTION', $returnAction);
	}
	$smarty->display ($mode == 'view' ? 'ProfileDetailViewInstances.tpl' : 'EditProfileInstances.tpl');
