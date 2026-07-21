<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/UserInfoUtil.php');

	global $adb, $current_user;

	$shareId = PlatzillaUtils::purify ($_REQUEST, 'shareid');

	if ((is_admin ($current_user)) && (!empty ($shareId))) {
		deleteSharingRule ($shareId);
		
		// Regenerar tablas temporales de compartición para todos los usuarios
		RecalculateSharingRules();
	}

	header ('Location: index.php?module=Settings&action=OrgSharingDetailView&parenttab=Settings');
	exit ();
