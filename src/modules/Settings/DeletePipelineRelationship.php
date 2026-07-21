<?php
	require_once ('include/platzilla/Managers/PicklistPipelineRelationshipManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	$moduleName        = PlatzillaUtils::purify ($_POST, 'formodule');
	$picklistFieldName = PlatzillaUtils::purify ($_POST, 'picklistfieldname');
	$pipelineFieldName = PlatzillaUtils::purify ($_POST, 'pipelinefieldname');
	$isInstance        = !empty ($_SESSION ['platInstancia']);

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado', 401);
		}

		if (empty ($moduleName)) {
			throw new Exception ('No se ha encontrado el módulo');
		}

		if (empty ($picklistFieldName)) {
			throw new Exception ('No se ha encontrado el campo Picklist');
		}

		if (empty ($pipelineFieldName)) {
			throw new Exception ('No se ha encontrado el campo Pipeline');
		}

		$relationship = PicklistPipelineRelationshipManager::getInstance($adb)->deleteRelationshipPicklistPipelineByFields($moduleName, $picklistFieldName, $pipelineFieldName);

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
