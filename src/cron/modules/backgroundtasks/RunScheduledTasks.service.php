<?php
	set_time_limit (0);

	require_once ('data/CRMEntity.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksRunner.class.php');

	global $adb, $platPrincipal;

	/** @var Users $current_user */
	$current_user = CRMEntity::getInstance ('Users');
	$current_user->retrieveCurrentUserInfoFromFile (1);

	try {
		require ('config.inc.php');
		$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		if (PlatformUtils::isModuleEnabled ($masterAdb, 'backgroundtasks')) {
			BackgroundTasksRunner::getInstance ($masterAdb, $platPrincipal)->runScheduledTasks ();
		} else {
			echo 'Módulo backgroundtasks no activo en la plataforma principal. Saltando';
		}
	} catch (Exception $e) {
		echo "Plataforma principal: {$e->getMessage ()}" . PHP_EOL;
	}

	try {
		$instances = PlatformUtils::getValidInstances ();
		if (empty ($instances)) {
			return;
		}

		foreach ($instances as $instance) {
			$targetAdb = AdbManager::getInstance ()->getTargetInstanceAdb ($instance ['code']);
			if (PlatformUtils::isModuleEnabled ($targetAdb, 'backgroundtasks')) {
				BackgroundTasksRunner::getInstance ($targetAdb, $instance ['code'])->runScheduledTasks ();
			}
		}
	} catch (Exception $e) {
		echo "Instancias: {$e->getMessage ()}" . PHP_EOL;
	}
