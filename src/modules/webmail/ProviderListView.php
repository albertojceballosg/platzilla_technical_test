<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');

	global $adb, $app_strings, $current_user, $mod_strings, $theme;

	$smarty = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	$keyword = PlatzillaUtils::purify ($_GET, 'keyword');
	$page    = PlatzillaUtils::purify ($_GET, 'page');

	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('DATA', WebmailUtils::getAvailableEmailProviders ($adb, $keyword, $page));
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('SEARCH_KEYWORD', $keyword);
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('modules/webmail/ProviderListView.tpl');
