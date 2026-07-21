<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/UserInfoUtil.php');

	global $adb, $current_user;

	$groupId    = PlatzillaUtils::purify ($_POST, 'groupid');
	$transferTo = PlatzillaUtils::purify ($_POST, 'transferto');

	if ((is_admin ($current_user)) && (!empty ($groupId)) && (!empty ($transferTo))) {
		deleteGroup ($groupId, $transferTo);
	}

	header ('Location: index.php?module=Settings&action=listgroups&parenttab=Settings');
	exit ();
