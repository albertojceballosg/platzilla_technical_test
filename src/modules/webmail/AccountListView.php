<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');

	global $adb, $current_user;

	$page         = PlatzillaUtils::purify ($_GET, 'page', 1);
	$returnAction = PlatzillaUtils::purify ($_GET, 'return_action', 'AccountListView');
	$returnModule = PlatzillaUtils::purify ($_GET, 'return_module', 'webmail');
	$isInstance   = !empty ($_SESSION ['platInstancia']);

	$smarty = new vtigerCRM_Smarty ();
	try {
		$smarty->assign ('DATA', WebmailUtils::fetchPagedMailAccounts ($adb, $current_user->id, $page, $isInstance));
		$smarty->assign ('RETURN_ACTION', $returnAction);
		$smarty->assign ('RETURN_MODULE', $returnModule);
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->display ('modules/webmail/AccountListView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', "index.php?module=webmail&action=AccountListView&parenttab=Settings&return_module={$returnModule}&return_action={$returnAction}");
		$smarty->display ('Message.tpl');
	}