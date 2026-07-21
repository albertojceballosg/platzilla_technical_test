<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	$moduleName = PlatzillaUtils::purify ($_POST, 'modulename');
	$blockLabel = PlatzillaUtils::purify ($_POST, 'label');
	$sequence   = PlatzillaUtils::purify ($_POST, 'sequence');

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado', 401);
		} else if (empty ($moduleName)) {
			throw new Exception ('No has suministrado el nombre del módulo');
		} else if (empty ($blockLabel)) {
			throw new Exception ('No has suministrado el nombre del bloque');
		} else if ($sequence === null) {
			throw new Exception ('No has suministrado el bloque siguiente');
		}

		$bm     = BlockManager::getInstance ($adb);
		$blocks = $bm->fetchBlocks ($moduleName);
		if (empty ($blocks)) {
			$blocks = array ();
		} else if ($sequence != -1) {
			foreach ($blocks as $block) {
				if ($block->getSequence () >= $sequence) {
					$block->setSequence ($block->getSequence () + 1)
						->setLocked (!empty ($_SESSION ['platInstancia']));
				}
			}
		} else if ($sequence == -1) {
			$sequence = null;
		}
		$blocks [] = Block::getInstance ()
			->setDeleted (false)
			->setIsCustom (Block::IS_CUSTOM_YES)
			->setLabel ($blockLabel)
			->setLocked (!empty ($_SESSION ['platInstancia']))
			->setModuleName ($moduleName)
			->setSequence ($sequence);
		$bm->saveBlocks ($moduleName, $blocks);
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
