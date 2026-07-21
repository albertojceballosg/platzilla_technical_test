<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/Translator.class.php');
	require_once ('modules/instancesdatasharing/lib/DataSharingUtils.class.php');

	global $adb, $current_user;

	$smarty = new vtigerCRM_Smarty();
	if (!is_admin ($current_user)) {
		$smarty->assign ('IS_ADMIN', false);
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	$keyword     = PlatzillaUtils::purify ($_GET, 'keyword');
	$page        = PlatzillaUtils::purify ($_GET, 'page');
	$rowsPerPage = 25;

	$smarty->assign ('AVAILABLE_MODULES', DataSharingUtils::fetchAvailableEntityModules ($adb));
	$smarty->assign ('DATA', DataSharingUtils::fetchRules ($adb, $keyword, $page, $rowsPerPage));
	$smarty->assign ('MOD', Translator::getModuleDictionary ('instancesdatasharing'));
	$smarty->assign ('PAGE', $page);
	$smarty->assign ('SEARCH_KEYWORD', $keyword);
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('modules/instancesdatasharing/ListView.tpl');
