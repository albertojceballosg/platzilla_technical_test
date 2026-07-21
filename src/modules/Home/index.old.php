<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/PlatformSubscriptionManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/DataViewUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Home/lib/HomeUtils.class.php');
	require_once ('modules/notification_center/lib/NotificationHelper.class.php');
	require_once ('modules/notifications/lib/NotificationUtils.class.php');
	require_once ('modules/notifications/lib/NotificationPeriodUtils.class.php');
	require_once ('modules/panelusuarios/lib/UsersHelper.class.php');
	require_once ('modules/store/lib/StoreUtils.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');

	global $adb, $app_strings, $current_language, $current_user, $currentModule, $webMailClient;

	try {
		if (!empty ($_SESSION ['platInstancia'])) {
			if (!StoreUtils::isInstanceVerified ($_SESSION ['platInstancia'])) {
				throw new Exception ('Debes verificar tu cuenta', 400);
			}

			$masterAdb    = AdbManager::getInstance ()->getMasterAdb ();
			$psm          = PlatformSubscriptionManager::getInstance ($masterAdb);
			$subscription = $psm->fetchSubscription ($_SESSION ['platInstancia']);
			if ((empty ($subscription)) || ($subscription->getStatus () == PlatformSubscription::STATUS_INACTIVE)) {
				throw new Exception ('Tu suscripción se encuentra inactiva', 403);
			}

			$applications     = PlatformUtils::getApplicationsByUserRole ($adb, $current_user->column_fields ['roleid'], 'Calendar');
			$canCreateRecords = true;
		} else {
			$applications     = PlatformUtils::getApplicationsByModuleName ($adb, 'Calendar');
			$canCreateRecords = true;
		}

		// Tab tareas
		$isAjaxRequest   = isset ($_GET ['Ajax']);
		$profileIds      = PlatzillaUtils::purify ($_GET, 'profileids');
		$kanbanViewId    = PlatzillaUtils::purify ($_GET, 'kviewid');
		$kanbanFieldName = PlatzillaUtils::purify ($_GET, 'kfieldname');
		$page            = PlatzillaUtils::purify ($_GET, 'page');
		$sortBy          = PlatzillaUtils::purify ($_GET, 'sortby');
		$sortOrder       = PlatzillaUtils::purify ($_GET, 'sortorder');
		$viewId          = PlatzillaUtils::purify ($_GET, 'viewid');
		$profileIds      = !empty ($profileIds) ? explode (',', $profileIds) : null;
		$viewType        = !empty ($kanbanViewId) ? 'KANBAN' : 'REGULAR';

		if (!empty ($viewId)) {
			$view = DataViewUtils::fetchViewById ($adb, 'Calendar', $viewId);
		} else {
			$view = DataViewUtils::fetchDefaultView ($adb, 'Calendar');
		}
		if (empty ($view)) {
			throw new Exception ('La vista solicitada no se encuentra registrada');
		}

		$viewPermissions = DataViewUtils::fetchViewPermissions ($adb, $view, $current_user);
		if ((!is_array ($viewPermissions)) || (!in_array (DataViewUtils::PERMISSION_CAN_USE, $viewPermissions))) {
			throw new Exception ('Acceso denegado');
		}

		$orderBy  = (!empty ($sortBy)) && (!empty ($sortOrder)) ? array ($sortBy => $sortOrder) : null;
		$viewData = DataViewUtils::fetchViewData ($adb, $view, $current_user, $page, $orderBy);

		$arguments     = array (
			'module'   => $currentModule,
			'user'     => $current_user,
			'view'     => Notification::LIST_VIEW,
			'style'    => Notification::STYLE_NOTIFY,
			'recordId' => 0,
			'platform' => $_SESSION ['plat'],
		);
		$notifications = NotificationUtils::fetchApplicableOnScreenNotifications ($adb, $arguments);

		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('ACTIVE_APPLICATIONS', $applications);
		$smarty->assign ('ALLOW_MASS_ACTIONS', false);
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('APPLICATION_VIEWS_ENABLED', PlatformUtils::areApplicationViewsEnabled ($adb));
		$smarty->assign ('AVAILABLE_VIEWS', DataViewUtils::fetchAvailableViews ($adb, 'Calendar', $current_user));
		$smarty->assign ('CAN_CREATE_RECORDS', $canCreateRecords);
		$smarty->assign ('IS_ADMIN', is_admin ($current_user));
		$smarty->assign ('IS_RELATED_TO_CALENDAR', DataViewUtils::isRelatedToCalendar ($adb, 'Calendar'));
		$smarty->assign ('MODULE', 'Calendar');
		$smarty->assign ('NOTIFICATIONS', $notifications);
		$smarty->assign ('PROFILE_IDS', $profileIds);
		$smarty->assign ('ROOT_FOLDER_PATH', PlatzillaUtils::getPlatzillaRootFolderPath ());
		$smarty->assign ('VIEW', $view);
		$smarty->assign ('VIEW_DATA', $viewData);
		$smarty->assign ('VIEW_PERMISSIONS', $viewPermissions);
		$smarty->assign ('VIEW_TYPE', $viewType);
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		if (!$isAjaxRequest) {
			$tasksTabContent = $smarty->fetch ('modules/Calendar/ListView.tpl');
		} else {
			$tasksTabContent = $smarty->fetch ('modules/Calendar/ListViewEntries.tpl');
		}

		// Tab actividades
		$smarty->clear_all_assign ();
		$smarty->assign ('ACTIVITIES', getFullCalendar ());
		$smarty->assign ('COLORES', array ('red-bg', 'yellow-bg', 'green-bg', 'emerald-bg', 'red-bg', 'yellow-bg', 'green-bg', 'emerald-bg', 'red-bg', 'yellow-bg'));
		$smarty->assign ('COMPANY', HomeUtils::getOrganizationDetails ($adb, $_SESSION ['plat']));
		$smarty->assign ('THEME', 'centaurus');
		$smarty->assign ('USERS', UsersHelper::getUsers ($adb));
		$activitiesTabContent = $smarty->fetch ('Home/dashboard.tpl');

		// Tab Mensajes
		$today       = date_create ()->format ('Y-m-d');
		$yesterday   = date_create ()->modify ('-1 day')->format ('Y-m-d');
		$lastWeek    = date_create ()->modify ('-7 day')->format ('Y-m-d');
		$lastMonth   = date_create ()->modify ('-30 day')->format ('Y-m-d');
		$lastQuarter = date_create ()->modify ('-90 day')->format ('Y-m-d');

		$mailAccounts      = WebmailUtils::fetchMailAccounts ($adb, $current_user->id);
		$mailAccount       = (!empty ($mailAccounts)) && (is_array ($mailAccounts)) ? $mailAccounts [0] : null;
		$arguments         = array ('emailFrom' => $lastMonth, 'dateTo' => $today, 'emailModule' => '');
		$emailsArchived    = NotificationHelper::getEmailsRelatedEntities ($adb, $current_user, $arguments);
		$emailsNotArchived = NotificationHelper::fetchNonRelatedEmails ($adb, $current_user, $arguments);
		$searchParameter   = NotificationHelper::getInitialParameters ();
		$parleyArray       = NotificationHelper::searchParleyByWhere ($adb, $current_user, array ('minTime' => (time () - (7 * 24 * 60 * 60))));
		$parleyModuleArray = NotificationHelper::getParleyModules ($adb, $current_user->id);
		$searchPeriod      = NotificationPeriodUtils::getStandarFiltersStartAndEndDate ('last30days');
		$alertsData        = array (
			'period' => $searchPeriod,
			'user'   => $current_user,
		);
		$systemAlerts      = NotificationUtils::searchAvailableAlerts ($adb, $alertsData);
		foreach ($systemAlerts as $alert) {
			$alert->timeSince = NotificationHelper::timeSince (intval ($alert->getCreatedTime ()));
			$alert->setContents (strip_tags ($alert->getContents ()));
		}

		ParleyManager::getInstance ($adb)->setLookedParley (ParleyHistories::getInstance ()->setUsersId ($current_user->id));

		$smarty->clear_all_assign ();
		$smarty->assign ('AVAILABLE_PERIODS', NotificationPeriodUtils::getAvailableShortPeriods ());
		$smarty->assign ('ALERT_PERIOD', $searchPeriod);
		$smarty->assign ('CHATS', $parleyArray);
		$smarty->assign ('CURRENT_USER_NAME', $current_user->column_fields ['first_name'] . ' ' . $current_user->column_fields ['last_name']);
		$smarty->assign ('dateFrom', $searchParameter ['dateFrom']);
		$smarty->assign ('dateTo', $searchParameter ['dateTo']);
		$smarty->assign ('EMAILS', $emailsNotArchived);
		$smarty->assign ('EMAILS_ARCHIVED', $emailsArchived);
		$smarty->assign ('emailsFrom', $lastMonth);
		$smarty->assign ('emailThMonth', $lastQuarter);
		$smarty->assign ('emailToday', $yesterday);
		$smarty->assign ('emailWeek', $lastWeek);
		$smarty->assign ('INIT_PERIOD', 'last30days');
		$smarty->assign ('lastWeek', $searchParameter ['lastWeek']);
		$smarty->assign ('lastMonth', $searchParameter ['lastMonth']);
		$smarty->assign ('lastThMonth', $searchParameter ['lastThMonth']);
		$smarty->assign ('MAIL_ACCOUNT', $mailAccount);
		$smarty->assign ('MOD', return_module_language ($current_language, 'notification_center'));
		$smarty->assign ('PARLEY_MODULES', $parleyModuleArray);
		$smarty->assign ('SYSALERTS', $systemAlerts);
		$smarty->assign ('today', $searchParameter ['todayTime']);
		$messagesTabContent = $smarty->fetch ('modules/notification_center/notificationCenter.tpl');

		// Render de todos los tabs
		$smarty->clear_all_assign ();
		$smarty->assign ('ACTIVITIES_TAB_CONTENT', $activitiesTabContent);
		$smarty->assign ('MESSAGES_TAB_CONTENT', $messagesTabContent);
		$smarty->assign ('TASKS_TAB_CONTENT', $tasksTabContent);
		$smarty->display ('Home/index.tpl');
	} catch (Exception $e) {
		$code   = $e->getCode ();
		$smarty = new vtigerCRM_Smarty ();
		if ($code === 400) {
			$smarty->assign ('MENSAJE', 'Debes verificar tu cuenta');
			$smarty->display ('instanciaUnverified.tpl');
		} else if ($code === 403) {
			$smarty->assign ('LABEL', 'Tu suscripción');
			$smarty->assign ('MESSAGE', $e->getMessage ());
			$smarty->assign ('TYPE', 'ERROR');
			$smarty->assign ('URL', 'index.php?module=Home&action=index');
			$smarty->display ('Message.tpl');
		} else {
			$smarty->assign ('LABEL', 'Se ha presentado un error fatal');
			$smarty->assign ('MESSAGE', $e->getMessage ());
			$smarty->assign ('TYPE', 'ERROR');
			$smarty->assign ('URL', 'index.php?module=Home&action=index');
			$smarty->display ('Message.tpl');
		}
	}
