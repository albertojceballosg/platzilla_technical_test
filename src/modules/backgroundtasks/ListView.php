<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksUtils.class.php');

	global $adb, $app_strings, $current_user, $mod_strings, $theme, $site_URL;
	setBugSnag ($site_URL);

	$smarty = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	if (!empty ($_SESSION ['platInstancia'])) {
		$result = $adb->pquery ('SELECT * FROM vtiger_tab WHERE isentitytype=1 AND presence IN (0, 2) OR name IN (?)', array ('notifications'));
		if ($adb->num_rows ($result) > 0) {
			$moduleNames = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$moduleNames [] = $row ['name'];
			}
		}
		DatabaseUtils::closeResult ($result);
		$result = null;
		$tasks = BackgroundTasksUtils::getTasks ($adb, $moduleNames, BackgroundTaskInterface::SCOPE_USER, true);
	} else {
		$tasks = BackgroundTasksUtils::getTasks ($adb, null, null, true);
	}

	$smarty->assign ('AVAILABLE_ACTIONS', BackgroundTasksUtils::getAvailableActions ($adb));
	$smarty->assign ('AVAILABLE_CATEGORIES', BackgroundTasksUtils::getAvailableCategories ($adb));
	$smarty->assign ('AVAILABLE_EVENT_INSTANTS', BackgroundTasksUtils::getAvailableEventInstants ());
	$smarty->assign ('AVAILABLE_EVENTS', BackgroundTasksUtils::getAvailableEvents ($adb));
	$smarty->assign ('AVAILABLE_FIELDS', $availableFields);
	$smarty->assign ('AVAILABLE_GRID_FIELDS', $availableGridFields);
	$smarty->assign ('AVAILABLE_MODULES', BackgroundTasksUtils::getAvailableEntityModules ($adb));
	$smarty->assign ('AVAILABLE_STATUSES', BackgroundTasksUtils::getAvailableStatuses ());
	$smarty->assign ('AVAILABLE_TRIGGERS', BackgroundTasksUtils::getAvailableTriggers ());
	$smarty->assign ('AVAILABLE_USERS', BackgroundTasksUtils::getAvailableUsers ($adb, $_SESSION ['plat']));
	$smarty->assign ('IS_INSTANCE', !empty ($_SESSION ['platInstancia']) ? true : false);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('TASKS', $tasks);
	$smarty->assign ('SYSTEM_VARIABLES', SystemVariables::getAvailableVariables ());
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('modules/backgroundtasks/ListView.tpl');
