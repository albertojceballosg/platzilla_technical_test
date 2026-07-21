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

	if (($minimumRecords === null) || ($minimumRecords < 0)) {
		$minimumRecords = 5;
	}

	if (($minimumSessions === null) || ($minimumSessions < 0)) {
		$minimumSessions = 1;
	}

	$masterAdb = AdbManager::getInstance ()->getMasterAdb ();

	$smarty->assign ('DAILY_REGISTRATIONS_PER_SOURCE', PlatformPerformanceUtils::fetchRegistrationsBySourceData ($masterAdb, $startDate, $endDate));
	$smarty->assign ('FROM', $startDate->format ('Y-m-d'));
	$smarty->assign ('REGISTRATIONS_PER_SOURCE', PlatformPerformanceUtils::fetchTotalRegistrationsPerSource ($masterAdb, $startDate, $endDate));
	$smarty->assign ('TO', $endDate->format ('Y-m-d'));
	$smarty->display ('modules/PlatformPerformance/ChannelAnalysis.tpl');