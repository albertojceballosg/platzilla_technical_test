<?php
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksRunner.class.php');
	require_once ('modules/store/lib/StoreUtils.class.php');

	global $current_user;

	$totalUsers = PlatzillaUtils::purify ($_POST, 'totalusers');

	$statusCode    = null;
	$statusMessage = null;
	try {
		$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		$result    = $masterAdb->pquery ('SELECT * FROM vtiger_instances WHERE code=?', array ($_SESSION ['platInstancia']));
		if ((!$result) || ($masterAdb->num_rows ($result) == 0)) {
			$statusCode    = 404;
			$statusMessage = 'Not found';
			throw new Exception ('La instancia no se encuentra registrada');
		}
		$instanceData = $masterAdb->fetchByAssoc ($result, -1, false);

		if (empty ($totalUsers)) {
			$statusCode    = 400;
			$statusMessage = 'Bad request';
			throw new Exception ('No has suministrado la cantidad de usuarios');
		} else if ((!is_numeric ($totalUsers)) || ($totalUsers < 0)) {
			$statusCode    = 400;
			$statusMessage = 'Bad request';
			throw new Exception ('La cantidad de usuarios suministrada no es un número válido');
		} else if ($totalUsers < $instanceData ['activeusers']) {
			$statusCode    = 400;
			$statusMessage = 'Bad request';
			throw new Exception ('La cantidad de usuarios suministrada no puede ser menor a la cantidad de usuarios activos. Elimina usuarios de la instancia y vuelve a intentarlo');
		}

		StoreUtils::changeInstanceUsers ($_SESSION ['platInstancia'], $totalUsers);
		$response = array (
			'message'   => 'Los usuarios han sido activados en tu cuenta',
			'returnurl' => 'index.php?module=store&action=index',
		);
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ($response);
	} catch (Exception $e) {
		$statusCode    = !empty ($statusCode) ? $statusCode : 500;
		$statusMessage = !empty ($statusMessage) ? $statusMessage : 'Internal server error';
		header ("HTTP/1.1 {$statusCode} {$statusMessage}");
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();
