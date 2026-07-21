<?php
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	$moduleName = PlatzillaUtils::purify ($_POST, 'modulename');
	$menuLabel  = PlatzillaUtils::purify ($_POST, 'menulabel');

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado', 401);
		} else if (empty ($moduleName)) {
			throw new Exception ('No has suministrado el nombre del módulo');
		}

		$mm = ModuleManager::getInstance ($adb);
		$moduleObject = $mm->fetchModule ($moduleName, true);
		if (empty ($moduleObject)) {
			throw new Exception ('El módulo suministrado no está registrado');
		}

		if (empty ($menuLabel)) {
			$moduleObject->setMenuLabel (null)
				->setShowInSettings (true);
		} else {
			$moduleObject->setMenuLabel ($menuLabel)
				->setShowInSettings (false);
		}
		$mm->updateModuleHeader ($moduleObject);
		create_tab_data_file ();
		create_parenttab_data_file ();
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ('OK');
	} catch (Exception $e) {
		if ($e->getCode () == 401) {
			header ('HTTP/1.1 401 Access denied');
		} else {
			header ('HTTP/1.1 400 Bad request');
		}
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();
