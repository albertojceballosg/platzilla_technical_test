<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksUtils.class.php');

	global $adb, $app_strings, $current_user, $theme, $site_URL;
	setBugSnag ($site_URL);

	$smarty = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	$platform = $_SESSION ['plat'];
	$logFileHandle = null;
	try {
		$taskId = PlatzillaUtils::purify ($_GET, 'record');
		if (empty ($taskId)) {
			throw new Exception ('No has suministrado el ID de la tarea');
		}

		$task = BackgroundTasksUtils::getTaskById ($adb, $taskId, true);
		if (empty ($task)) {
			throw new Exception ('No se encuentra registrada la tarea con el ID suministrado');
		}

		$taskName = $task->getName ();
		$logFilePath = __DIR__ . "/../../{$platform}/logs/backgroundtasks/{$taskName}.log";
		if (file_exists ($logFilePath)) {
			$logFileHandle = fopen ($logFilePath, 'r');
		}

		$smarty->assign ('LOG_FILE_HANDLE', $logFileHandle);
		$smarty->assign ('TASK_ID', $taskId);
		$smarty->assign ('TASK_NAME', $taskName);
		$smarty->display ('modules/backgroundtasks/LogView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php?module=backgroundtasks&action=ListView&parenttab=Settings');
		$smarty->display ('Message.tpl');
	}
	if ($logFileHandle) {
		fclose ($logFileHandle);
	}
