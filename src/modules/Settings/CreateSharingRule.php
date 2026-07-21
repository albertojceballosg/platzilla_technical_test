<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $app_strings, $mod_strings, $theme;

	$mode          = SettingsUtils::purify ($_REQUEST, 'mode');
	$shareId       = SettingsUtils::purify ($_REQUEST, 'shareid');
	$sharingModule = SettingsUtils::purify ($_REQUEST, 'sharing_module');

	// Constructing the Role Array
	$roleDetails = getAllRoleDetails ();
	unset ($roleDetails ['H1']);

	// Constructing the Group Array
	$groupDetails = getAllGroupName ();

	if ($shareId) {
		$shareInformation = getSharingRuleInfo ($shareId);
		$tabId            = $shareInformation [1];
		$sharingModule    = getTabModuleName ($tabId);
	} else {
		$shareInformation = array ('', '', '', '', '', '', '', '');
		$tabId            = getTabid ($sharingModule);
	}

	if ($mode == 'create') {
		$options = array ();
		foreach ($roleDetails as $roleId => $roleName) {
			$options [] = array (
				'text'  => "{$mod_strings ['LBL_ROLES']}::{$roleName [0]}",
				'value' => "roles::{$roleId}",
			);
		}
		foreach ($roleDetails as $roleId => $roleName) {
			$options [] = array (
				'text'  => "{$mod_strings ['LBL_ROLES_SUBORDINATES']}::{$roleName [0]}",
				'value' => "rs::{$roleId}",
			);
		}
		foreach ($groupDetails as $groupId => $groupName) {
			$options [] = array (
				'text'  => "{$mod_strings ['LBL_GROUP']}::{$groupName}",
				'value' => "groups::{$groupId}",
			);
		}
		$fromOptions = $options;
		$toOptions   = $options;

		$shareOptions = array (
			array (
				'text'     => $mod_strings ['Read Only'],
				'value'    => 0,
				'selected' => true,
			),
			array (
				'text'  => $mod_strings ['Read/Write'],
				'value' => 1,
			),
		);
	} else if ($mode == 'edit') {
		// constructing the from combo values
		$fromType = $shareInformation [3];
		$fromId   = $shareInformation [5];

		$fromOptions = array ();
		foreach ($roleDetails as $roleId => $roleName) {
			$option = array (
				'text'  => "{$mod_strings ['LBL_ROLES']}::{$roleName [0]}",
				'value' => "roles::{$roleId}",
			);
			if (($fromType == 'roles') && ($roleId == $fromId)) {
				$option ['selected'] = true;
			}
			$fromOptions [] = $option;
		}
		foreach ($roleDetails as $roleId => $roleName) {
			$option = array (
				'text'  => "{$mod_strings ['LBL_ROLES_SUBORDINATES']}::{$roleName [0]}",
				'value' => "rs::{$roleId}",
			);
			if (($fromType == 'rs') && ($roleId == $fromId)) {
				$option ['selected'] = true;
			}
			$fromOptions [] = $option;
		}
		foreach ($groupDetails as $groupId => $groupName) {
			$option = array (
				'text'  => "{$mod_strings ['LBL_GROUP']}::{$groupName}",
				'value' => "groups::{$groupId}",
			);
			if (($fromType == 'groups') && ($groupId == $fromId)) {
				$option ['selected'] = true;
			}
			$fromOptions [] = $option;
		}

		// constructing the to combo values
		$toType = $shareInformation [4];
		$toId   = $shareInformation [6];

		$toOptions = array ();
		foreach ($roleDetails as $roleId => $roleName) {
			$option = array (
				'text'  => "{$mod_strings ['LBL_ROLES']}::{$roleName [0]}",
				'value' => "roles::{$roleId}",
			);
			if (($toType == 'roles') && ($roleId == $toId)) {
				$option ['selected'] = true;
			}
			$toOptions [] = $option;
		}
		foreach ($roleDetails as $roleId => $roleName) {
			$option = array (
				'text'  => "{$mod_strings ['LBL_ROLES_SUBORDINATES']}::{$roleName [0]}",
				'value' => "rs::{$roleId}",
			);
			if (($toType == 'rs') && ($roleId == $toId)) {
				$option ['selected'] = true;
			}
			$toOptions [] = $option;
		}
		foreach ($groupDetails as $groupId => $groupName) {
			$option = array (
				'text'  => "{$mod_strings ['LBL_GROUP']}::{$groupName}",
				'value' => "groups::{$groupId}",
			);
			if (($toType == 'groups') && ($groupId == $toId)) {
				$option ['selected'] = true;
			}
			$toOptions [] = $option;
		}

		$shareOptions = array (
			array (
				'text'     => $mod_strings ['Read Only'],
				'value'    => 0,
				'selected' => $shareInformation [7] == 0 ? true : false,
			),
			array (
				'text'     => $mod_strings ['Read/Write'],
				'value'    => 1,
				'selected' => $shareInformation [7] == 1 ? true : false,
			),
		);
	}

	$displayModule = $app_strings [ $sharingModule ];

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('DISPLAY_MODULE', $displayModule);
	$smarty->assign ('FROM_OPTIONS', $fromOptions);
	$smarty->assign ('IMAGE_CLOSE_URL', vtiger_imageurl ('close.gif', $theme));
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODE', $mode);
	$smarty->assign ('SHARE_ID', $shareId);
	$smarty->assign ('SHARE_OPTIONS', $shareOptions);
	$smarty->assign ('SHARING_MODULE', $sharingModule);
	$smarty->assign ('TO_OPTIONS', $toOptions);
	$smarty->display ('Settings/CreateSharingRule.tpl');
