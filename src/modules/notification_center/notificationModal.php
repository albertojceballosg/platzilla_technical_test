<?php
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('modules/notification_center/lib/NotificationHelper.class.php');
	require_once ('modules/notifications/lib/NotificationUtils.class.php');
	require_once ('modules/notifications/lib/NotificationPeriodUtils.class.php');
	require_once ('include/platzilla/Objects/NotificationInterface.php');
	require_once ('Smarty_setup.php');

	global $adb, $currentModule, $current_user, $default_timezone, $current_language;

	if (isset($default_timezone) && function_exists ('date_default_timezone_set')) {
		date_default_timezone_set ($default_timezone);
	} else {
		date_default_timezone_set ('UTC');
	}

	$today       = date ('Y-m-d h:i:s');
	$todayTime   = strtotime ($today);
	$lastWeek    = (time () - (7 * 24 * 60 * 60));
	$lastMonth   = (time () - (30 * 24 * 60 * 60));
	$lastThMonth = (time () - (90 * 24 * 60 * 60));

	$objectDate = new DateTime();
	$objectDate->modify ('-7 day');
	$dateFrom   = $objectDate->format ('Y-m-d');
	$objectDate = new DateTime();
	$dateTo     = $objectDate->format ('Y-m-d');

	$seach['minTime'] = $lastWeek;

	$searchParameter   = NotificationHelper::getInitialParameters ();
	$parleyArray       = NotificationHelper::searchParleyByWhere ($adb, $current_user, $seach);
	$parleyModuleArray = NotificationHelper::getParleyModules ($adb, $current_user->id);

	$emailsDate = new DateTime();
	$emailsDate->modify ('-1 day');
	$emailToday = $emailsDate->format ('Y-m-d');

	$emailsDate = new DateTime();
	$emailsDate->modify ('-7 day');
	$emailWeek = $emailsDate->format ('Y-m-d');

	$emailsDate = new DateTime();
	$emailsDate->modify ('-30 day');
	$emailFrom = $emailsDate->format ('Y-m-d');

	$emailsDate = new DateTime();
	$emailsDate->modify ('-90 day');
	$emailThMonth = $emailsDate->format ('Y-m-d');

	$seachArchivedEmails = array ('emailFrom' => $emailFrom, 'dateTo' => $dateTo, 'emailModule' => '');

	$emailsArchived    = NotificationHelper::getEmailsRelatedEntities ($adb, $current_user, $seachArchivedEmails);
	$emailsNotArchived = NotificationHelper::fetchNonRelatedEmails ($adb, $current_user, $seachArchivedEmails);

	$searchPeriod = NotificationPeriodUtils::getStandarFiltersStartAndEndDate ('last30days');
	$alertsData   = array (
		'period' => $searchPeriod,
		'user'   => $current_user,
	);

	$systemAlerts = NotificationUtils::searchAvailableAlerts ($adb, $alertsData, $current_user);

	foreach ($systemAlerts as $alert) {
		$alert->timeSince = NotificationHelper::timeSince (intval ($alert->getCreatedTime ()));
		$alert->setContents (strip_tags ($alert->getContents ()));
	}

	ParleyManager::getInstance ($adb)->setLookedParley (
		ParleyHistories::getInstance ()
			->setUsersId($current_user->id)
	);

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('AVAILABLE_PERIODS', NotificationPeriodUtils::getAvailableShortPeriods ());
	$smarty->assign ('ALERT_PERIOD', $searchPeriod);
	$smarty->assign ('INIT_PERIOD', 'last30days');
	$smarty->assign ('today', $searchParameter['todayTime']);
	$smarty->assign ('lastWeek', $searchParameter['lastWeek']);
	$smarty->assign ('lastMonth', $searchParameter['lastMonth']);
	$smarty->assign ('lastThMonth', $searchParameter['lastThMonth']);
	$smarty->assign ('dateFrom', $searchParameter['dateFrom']);
	$smarty->assign ('dateTo', $searchParameter['dateTo']);
	$smarty->assign ('emailsFrom', $emailFrom);
	$smarty->assign ('emailToday', $emailToday);
	$smarty->assign ('emailWeek', $emailWeek);
	$smarty->assign ('emailThMonth', $emailThMonth);
	$smarty->assign ('MOD', return_module_language ($current_language, 'notification_center'));
	$smarty->assign ('PARLEY_MODULES', $parleyModuleArray);
	$smarty->assign ('CURRENT_USER_NAME', $current_user->column_fields['first_name'] . ' ' . $current_user->column_fields['last_name']);
	$smarty->assign ('CHATS', $parleyArray);
	$smarty->assign ('SYSALERTS', $systemAlerts);
	$smarty->assign ('EMAILS_ARCHIVED', $emailsArchived);
	$smarty->assign ('EMAILS', $emailsNotArchived);
	// Agregar datos de usuarios para el typeahead del chat con límite para evitar timeout
	try {
		$usersForChat = NotificationHelper::fetchUserToChat ($adb, $_SESSION ['plat']);
		// Limitar a los primeros 50 usuarios para evitar timeout
		if (count($usersForChat) > 50) {
			$usersForChat = array_slice($usersForChat, 0, 50);
		}
		$smarty->assign ('SEARCH_USERS_CHATS', json_encode ($usersForChat));
	} catch (Exception $e) {
		// En caso de error, asignar array vacío
		$smarty->assign ('SEARCH_USERS_CHATS', '[]');
	}
	$smarty->display ('modules/notification_center/notificationCenter.tpl');
	exit();
