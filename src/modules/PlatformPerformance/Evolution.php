<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('modules/PlatformPerformance/lib/PlatformPerformanceUtils.class.php');

	$smarty = new vtigerCRM_Smarty ();
	if (!empty ($_SESSION ['platInstancia'])) {
		$smarty->assign ('IS_ADMIN', false);
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	$masterAdb = AdbManager::getInstance ()->getMasterAdb ();

	$smarty->assign ('EXPIRED_INSTANCES_TOTAL_RECORDS', PlatformPerformanceUtils::fetchExpiredInstancesTotalRecords ($masterAdb));
	$smarty->assign ('EXPIRED_INSTANCES_TOTAL_SESSIONS', PlatformPerformanceUtils::fetchExpiredInstancesTotalSessions ($masterAdb));
	$smarty->display ('modules/PlatformPerformance/Evolution.tpl');