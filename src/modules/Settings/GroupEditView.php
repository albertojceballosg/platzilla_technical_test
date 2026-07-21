<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/GetParentGroups.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $app_strings, $current_language, $mod_strings;

	$groupId = SettingsUtils::purify ($_REQUEST, 'groupId');

	if ($groupId) {
		$group                          = getGroupInfo ($groupId);
		$parentGroups                   = new GetParentGroups ();
		$parentGroups->parent_groups [] = $groupId;
		$parentGroups->getAllParentGroups ($groupId);
		$parentGroups = $parentGroups->parent_groups;

		$members      = array ();
		$groupMembers = $group [2];
		foreach ($groupMembers as $memberType => $memberValue) {
			foreach ($memberValue as $memberId) {
				if ($memberType == 'groups') {
					$memberName    = fetchGroupName ($memberId);
					$memberDisplay = 'group';
				} else if ($memberType == 'roles') {
					$memberName    = getRoleName ($memberId);
					$memberDisplay = 'role';
				} else if ($memberType == 'rs') {
					$memberName    = getRoleName ($memberId);
					$memberDisplay = 'rs';
				} else if ($memberType == 'users') {
					$memberName    = getUserFullName ($memberId);
					$memberDisplay = 'user';
				} else {
					$memberName    = '';
					$memberDisplay = '';
				}
				$members [ $memberType ]["{$memberDisplay}::{$memberId}"] = $memberName;
			}
		}
	} else {
		$group        = null;
		$members      = null;
		$parentGroups = array ();
	}

	// Constructing the Role Array
	$roleDetails = getAllRoleDetails ();
	unset ($roleDetails ['H1']);
	$roles = array ();
	foreach ($roleDetails as $roleId => $roleInfo) {
		$roles [ $roleId ] = $roleInfo [0];
	}

	// Constructing the User Array
	$usersDetails = getAllUserName ();
	$users        = array ();
	foreach ($usersDetails as $userId => $userInfo) {
		$users [ $userId ] = $userInfo;
	}

	// Constructing the Group Array
	$groupsDetails = getAllGroupName ();
	$groups        = array ();
	foreach ($groupsDetails as $id => $groupInfo) {
		if (!in_array ($id, $parentGroups)) {
			$groups [ $id ] = $groupInfo;
		}
	}

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('AVAILABLE_GROUPS', $groups);
	$smarty->assign ('AVAILABLE_ROLES', $roles);
	$smarty->assign ('AVAILABLE_USERS', $users);
	$smarty->assign ('CMOD', $mod_strings);
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	$smarty->assign ('RECORD', $groupId);
	$smarty->assign ('SELECTED_GROUP', $group);
	$smarty->assign ('SELECTED_GROUP_MEMBERS', $members);
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('GroupEditView.tpl');
