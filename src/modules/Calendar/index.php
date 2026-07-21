<?php
	require_once ('include/platzilla/Objects/NotificationInterface.php');
	require_once ('include/utils/CalendarViewUtils.class.php');
	require_once ('modules/Calendar/CalendarCommon.php');
	require_once ('modules/notifications/lib/NotificationUtils.class.php');
	require_once ('modules/operating_modes/lib/ManagementModeHelper.class.php');
	require_once('include/utils/DataViewUtils.php');
	require_once ('modules/Settings/lib/PanelViewHelper.class.php');
	require_once ('include/utils/NumberHelper.class.php');
	require_once ('include/utils/CommonUtils.php');

	global $adb, $app_strings, $current_user, $currentModule, $mod_strings, $theme;

	$param = array ();
	if ($_REQUEST ['activity_type'] != '') {
		$param ['activity_type'] = $_REQUEST ['activity_type'];
	}
	if ($_REQUEST ['userid'] != '') {
		$param ['user_id'] = $_REQUEST ['userid'];
	}
	
	try {
		if (!isset($_REQUEST ['calendar_main'])) {
			$defaultView = CalendarViewUtils::fetchDefaultView ($adb);
			if (!empty ($defaultView)) {
				$view          = CalendarViewUtils::getCalendarViewById ($adb, $defaultView['calendarviewid']);
				$data          = CalendarViewUtils::getCalendarData ($adb, $view, $current_user, $ruleId);
				$smarty = new vtigerCRM_Smarty();
				$smarty->assign ('CALENDAR_VIEWS', CalendarViewUtils::getCalendarViewByModules ($adb));
				$smarty->assign ('DATA', $data);
				$smarty->assign ('MOD', return_module_language ($current_language,  $defaultView['modulename']));
				$smarty->assign ('MODULE', $defaultView['modulename']);
				$smarty->assign ('RULE_ID', !empty ($ruleId) ? $ruleId : null);
				$smarty->assign ('VIEW', $view);
				$smarty->assign ('VIEW_ID', $defaultView['calendarviewid']);
				$smarty->display ('CalendarView.tpl');
				exit();
			}
			
		}
	} catch (Exception $e) {
	
	}
	
	$dataCalendar     = getFullCalendar ($order = '', $param);
	$activityTypes    = getActivityTypes ();
	$calendarType     = ($currentModule == 'Calendar') ? 'task' : '';
	$calenderViewData = ManagementModeHelper::fetchCalendarViewData ('CALENDAR_VIEW', $dataCalendar, $calendarType);
		$result = $adb->pquery ('SELECT * FROM vtiger_tab WHERE presence=0 AND isentitytype=1 ORDER BY tablabel', array ());
	if ($adb->num_rows ($result) == 0) {
		$relatedModules = null;
	} else {
		$relatedModules = array ();
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$relatedModules [] = $row;
		}
	}
	if ($result instanceof ADORecordSet) {
		$result->Close ();
		$result = null;
	}
	$isInstance       = !empty ($_SESSION ['platInstancia']);
	$availableModules = CalendarViewUtils::getAvailableModules ($adb);
	if (($isInstance) && (!empty ($availableModules))) {
		$moduleNames = array ();
		foreach ($availableModules as $availableModule) {
			$moduleNames [] = $availableModule ['name'];
		}
	} else {
		$moduleNames = null;
	}
	$notificationData = array (
		'module'   => $currentModule,
		'user'     => $current_user,
		'view'     => Notification::LIST_VIEW,
		'style'    => Notification::STYLE_NOTIFY,
		'recordId' => 0,
		'platform' => $_SESSION ['plat'],

	);

	$result = $adb->query ('SELECT * FROM vtiger_users ORDER BY id');
	if ($adb->num_rows ($result) > 0) {
		$availableUsers = array ();
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$availableUsers [ $row ['id'] ] = trim ("{$row ['first_name']} {$row ['last_name']}");
		}
	} else {
		$availableUsers = null;
	}
	if ($result instanceof ADORecordSet) {
		$result->Close ();
		$result = null;
	}

	$result = $adb->query ('SELECT * FROM vtiger_groups ORDER BY groupid');
	if ($adb->num_rows ($result) > 0) {
		$availableGroups = array ();
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$availableGroups [ $row ['groupid'] ] = $row ['groupname'];
		}
	} else {
		$availableGroups = null;
	}
	if ($result instanceof ADORecordSet) {
		$result->Close ();
		$result = null;
	}
	$objectDate = new DateTime();
	$today      = $objectDate->format ('Y-m-d');
	$objectDate = new DateTime();
	$objectDate->modify ('+1 day');
	$tomorrow   = $objectDate->format ('Y-m-d');

	$smarty = new vtigerCRM_Smarty();
	$smarty->assign ('ACTIVITYTYPE', $activityTypes);
	$smarty->assign ('ACTIVITYTYPESELECTED', $_REQUEST ['activity_type']);
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('AVAILABLE_ACTIVITY_TYPES', DataViewUtils::fetchActivityType ($adb, $moduleName, $mod_strings));
	$smarty->assign ('AVAILABLE_EVENT_STATUSES', DataViewUtils::getAvailableEventStatuses($adb, $mod_strings));
	$smarty->assign ('AVAILABLE_GROUPS', DataViewUtils::getAvailableGroups($adb));
	$smarty->assign ('AVAILABLE_IMPORTANCE', DataViewUtils::getAvailableImportanceOfTasks());
	$smarty->assign ('AVAILABLE_MODULES', PanelViewHelper::fetchAvailableModules ($adb, $current_user->id, array ('SHOW' => array('proyectos'))));
	$smarty->assign ('AVAILABLE_SYSTEM_USERS', UserManager::getInstance($adb, null)->fetchUsers());
	$smarty->assign ('AVAILABLE_TASK_PRIORITIES', DataViewUtils::getTaskPriorities($adb));
	$smarty->assign ('AVAILABLE_USERS', DataViewUtils::getAvailableUserAndAvatar($adb, $current_user));
	$smarty->assign ('AVAILABLE_ESTIMATED_TIME_UNITS', getAvailableEstimatedTimeUnits());
	$smarty->assign ('DEFAULT_ESTIMATED_TIME_UNIT', 'Hora');
	$smarty->assign ('BUTTONS', isset ($botones) ? $botones : null);
	$smarty->assign ('CALENDAR_TYPE', ($currentModule == 'Calendar' ? 'task' : ''));
	$smarty->assign ('CALENDAR_VIEWS', CalendarViewUtils::getCalendarViewByModules ($adb));
	$smarty->assign ('CATEGORIES',  DataViewUtils::getAvailableTaskCategories($adb, $current_user->id));
	$smarty->assign ('CATEGORY', isset ($category) ? $category : null);
	$smarty->assign ('CURRENT_USER_ID', $current_user->id);
	$smarty->assign ('CURRENT_USER_NAME', isset($current_user->first_name) && isset($current_user->last_name) ? $current_user->first_name . ' ' . $current_user->last_name : 'Usuario');
	$smarty->assign ('DATA', $calenderViewData);
	$smarty->assign ('FLMODULE', $moduleName);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODULE', $currentModule);
	$smarty->assign ('NOTIFICATIONS', NotificationUtils::fetchApplicableOnScreenNotifications ($adb, $notificationData));
	$smarty->assign ('RELATED_MODULES', $relatedModules);
	$smarty->assign ('SINGLE_MOD', 'SINGLE_' . $currentModule);
	$smarty->assign ('THEME', $theme);
	$numberHelper = NumberHelper::getInstance($adb, $current_user);
	$assignedNumberingFormat = $numberHelper->getNumberFormat();
	$smarty->assign ('NUMBERING_FORMAT', $assignedNumberingFormat);
	$smarty->assign ('TODAY', $today);
	$smarty->assign ('TOMORROW', $tomorrow);
	$smarty->assign ('USERIDSELECTED', $_REQUEST['userid']);
	$smarty->assign ('USERSELECTED', $_REQUEST['user']);
	$dataSelectUsers = getInfoSelectAsignedUserId();
	$smarty->assign ('USUARIOS', $dataSelectUsers);
	//$smarty->assign ('CALENDAR_VIEWS', CalendarViewUtils::getCalendarViews ($adb, null, null, $moduleNames));
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	//	'CURRENT_USER_ID' => $current_user->id,
	//	'CURRENT_USER_NAME' => $current_user->first_name . ' ' . $current_user->last_name,
	//	'ACTIVITYTYPE' => $activityTypes,
	//	'AVAILABLE_ACTIVITY_TYPES' => $smarty->getTemplateVars('AVAILABLE_ACTIVITY_TYPES'),
	//	'CALENDAR_TYPE' => $calendarType,
	//	'CALENDAR_VIEWS' => $smarty->getTemplateVars('CALENDAR_VIEWS'),
	//	'DATA' => $calenderViewData,
	//], true));
	$smarty->display ('Calendar.tpl');

