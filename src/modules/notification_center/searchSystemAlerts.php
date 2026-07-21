<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/notification_center/lib/NotificationHelper.class.php');
	require_once ('modules/notifications/lib/NotificationUtils.class.php');
	require_once ('modules/notifications/lib/NotificationPeriodUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $currentModule, $current_user;

	$searchPeriod = PlatzillaUtils::purify ($_REQUEST, 'viewSystemPeriod');
	$searchFrom   = PlatzillaUtils::purify ($_REQUEST, 'searchFrom');
	$datePeriod   = PlatzillaUtils::purify ($_REQUEST, 'dateSystem');

	if ($searchFrom == 'modalView') {
		$alertsData = array (
			'period' => ($searchPeriod == 'custom') ? $datePeriod : NotificationPeriodUtils::getStandarFiltersStartAndEndDate ($searchPeriod),
			'user'   => $current_user,
		);

		$systemAlerts = NotificationUtils::searchAvailableAlerts ($adb, $alertsData, $current_user);

		foreach ($systemAlerts as $alert) {
			$alert->timeSince = NotificationHelper::timeSince (intval ($alert->getCreatedTime ()));
			$alert->setContents (strip_tags ($alert->getContents ()));
		}

		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('SYSALERTS', $systemAlerts);
		$smarty->display ('modules/notification_center/listSystemAlerts.tpl');
	} else {
		echo json_encode (NotificationPeriodUtils::getStandarFiltersStartAndEndDate ($searchPeriod));
	}
	exit();
