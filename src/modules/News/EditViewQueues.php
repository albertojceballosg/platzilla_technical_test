<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/News/lib/AdQueueHelper.class.php');

	global $app_strings, $current_user, $mod_strings, $theme, $site_URL;

	setBugSnag ($site_URL);

	$queueId = PlatzillaUtils::purify ($_GET, 'record');

	$smarty = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	try {
		$queue = AdQueueHelper::getInstance();

		$smarty->assign ('AVAILABLE_PERIODS', $queue->availablePeriods ());
		$smarty->assign ('AVAILABLE_STATUS', $queue->availableStatus ());
		$smarty->assign ('AD_QUEUE', $queue->fetchAdQueueById ($queueId));
		$smarty->assign ('MOD', $mod_strings);
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php?module=News&action=ListView&parenttab=Settings');
		$smarty->display ('Message.tpl');
	}
	$smarty->display ('Smarty/templates/centaurus/modules/News/EditViewQueues.tpl');
