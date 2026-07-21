<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/UserInfoUtil.php');

	global $adb, $app_strings, $current_language, $mod_strings;

	$groupsData = getAllGroupInfo ();
	$headers    = array ($mod_strings ['LBL_LIST_TOOLS'], $mod_strings ['LBL_GROUP_NAME'], $mod_strings ['LBL_DESCRIPTION']);
	$groups   = array ();
	foreach ($groupsData as $groupId => $groupData) {
		$groups [] = array (
			'description' => $groupData [1],
			'groupid'     => $groupId,
			'groupname'   => $groupData [0],
		);
	}

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('CMOD', $mod_strings);
	$smarty->assign ('GROUPS', $groups);
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	$smarty->assign ('USERS', getAllUserName());
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('ListGroup.tpl');
