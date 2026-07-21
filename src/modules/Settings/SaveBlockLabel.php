<?php
	require_once ('include/platzilla/Managers/BlockManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado', 401);
		}

		$blockId    = PlatzillaUtils::purify ($_POST, 'blockid');
		$label      = PlatzillaUtils::purify ($_POST, 'label');
		$isInstance = !empty ($_SESSION ['platInstancia']);

		if (empty ($blockId)) {
			throw new Exception ('No has suministrado el ID del bloque');
		} else if (empty ($label)) {
			throw new Exception ('No has suministrado la etiqueta');
		}

		$bm    = BlockManager::getInstance ($adb);
		$block = $bm->fetchBlock ($blockId, true);
		if (empty ($block)) {
			throw new Exception ('El bloque suministrado no se encuentra registrado');
		}

		$block->setLabel ($label)->setLocked ($isInstance);
		$bm->updateBlockHeader ($block);
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
