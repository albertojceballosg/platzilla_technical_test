<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/News/lib/AdQueueHelper.class.php');

	global $adb, $currentModule, $mod_strings, $theme, $site_URL;

	setBugSnag ($site_URL);

	$record       = PlatzillaUtils::purify ($_GET, 'record');
	$returnAction = PlatzillaUtils::purify ($_GET, 'return_action', 'ListView');
	$returnModule = PlatzillaUtils::purify ($_GET, 'return_module', $currentModule);
	$queue        = AdQueueHelper::getInstance();
	if (!isset ($_SESSION ['flashmessage']['data'])) {
		$newsItemData = $queue->fetchNewsItemData ($record);
	} else {
		$newsItemData = $_SESSION ['flashmessage']['data'];
	}

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('AD_QUEUES', $queue->fetchAdQueus (true));
	$smarty->assign ('AVAILABLE_CUSTOMERS_DATA', $queue->fetchCustomersData ());
	$smarty->assign ('AVAILABLE_PERIODS', $queue->availablePeriods ());
	$smarty->assign ('AVAILABLE_PROVIDERS_DATA', $queue->fetchProvidersData ());
	$smarty->assign ('AVAILABLE_STATUS', $queue->availableStatus ());
	$smarty->assign ('AVAILABLE_USERS_DATA', $queue->fetchUsersData ($adb));
	$smarty->assign ('CATEGORIES', AdQueue::AD_QUEUE_CATEGORIES);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('NEWS_ITEM', $newsItemData);
	$smarty->assign ('RETURN_ACTION', $returnAction);
	$smarty->assign ('RETURN_MODULE', $returnModule);
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('modules/News/EditView.tpl');
