<?php
	require_once ('include/platzilla/Managers/BlockManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	$blockId   = PlatzillaUtils::purify ($_POST, 'blockid');
	$sequences = PlatzillaUtils::purify ($_POST, 'sequences');

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado', 401);
		} else if (empty ($sequences)) {
			throw new Exception ('No has suministrado los campos');
		}

		$block = BlockManager::getInstance ($adb)->fetchBlock ($blockId);
		if (empty ($block)) {
			throw new Exception ('El bloque suministrado no está registrado');
		}

		$fm = FieldManager::getInstance ($adb);
		$fields     = $block->getFields ();
		foreach ($fields as $field) {
			$fieldId = $field->getId ();
			if (!isset ($sequences [ $fieldId ])) {
				continue;
			} else if ($field->getSequence () != $sequences [$fieldId]) {
				$hasChanges = true;
				$field->setSequence ($sequences [$fieldId])
					->setLocked (!empty ($_SESSION ['platInstancia']));
				$fm->updateFieldHeader ($field);
			}
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