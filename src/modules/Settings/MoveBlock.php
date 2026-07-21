<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	$moduleName = PlatzillaUtils::purify ($_POST, 'modulename');
	$blockId    = PlatzillaUtils::purify ($_POST, 'blockid');
	$sequence   = PlatzillaUtils::purify ($_POST, 'sequence');

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado', 401);
		} else if (empty ($moduleName)) {
			throw new Exception ('No has suministrado el nombre del módulo');
		} else if (empty ($blockId)) {
			throw new Exception ('No has suministrado el ID del bloque');
		} else if ($sequence === null) {
			throw new Exception ('No has suministrado la nueva posición del bloque');
		}

		$bm     = BlockManager::getInstance ($adb);
		$blocks = $bm->fetchBlocks ($moduleName);
		if (empty ($blocks)) {
			throw new Exception ('No hay bloques registrados');
		}

		$found = false;
		foreach ($blocks as $block) {
			if ($block->getId () == $blockId) {
				$block->setSequence ($sequence)
					->setLocked (!empty ($_SESSION ['platInstancia']));
				$found = true;
				$bm->updateBlockHeader ($block);
			} else if ($block->getSequence () == $sequence) {
				$block->setSequence ($found ? $block->getSequence () - 1 : $block->getSequence () + 1)
					->setLocked (!empty ($_SESSION ['platInstancia']));
				$bm->updateBlockHeader ($block);
			}
		}
		if (!$found) {
			throw new Exception ('No se encuentra el bloque suministrado');
		}

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
