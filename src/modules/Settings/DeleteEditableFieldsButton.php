<?php
	require_once ('include/platzilla/Managers/EditableFieldsManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	$buttonName = PlatzillaUtils::purify ($_POST, 'buttonname');
	$isInstance = !empty ($_SESSION ['platInstancia']);

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado', 401);
		}

		if (empty($buttonName)) {
			throw new Exception ('No has suministrado el botón a editar');
		}

		$button = EditableFieldsManager::getInstance($adb)->deleteEditableButton ($buttonName);

		if (empty ($button)) {
			throw new Exception ('Se ha presentado un error. Intenta más tarde');
		}

		header('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode (array ('error' => 'OK'));
	} catch (Exception $e) {
		header('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode (array('error' => $e->getMessage ()));
	}
	exit ();
