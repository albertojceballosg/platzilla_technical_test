<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksUtils.class.php');

	global $adb, $current_user, $site_URL;
	setBugSnag ($site_URL);

	$platform      = $_SESSION ['plat'];
	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('No estás autorizado a realizar la operación solicitada');
		}

		$taskId = PlatzillaUtils::purify ($_POST, 'record');
		if (empty ($taskId)) {
			throw new Exception ('No has suministrado el ID de la tarea');
		}

		$task = BackgroundTasksUtils::getTaskById ($adb, $taskId, true);
		if (empty ($task)) {
			throw new Exception ('No se encuentra registrada la tarea con el ID suministrado');
		}

		$taskName    = $task->getName ();
		$logFilePath = __DIR__ . "/../../{$platform}/logs/backgroundtasks/{$taskName}.log";
		if (file_exists ($logFilePath)) {
			unlink ($logFilePath);
		}

		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'Se ha eliminado el registro de eventos',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php?module=backgroundtasks&action=index');
	exit ();
