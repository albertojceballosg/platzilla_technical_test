<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/News/lib/AdQueueHelper.class.php');
	require_once ('modules/News/lib/NewsUtils.php');

	global $adb, $currentModule, $mod_strings, $site_URL;

	setBugSnag ($site_URL);

	$page         = PlatzillaUtils::purify ($_GET, 'page', 1);
	$returnAction = PlatzillaUtils::purify ($_GET, 'return_action', 'ListView');
	$returnModule = PlatzillaUtils::purify ($_GET, 'return_module', $currentModule);
	$selectedTab  = PlatzillaUtils::purify ($_GET, 'tab', 'news-tab');

	$smarty = new vtigerCRM_Smarty ();
	try {
		$queue = AdQueueHelper::getInstance();
		$smarty->assign ('AVAILABLE_PERIODS', $queue->availablePeriods ());
		$smarty->assign ('AVAILABLE_STATUS', $queue->availableStatus ());
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('DATA', $queue->fetchPagedNewsData (null, $page));
		$smarty->assign ('AD_QUEUES', $queue->fetchAdQueus (true));
		$smarty->assign ('RETURN_ACTION', $returnAction);
		$smarty->assign ('SELECTED_TAB', $selectedTab);
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->display ('modules/News/ListView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', "index.php?module=News&action=ListView&parenttab=Settings&return_module={$returnModule}&return_action={$returnAction}");
		$smarty->display ('Message.tpl');
	}
