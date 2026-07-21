<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksUtils.class.php');

	global $adb, $current_user, $site_URL;
	setBugSnag ($site_URL);

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado');
		}

		$taskId = PlatzillaUtils::purify ($_POST, 'record');
		$taskSt = PlatzillaUtils::purify ($_POST, 'statustask');
		if (empty ($taskId)) {
			throw new Exception ('Tarea desconocida!');
		}

		if ($taskSt == BackgroundTask::STATUS_ENABLED) {
			BackgroundTasksUtils::disableTask ($adb, $taskId);
			throw new Exception (BackgroundTask::STATUS_DISABLED);
		} else if ($taskSt == BackgroundTask::STATUS_DISABLED) {
			BackgroundTasksUtils::enableTask ($adb, $taskId);
			throw new Exception (BackgroundTask::STATUS_ENABLED);
		} else {
			throw new Exception ('Tarea desconocida!');
		}
	} catch (Exception $e) {
		echo json_encode (array ('message' => $e->getMessage ()));
	}
	exit();
