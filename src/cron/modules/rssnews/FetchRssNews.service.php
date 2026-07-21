<?php
	set_time_limit (0);

	require_once ('data/CRMEntity.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('modules/rssnews/lib/RssNewsManager.class.php');

	global $adb;

	/** @var Users $current_user */
	$current_user = CRMEntity::getInstance ('Users');
	$current_user->retrieveCurrentUserInfoFromFile (1);

	try {
		$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		if ((PlatformUtils::isModuleEnabled ($masterAdb, 'clientes_bdi')) && (PlatformUtils::isModuleEnabled ($masterAdb, 'medios_bdi'))) {
			RssNewsManager::getInstance ()->process ($masterAdb);
		} else {
			echo 'Módulos clientes_bdi y medios_bdi no activos en la plataforma principal. Saltando';
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
			if ((PlatformUtils::isModuleEnabled ($targetAdb, 'clientes_bdi')) && (PlatformUtils::isModuleEnabled ($targetAdb, 'medios_bdi'))) {
				RssNewsManager::getInstance ()->process ($targetAdb);
			}
		}
	} catch (Exception $e) {
		echo "Instancias: {$e->getMessage ()}" . PHP_EOL;
	}
