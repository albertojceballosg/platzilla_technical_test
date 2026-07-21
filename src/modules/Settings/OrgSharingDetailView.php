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

	$availableModules     = OrgSharingHelper::getModulesDataByName ($adb);
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

	$customAccess = array ();
	foreach ($availableModuleNames as $availableModuleName) {
		$customAccess [ $availableModuleName ] = OrgSharingHelper::getSharingRules ($adb, $availableModuleName);
	}
	uksort (
		$customAccess,
		function ($ruleA, $ruleB) use ($availableModules) {
			$moduleLabelA = $availableModules [ $ruleA ]['tablabel'];
			$moduleLabelB = $availableModules [ $ruleB ]['tablabel'];
			return strcmp ($moduleLabelA, $moduleLabelB);
		}
	);

	// Constructing the Role Array
	$roleDetails = getAllRoleDetails ();
	unset ($roleDetails ['H1']);

	// Constructing the Group Array
	$groupDetails = getAllGroupName ();

	$options = array ();
	foreach ($roleDetails as $roleId => $roleName) {
		$options ["roles::{$roleId}"] = array (
			'text'  => "Rol {$roleName [0]}",
			'value' => "roles::{$roleId}",
		);
	}
	foreach ($roleDetails as $roleId => $roleName) {
		$options ["rs::{$roleId}"] = array (
			'text'  => "Rol {$roleName [0]} y subordinados",
			'value' => "rs::{$roleId}",
		);
	}
	foreach ($groupDetails as $groupId => $groupName) {
		$options ["groups::{$groupId}"] = array (
			'text'  => "Grupo {$groupName}",
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

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('AVAILABLE_MODULES', $availableModules);
	$smarty->assign ('CMOD', $mod_strings);
	$smarty->assign ('DEFAULT_SHARING', $accessPrivileges);
	$smarty->assign ('FROM_OPTIONS', $fromOptions);
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	$smarty->assign ('MODSHARING', $customAccess);
	$smarty->assign ('SHARE_OPTIONS', $shareOptions);
	$smarty->assign ('TO_OPTIONS', $toOptions);
	$smarty->display ('OrgSharingDetailView.tpl');
