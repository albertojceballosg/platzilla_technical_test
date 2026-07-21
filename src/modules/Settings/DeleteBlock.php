<?php
	require_once ('include/platzilla/Managers/BlockManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	$moduleName = PlatzillaUtils::purify ($_POST, 'modulename');
	$blockId    = PlatzillaUtils::purify ($_POST, 'blockid');

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado', 401);
		} else if (empty ($moduleName)) {
			throw new Exception ('No has suministrado el nombre del módulo');
		} else if (empty ($blockId)) {
			throw new Exception ('No has suministrado el ID del bloque');
		}

		$bm     = BlockManager::getInstance ($adb);
		$block = $bm->fetchBlock ($blockId, true);
		if (empty ($block)) {
			throw new Exception ('El bloque suministrado no está registrado');
		} else if ($block->getModuleName () != $moduleName) {
			throw new Exception ('El bloque suministrado no está asociado al módulo suministrado');
		}
		$bm->deleteBlock ($block);
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
