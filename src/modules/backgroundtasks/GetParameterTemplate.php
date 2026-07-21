<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/NotificationManager.php');
	require_once ('include/utils/GridFieldUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/SystemVariables.class.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksUtils.class.php');

	global $adb, $mod_strings, $site_URL;
	setBugSnag ($site_URL);

	$actions    = PlatzillaUtils::purify ($_GET, 'actions');
	$moduleName = PlatzillaUtils::purify ($_GET, 'modulename');
	$scope      = PlatzillaUtils::purify ($_GET, 'scope');
	if (empty ($actions)) {
		echo '';
		exit ();
	}

	$action                  = reset ($actions);
	$actionId                = key ($actions);
	$actionType              = $action ['actiontype'];
	$availableAction         = BackgroundTasksUtils::getAvailableAction ($adb, $action ['actiontype'], $action ['parameters']);
	$availablePicklistValues = array ($actionId => BackgroundTasksUtils::getAvailablePicklistValues ($adb, $action ['parameters']['modulename']));

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('ACTION_HANDLER_CLASS', $availableAction->getHandlerClass ());
	$smarty->assign ('ACTION_SEQUENCE', $actionId);
	$smarty->assign ('AVAILABLE_FIELDS', BackgroundTasksUtils::getAvailableFieldsData ($adb, $moduleName));
	$smarty->assign ('AVAILABLE_GRID_FIELDS', GridFieldUtils::getAvailableGridFields ($adb, $moduleName));
	$smarty->assign ('AVAILABLE_PICKLIST_VALUES', $availablePicklistValues);
	$smarty->assign ('AVAILABLE_USERS', BackgroundTasksUtils::getAvailableUsers ($adb, $_SESSION ['plat']));
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('PARAMETERS', $availableAction->getParameters ());
	$smarty->assign ('SELECTED_MODULE_NAME', $moduleName);
	$smarty->assign ('SYSTEM_VARIABLES', SystemVariables::getAvailableVariables ());
	$smarty->assign ('SYSTEM_VARIABLE_TYPES', SystemVariables::getAvailableVariableTypes ());
	$smarty->assign ('TASK_SCOPE', $scope);
	if (file_exists (PlatzillaUtils::getPlatzillaRootFolderPath () . "/Smarty/templates/centaurus/modules/backgroundtasks/actions/{$availableAction->getHandlerClass ()}/Parameters.tpl")) {
		$smarty->display ("modules/backgroundtasks/actions/{$availableAction->getHandlerClass ()}/Parameters.tpl");
	} else if (($action ['actiontype'] == 'SEND NOTIFICATION') && (isset($action ['parameters']['modulename']))) {
		$smarty->assign ('NOTIFICATION_SELECTED', null);
		$smarty->assign ('NOTIFICATIONS', NotificationManager::getInstance ($adb)->fetchNotifications ($action ['parameters']['modulename'], true));
		$smarty->display ('modules/backgroundtasks/actions/SendNotificationAction/parameters/notifications.tpl');
	} else {
		$smarty->display ('modules/backgroundtasks/Parameters.tpl');
	}
	exit ();
