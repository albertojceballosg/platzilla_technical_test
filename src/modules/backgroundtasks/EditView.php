<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Configuration/BackgroundTaskParameterConfigurationInterface.php');
	require_once ('include/platzilla/Managers/NotificationManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/GridFieldUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/SystemVariables.class.php');
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

	try {
		$taskId = PlatzillaUtils::purify ($_GET, 'record');
		if (!empty ($taskId)) {
			$task                = BackgroundTasksUtils::getTaskById ($adb, $taskId);
			$availableFields     = BackgroundTasksUtils::getAvailableFieldsData ($adb, $task->getModuleName ());
			$availableGridFields = GridFieldUtils::getAvailableGridFields ($adb, $task->getModuleName ());
			$actions             = $task->getActions ();
			if (!empty ($actions)) {
				$availablePicklistValues = array ();
				$availableNotification   = array();
				foreach ($actions as $action) {
					$parameters = $action->getParameters ();
					if (!empty ($parameters)) {
						$notifyModuleSelected = null;
						$notifyValueSelected  = null;
						foreach ($parameters as $parameter) {
							if ($parameter->getActionType () == 'SEND NOTIFICATION') {
								if ($parameter->getName () == 'modulename') {
									$notifyModuleSelected = $parameter->getValue ();
								} else if ($parameter->getName () == 'notificationid') {
									$notifyValueSelected = $parameter->getValueFormula ();
								}
							} else if ($parameter->getName () == 'modulename') {
								$availablePicklistValues [ $action->getSequence () ] = BackgroundTasksUtils::getAvailablePicklistValues ($adb, $parameter->getValueFormula ());
							}
							if (!empty ($notifyModuleSelected) && !empty ($notifyValueSelected)) {
								$smarty->assign ('MOD', $mod_strings);
								$smarty->assign ('NOTIFICATION_SELECTED', $notifyValueSelected);
								$smarty->assign ('NOTIFICATIONS', NotificationManager::getInstance ($adb)->fetchNotifications ($notifyModuleSelected, true));
								$availableNotification [ $action->getSequence () ] = $smarty->fetch ('modules/backgroundtasks/actions/SendNotificationAction/parameters/notifications.tpl');
							}
						}
					} else {
						$availablePicklistValues [ $action->getSequence () ] = null;
					}
				}
			} else {
				$availablePicklistValues = null;
			}
			$selectedModuleName = $task->getModuleName ();
			$modulesInTasks     = BackgroundTasksUtils::getRelatedModulesInTasks ($adb, $taskId);
		} else {
			$task                    = null;
			$availableFields         = null;
			$availableGridFields     = null;
			$availablePicklistValues = null;
			$availableNotification   = null;
			$selectedModuleName      = null;
			$modulesInTasks          = null;
		}

		$smarty->assign ('AVAILABLE_ACTIONS', BackgroundTasksUtils::getAvailableActions ($adb));
		$smarty->assign ('AVAILABLE_CATEGORIES', BackgroundTasksUtils::getAvailableCategories ($adb));
		$smarty->assign ('AVAILABLE_EVENT_INSTANTS', BackgroundTasksUtils::getAvailableEventInstants ());
		$smarty->assign ('AVAILABLE_EVENTS', BackgroundTasksUtils::getAvailableEvents ($adb));
		$smarty->assign ('AVAILABLE_FIELDS', $availableFields);
		$smarty->assign ('AVAILABLE_GRID_FIELDS', $availableGridFields);
		$smarty->assign ('AVAILABLE_MODULES', BackgroundTasksUtils::getAvailableEntityModules ($adb));
		$smarty->assign ('AVAILABLE_PICKLIST_VALUES', $availablePicklistValues);
		$smarty->assign ('AVAILABLE_STATUSES', BackgroundTasksUtils::getAvailableStatuses ());
		$smarty->assign ('AVAILABLE_TRIGGERS', BackgroundTasksUtils::getAvailableTriggers ());
		$smarty->assign ('AVAILABLE_USERS', BackgroundTasksUtils::getAvailableUsers ($adb, $_SESSION ['plat']));
		$smarty->assign ('MODULES_IN_TASK',$modulesInTasks);
		$smarty->assign ('IS_INSTANCE', !empty ($_SESSION ['platInstancia']));
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('NOTIFICATIONS', $availableNotification);
		$smarty->assign ('SELECTED_MODULE_NAME', $selectedModuleName);
		$smarty->assign ('SYSTEM_VARIABLES', SystemVariables::getAvailableVariables ());
		$smarty->assign ('SYSTEM_VARIABLE_TYPES', SystemVariables::getAvailableVariableTypes ());
		if (!empty ($taskId)) {
			$smarty->assign ('RECORD', $taskId);
			$smarty->assignByRef ('TASK', $task);
		}
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->display ('modules/backgroundtasks/EditView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php?module=backgroundtasks&action=ListView&parenttab=Settings');
		$smarty->display ('Message.tpl');
	}
