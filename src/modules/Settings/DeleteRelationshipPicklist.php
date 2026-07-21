<?php
	require_once ('include/platzilla/Managers/PicklistRelationshipManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	$moduleName   = PlatzillaUtils::purify ($_POST, 'formodule');
	$relationName = PlatzillaUtils::purify ($_POST, 'relationname');
	$isInstance   = !empty ($_SESSION ['platInstancia']);

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado', 401);
		}

		if (empty ($moduleName)) {
			throw new Exception ('No se ha encontrado el módulo');
		}

		if (empty ($relationName)) {
			throw new Exception ('No se ha encontrado la relación entre campos lista indicado');
		}
		$relationship = PicklistRelationshipManager::getInstance($adb)->deleteRelationshipPicklist($moduleName, $relationName);

		if (empty ($relationship)) {
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
