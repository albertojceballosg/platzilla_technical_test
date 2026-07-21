<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/Translator.class.php');
	require_once ('modules/Settings/lib/RoleHelper.class.php');

	global $adb, $current_user, $theme;

	$smarty = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('APP', Translator::getApplicationDictionary ());
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	$roleId       = PlatzillaUtils::purify ($_GET, 'roleid');
	$parentRoleId = PlatzillaUtils::purify ($_GET, 'parentroleid');

	$availableProfiles = RoleHelper::fetchAvailableProfiles ($adb);
	if (!empty ($availableProfiles)) {
		usort (
			$availableProfiles,
			function (Profile $profileA, Profile $profileB) {
				return strcmp ($profileA->getName (), $profileB->getName ());
			}
		);
	}
	if (!empty ($_SESSION ['flashmessage']['data'])) {
		$role = Role::getInstance ()
			->setId ($_SESSION ['flashmessage']['data']['roleid'])
			->setName ($_SESSION ['flashmessage']['data']['rolename'])
			->setProfiles (RoleHelper::fetchSelectedProfiles ($adb, $profileIds));
		if (!empty ($_SESSION ['flashmessage']['data']['parentroleid'])) {
			$parentRole = RoleHelper::fetchRole ($adb, $parentRoleId);
		} else {
			$parentRole = null;
		}
		unset ($_SESSION ['flashmessage']['data']);
	} else if (!empty ($roleId)) {
		$role       = RoleHelper::fetchRole ($adb, $roleId);
		$parentRole = $role->getParent ();
	} else {
		$role = null;
		if (!empty ($parentRoleId)) {
			$parentRole = RoleHelper::fetchRole ($adb, $parentRoleId);
		} else {
			$parentRole = null;
		}
	}

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APP', Translator::getApplicationDictionary ());
	$smarty->assign ('AVAILABLE_PROFILES', $availableProfiles);
	$smarty->assign ('MOD', Translator::getModuleDictionary ('Settings'));
	$smarty->assign ('PARENT_ROLE', $parentRole);
	$smarty->assign ('ROLE', $role);
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('Settings/RoleEditView.tpl');
