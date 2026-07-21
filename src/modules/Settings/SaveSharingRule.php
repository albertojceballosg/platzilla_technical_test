<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $current_user;

	$moduleName = SettingsUtils::purify ($_REQUEST, 'sharemodule');

	if ((!is_admin ($current_user)) || (!$moduleName)) {
		header ('Location: index.php?module=Settings&action=OrgSharingDetailView&parenttab=Settings');
		exit ();
	}

	$access  = SettingsUtils::purify ($_REQUEST, 'access');
	$owner   = SettingsUtils::purify ($_REQUEST, 'owner');
	$shareId = SettingsUtils::purify ($_REQUEST, 'shareid');
	$to      = SettingsUtils::purify ($_REQUEST, 'to');

	$owner = $owner ? explode ('::', $owner) : null;
	$to    = $to ? explode ('::', $to) : null;

	$shareEntityType       = isset ($owner [0]) ? $owner [0] : null;
	$shareEntityId         = isset ($owner [1]) ? $owner [1] : null;
	$toEntityType          = isset ($to [0]) ? $to [0] : null;
	$toEntityId            = isset ($to [1]) ? $to [1] : null;
	$moduleId              = getTabid ($moduleName);
	$relatedSharingModules = getRelatedSharingModules ($moduleId);

	if ((isset ($shareId)) && ($shareId !== '')) {
		updateSharingRule ($shareId, $moduleId, $shareEntityType, $toEntityType, $shareEntityId, $toEntityId, $access);
		// Adding the Related ModulePermission Sharing
		foreach ($relatedSharingModules as $relatedModuleId => $ignored) {
			$relatedModuleName        = getTabModuleName ($relatedModuleId);
			$relatedSharingPermission = SettingsUtils::purify ($_REQUEST, "{$relatedModuleName}_accessopt");
			updateRelatedModuleSharingPermission ($shareId, $moduleId, $relatedModuleId, $relatedSharingPermission);
		}
	} else {
		$shareId = addSharingRule ($moduleId, $shareEntityType, $toEntityType, $shareEntityId, $toEntityId, $access);
		// Adding the Related ModulePermission Sharing
		foreach ($relatedSharingModules as $relatedModuleId => $ignored) {
			$relatedModuleName        = getTabModuleName ($relatedModuleId);
			$relatedSharingPermission = SettingsUtils::purify ($_REQUEST, "{$relatedModuleName}_accessopt");
			addRelatedModuleSharingPermission ($shareId, $moduleId, $relatedModuleId, $relatedSharingPermission);
		}
	}
	
	// Regenerar tablas temporales de compartición para todos los usuarios
	RecalculateSharingRules();
	
	header ('Location: index.php?module=Settings&action=OrgSharingDetailView&parenttab=Settings');
	exit ();
