<?php
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/platzilla/Managers/TableFieldManager.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	$moduleName = PlatzillaUtils::purify ($_POST, 'modulename');
	$fieldName  = PlatzillaUtils::purify ($_POST, 'fieldname');

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado', 401);
		} else if (empty ($moduleName)) {
			throw new Exception ('No has suministrado el nombre del módulo');
		} else if (empty ($fieldName)) {
			throw new Exception ('No has suministrado nombre del campo');
		}

		$fm    = FieldManager::getInstance ($adb);
		$field = $fm->fetchFieldByName ($moduleName, $fieldName);
		if (empty ($field)) {
			throw new Exception ('El campo suministrado no está registrado');
		}
		if ($field->getUiType () == FieldInterface::UI_TYPE_TABLE_FIELD) {
			TableFieldManager::getInstance ($adb)->deleteTableField ($field->getName (), $moduleName);
			DatabaseUtils::deleteTableIfExists ($adb, $field->getTableName ());
		}
		$fm->deleteField ($field);
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
