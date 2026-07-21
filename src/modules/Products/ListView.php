<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/ProductManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $app_strings, $current_user, $mod_strings;

	$smarty = new vtigerCRM_Smarty();
	if ((!empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
		$smarty->assign ('IS_ADMIN', false);
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	$page = PlatzillaUtils::purify ($_GET, 'page');

	$smarty->assign ('DATA', ProductManager::getInstance ($adb)->fetchProducts ($page));
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('PAGE', $page);
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('modules/Products/ListView.tpl');