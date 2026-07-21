<?php
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	$moduleName = PlatzillaUtils::purify ($_POST, 'modulename');
	$fieldId    = PlatzillaUtils::purify ($_POST, 'fieldid');
	$label      = PlatzillaUtils::purify ($_POST, 'label');

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado', 401);
		} else if (empty ($moduleName)) {
			throw new Exception ('No has suministrado el nombre del módulo');
		} else if (empty ($fieldId)) {
			throw new Exception ('No has suministrado el ID del campo');
		} else if (empty ($label)) {
			throw new Exception ('No has suministrado el nombre del campo');
		}


		$fm    = FieldManager::getInstance ($adb);
		$field = $fm->fetchFieldById ($fieldId);
		if (empty ($field)) {
			throw new Exception ('El campo suministrado no se encuentra registrado');
		} else if ($field->getModuleName () != $moduleName) {
			throw new Exception ('El campo no se encuentra asociado al módulo suministrado');
		}

		$field->setLabel ($label)->setLocked (!empty ($_SESSION ['platInstancia']));
		$fm->updateFieldHeader ($field);
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
