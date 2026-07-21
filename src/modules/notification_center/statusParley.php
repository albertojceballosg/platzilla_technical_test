<?php
	require_once ('include/platzilla/Managers/ParleyManager.php');
	require_once ('include/platzilla/Objects/NotificationInterface.php');
	require_once ('include/utils/DataViewUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Courses/lib/CourseManager.php');
	require_once ('modules/notification_center/lib/NotificationHelper.class.php');
	require_once ('modules/notifications/lib/NotificationPeriodUtils.class.php');
	require_once ('modules/notifications/lib/NotificationUtils.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');

	global $adb, $currentModule, $current_user;

	if ((!isset($_SESSION['show_notifications'])) || empty ($_SESSION ['show_notifications'])) {
		$_SESSION['show_notifications'] = 'on';
		$_SESSION['last_notifications'] = 0;
	}
	$masterAdb        = AdbManager::getInstance ()->getMasterAdb ();
	$option           = PlatzillaUtils::purify ($_REQUEST, 'parley');
	$objParleyManager = ParleyManager::getInstance ($adb);
	$searchPeriod     = NotificationPeriodUtils::getStandarFiltersStartAndEndDate ('last30days');
	$alertsData       = array (
		'period'             => $searchPeriod,
		'user'               => $current_user,
		'platform'           => $_SESSION ['plat'],
		'show_notifications' => $_SESSION['show_notifications'],
		
	);
	/**  $totalParley, fuera de servisio por ahora
	$totalParley = $objParleyManager->getAllNewParley (
		ParleyHistories::getInstance ()
			->setUsersId ($current_user->id)
	);
	*/
	$totalParley = 0;
	$totalEmails = WebmailUtils::getMailCountByStatus ($adb, $current_user->id);
	/** fueara de servicio por ahora */
	//$totalCourse = CourseManager::getInstance ($masterAdb)->getTotalNewCourseByUser ($current_user->id);
	$totalCourse = 0;
	/** fueara de servicio por ahora */
	//$totalTasks  = DataViewUtils::getTotalNewTasks($adb, 'Calendar');
	$totalTasks = 0;
	if ($option == 'get') {
		$notificationsGroup = NotificationUtils::searchAvailableAlerts ($adb, $alertsData, $current_user);
		$notifyData         = array ();
		foreach ($notificationsGroup as $notify) {
			$notifyData [] = array (
				'action'   => strtolower ($notify->getAction ()),
				'contenet' => strip_tags ($notify->getContents ()),
			);
		}

		$totalNotifications = count ($notificationsGroup);

		if ($_SESSION ['last_notifications'] < ($totalNotifications + $totalParley)) {
			$_SESSION ['show_notifications'] = 'on';
			$_SESSION ['last_notifications'] = ($totalNotifications + $totalParley);
		}
		$_SESSION ['show_notifications'] = 'off';
		
		$notifyData['totalNotify'] = $totalNotifications;
		$notifyData['totalParley'] = $totalParley;
		$notifyData['totalEmails'] = $totalEmails;
		$notifyData['totalCourse'] = $totalCourse;
		$notifyData['totalTasks']  = $totalTasks;
		$notifyData['show']        = $_SESSION ['show_notifications'];
		echo json_encode ($notifyData);
		$_SESSION ['show_notifications'] = ($_SESSION ['show_notifications'] == 'on') ? 'off' : $_SESSION ['show_notifications'];
	} else if ($option == 'set') {
		$_SESSION ['last_notifications'] = ($_SESSION ['last_notifications'] >= $totalParley) ? ($_SESSION ['last_notifications'] - $totalParley) : 0;
		echo $objParleyManager->setLookedParley (
			ParleyHistories::getInstance ()
				->setUsersId ($current_user->id)
		);
	}
	exit();
