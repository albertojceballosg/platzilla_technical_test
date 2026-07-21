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

	$from            = PlatzillaUtils::purify ($_GET, 'from');
	$minimumRecords  = PlatzillaUtils::purify ($_GET, 'records');
	$minimumSessions = PlatzillaUtils::purify ($_GET, 'sessions');
	$to              = PlatzillaUtils::purify ($_GET, 'to');

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

	$masterAdb                          = AdbManager::getInstance ()->getMasterAdb ();
	$dailyRegistrations                 = PlatformPerformanceUtils::fetchTotalDailyRegistrations ($masterAdb, $startDate, $endDate);
	$dailySubscriptions                 = PlatformPerformanceUtils::fetchTotalDailySubscriptions ($masterAdb, $startDate, $endDate);
	$dailyRegistrationsAndSubscriptions = array ();
	foreach ($dailyRegistrations as $date => $registrations) {
		$dailyRegistrationsAndSubscriptions [ $date ] = array (
			'Registros'     => $registrations,
			'Suscripciones' => isset ($dailySubscriptions [ $date ]) ? $dailySubscriptions [ $date ] : 0,
		);
	}

	$smarty->assign ('DAILY_REGISTRATIONS', $dailyRegistrations);
	$smarty->assign ('DAILY_REGISTRATIONS_VS_SUBSCRIPTIONS', $dailyRegistrationsAndSubscriptions);
	$smarty->assign ('DAILY_SUBSCRIPTIONS', $dailySubscriptions);
	$smarty->assign ('EVOLUTION', PlatformPerformanceUtils::fetchOnboardingEvolutionData ($masterAdb, $startDate, $endDate, $minimumSessions, $minimumRecords));
	$smarty->assign ('FROM', $startDate->format ('Y-m-d'));
	$smarty->assign ('MINIMUM_RECORDS', $minimumRecords);
	$smarty->assign ('MINIMUM_SESSIONS', $minimumSessions);
	$smarty->assign ('OFFER_DATA', PlatformPerformanceUtils::fetchOfferData ($masterAdb, $startDate, $endDate));
	$smarty->assign ('REGISTRATIONS_VS_SUBSCRIPTIONS', PlatformPerformanceUtils::fetchRegistrationsVsSubscriptionsData ($masterAdb, $startDate, $endDate));
	$smarty->assign ('TO', $endDate->format ('Y-m-d'));
	$smarty->display ('modules/PlatformPerformance/OnboardAnalysis.tpl');