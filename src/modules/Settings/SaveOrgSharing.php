<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/UserInfoUtil.php');

	global $adb, $current_user;

	$accesses = PlatzillaUtils::purify ($_POST, 'access');

	if ((is_admin ($current_user)) && (!empty ($accesses))) {
		foreach ($accesses as $moduleName => $permission) {
			$adb->pquery (
				'UPDATE
					vtiger_def_org_share dos
					INNER JOIN vtiger_tab t ON t.tabid=dos.tabid
				SET
					dos.permission=?
				WHERE
					t.name=?',
				array ($permission, $moduleName)
			);
		}
		
		// Regenerar tablas temporales de compartición para todos los usuarios
		RecalculateSharingRules();
	}

	header ('Location: index.php?module=Settings&action=OrgSharingDetailView&parenttab=Settings');
	exit ();
