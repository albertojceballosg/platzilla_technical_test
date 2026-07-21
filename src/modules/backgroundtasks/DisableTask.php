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
		if (empty ($taskId)) {
			throw new Exception ('No has suministrado el ID de la tarea a deshabilitar');
		}

		BackgroundTasksUtils::disableTask ($adb, $taskId);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'La tarea ha sido deshabilitada',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php?module=backgroundtasks&action=ListView&parenttab=Settings');
