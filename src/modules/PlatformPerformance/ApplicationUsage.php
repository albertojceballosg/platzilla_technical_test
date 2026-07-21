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

	$from = PlatzillaUtils::purify ($_GET, 'from');
	$to   = PlatzillaUtils::purify ($_GET, 'to');

	$dummy = DateTime::createFromFormat ('Y-m-d', $from);
	if ((!($dummy instanceof DateTime)) || ($dummy->format ('Y-m-d') !== $from)) {
		$oneMonthInterval = new DateInterval ('P1M');
		$startDate        = date_create ()->sub ($oneMonthInterval);
	} else {
		$startDate = DateTime::createFromFormat ('Y-m-d', $from);
	}

	$today = date_create ();
	$dummy = DateTime::createFromFormat ('Y-m-d', $to);
	if ((!($dummy instanceof DateTime)) || ($dummy->format ('Y-m-d') !== $to) || ($dummy > $today)) {
		$endDate = date_create ();
	} else {
		$endDate = DateTime::createFromFormat ('Y-m-d', $to);
	}

	$masterAdb = AdbManager::getInstance ()->getMasterAdb ();

	$smarty->assign ('FROM', $startDate->format ('Y-m-d'));
	$smarty->assign ('RECORDS_PER_APPLICATION', PlatformPerformanceUtils::fetchTotalRecordsPerApplication ($masterAdb, $startDate, $endDate));
	$smarty->assign ('RECORDS_PER_CUSTOMER', PlatformPerformanceUtils::fetchTotalRecordsPerCustomer ($masterAdb, $startDate, $endDate));
	$smarty->assign ('RECORDS_PER_MODULE', PlatformPerformanceUtils::fetchTotalRecordsPerModule ($masterAdb, $startDate, $endDate));
	$smarty->assign ('TO', $endDate->format ('Y-m-d'));
	$smarty->assign ('TIME_PER_CUSTOMER', PlatformPerformanceUtils::fetchTotalTimePerCustomer ($masterAdb, $startDate, $endDate));
	$smarty->assign ('VISITS_PER_APPLICATION', PlatformPerformanceUtils::fetchTotalVisitsPerApplication ($masterAdb, $startDate, $endDate));
	$smarty->display ('modules/PlatformPerformance/ApplicationUsage.tpl');