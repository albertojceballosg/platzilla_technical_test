<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/Translator.class.php');
	require_once ('modules/Settings/lib/RoleHelper.class.php');

	global $adb, $current_language, $theme;

	$ajaxRequest = PlatzillaUtils::purify ($_REQUEST, 'ajax');

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APP', Translator::getApplicationDictionary ());
	$smarty->assign ('MOD', Translator::getModuleDictionary ('Settings'));
	$smarty->assign ('ROLE_DETAILS', RoleHelper::getAllRoleDetails ());
	$smarty->assign ('ROLES', RoleHelper::buildRoleHierarchy ($adb));
	$smarty->assign ('THEME', $theme);
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	if ($ajaxRequest == 'true') {
		$smarty->display ('Settings/RoleTree.tpl');
	} else {
		$smarty->display ('Settings/RoleListView.tpl');
	}
