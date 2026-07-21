<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
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
		if (empty ($taskId)) {
			throw new Exception ('No has suministrado el ID de la tarea a duplicar');
		}

		$task = BackgroundTasksUtils::duplicateTask ($adb, $taskId);
		$availableFields     = BackgroundTasksUtils::getAvailableFieldsData ($adb, $task->getModuleName ());
		$availableGridFields = GridFieldUtils::getAvailableGridFields ($adb, $task->getModuleName ());
		$actions             = $task->getActions ();
		if (!empty ($actions)) {
			$availablePicklistValues = array ();
			foreach ($actions as $action) {
				$parameters = $action->getParameters ();
				if (!empty ($parameters)) {
					foreach ($parameters as $parameter) {
						if ($parameter->getName () == 'modulename') {
							$availablePicklistValues [ $action->getSequence () ] = BackgroundTasksUtils::getAvailablePicklistValues ($adb, $parameter->getValueFormula ());
						}
					}
				} else {
					$availablePicklistValues [ $action->getSequence () ] = null;
				}
			}
		} else {
			$availablePicklistValues = null;
		}

		$smarty->assign ('AVAILABLE_ACTIONS', BackgroundTasksUtils::getAvailableActions ($adb));
		$smarty->assign ('AVAILABLE_CATEGORIES', BackgroundTasksUtils::getAvailableCategories ($adb));
		$smarty->assign ('AVAILABLE_EVENT_INSTANTS', BackgroundTasksUtils::getAvailableEventInstants ());
		$smarty->assign ('AVAILABLE_EVENTS', BackgroundTasksUtils::getAvailableEvents ($adb));
		$smarty->assign ('AVAILABLE_FIELDS', BackgroundTasksUtils::getAvailableFieldsData ($adb, $task->getModuleName ()));
		$smarty->assign ('AVAILABLE_GRID_FIELDS', $availableGridFields);
		$smarty->assign ('AVAILABLE_MODULES', BackgroundTasksUtils::getAvailableEntityModules ($adb));
		$smarty->assign ('AVAILABLE_PICKLIST_VALUES', $availablePicklistValues);
		$smarty->assign ('AVAILABLE_STATUSES', BackgroundTasksUtils::getAvailableStatuses ());
		$smarty->assign ('AVAILABLE_TRIGGERS', BackgroundTasksUtils::getAvailableTriggers ());
		$smarty->assign ('AVAILABLE_USERS', BackgroundTasksUtils::getAvailableUsers ($adb, $_SESSION ['plat']));
		$smarty->assign ('IS_INSTANCE', !empty ($_SESSION ['platInstancia']));
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('SYSTEM_VARIABLES', SystemVariables::getAvailableVariables ());
		$smarty->assign ('SYSTEM_VARIABLE_TYPES', SystemVariables::getAvailableVariableTypes ());
		$smarty->assignByRef ('TASK', $task);
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
