<?php
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksRunner.class.php');

	global $current_user, $platPrincipal;

	$applicationCode = PlatzillaUtils::purify ($_POST, 'applicationcode');

	try {
		if ((empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
			throw new Exception ('Acceso denegado', 401);
		} else if (empty ($applicationCode)) {
			throw new Exception ('No has suministrado el código de la aplicación', 400);
		}

		$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		$instance  = PlatformManager::getInstance ($masterAdb)->fetchInstanceAsCrmEntity ($_SESSION ['platInstancia']);
		BackgroundTasksRunner::getInstance ($masterAdb, $platPrincipal)->runEventTriggeredTasks (
			'STORE OPERATION',
			BackgroundTaskInterface::EVENT_INSTANT_BEFORE,
			$instance
		);

		try {
			PlatformManager::getInstance ($masterAdb)->installInstanceApplication ($_SESSION ['platInstancia'], $applicationCode);
		} catch (PlatformSubscriptionException $pse) {
			if ($pse->getMessage () != PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_APPLICATION_INSTALLED) {
				throw $pse;
			}
		}
		PlatformManager::getInstance ($masterAdb)->subscribeInstanceApplication ($_SESSION ['platInstancia'], $applicationCode);

		BackgroundTasksRunner::getInstance ($masterAdb, $platPrincipal)->runEventTriggeredTasks (
			'STORE OPERATION',
			BackgroundTaskInterface::EVENT_INSTANT_AFTER,
			$instance
		);

		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ('La aplicación ha sido agregada a tu suscripción');
	} catch (Exception $e) {
		$statusCode    = !empty ($e->getCode ()) ? $e->getCode () : 500;
		switch ($statusCode) {
			case 400:
				$statusMessage = 'Bad request';
				break;
			case 401:
				$statusMessage = 'Access denied';
				break;
			default:
				$statusMessage = 'Internal server error';
				break;
		}
		header ("HTTP/1.1 {$statusCode} {$statusMessage}");
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();
