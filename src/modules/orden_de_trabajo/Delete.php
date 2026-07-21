<?php
	require_once ('data/CRMEntity.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/utils.php');
	require_once ('include/utils/VtlibUtils.php');
	require_once ('modules/proyectos/handlers/taskToProject.class.php');

	global $currentModule, $adb;

	$record        = isset ($_REQUEST ['record']) ? vtlib_purify ($_REQUEST ['record']) : '';
	$module        = isset ($_REQUEST ['module']) ? vtlib_purify ($_REQUEST ['module']) : '';
	$returnModule  = isset ($_REQUEST ['return_module']) ? vtlib_purify ($_REQUEST ['return_module']) : '';
	$returnAction  = isset ($_REQUEST ['return_action']) ? vtlib_purify ($_REQUEST ['return_action']) : '';
	$returnId      = isset ($_REQUEST ['return_id']) ? vtlib_purify ($_REQUEST ['return_id']) : '';
	$parentTab     = getParentTab ();
	$searchUrlPart = getBasic_Advance_SearchURL ();

	$entity = CRMEntity::getInstance ($currentModule);
	
	// Capturar proyectos relacionados antes de eliminar el trabajo
	$projectIds = array ();
	if (!empty ($record)) {
		$projectHandler = taskToProject::getInstance ($adb);
		$projectIds     = $projectHandler->getRelatedProjectIds ($record);
	}

	if ($returnAction == 'KANBAN-DELETE') {
		try {
			DeleteEntity($currentModule, $module, $entity, $record, null);
			
			// Recalcular fechas de proyectos afectados
			if (!empty ($projectIds)) {
				foreach ($projectIds as $projectId) {
					$projectHandler->recalculateProjectEstimatedDatesFromDb ($projectId);
				}
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
		exit();
	} else {
		DeleteEntity ($currentModule, $returnModule, $entity, $record, $returnId);
		
		// Recalcular fechas de proyectos afectados
		if (!empty ($projectIds)) {
			foreach ($projectIds as $projectId) {
				$projectHandler->recalculateProjectEstimatedDatesFromDb ($projectId);
			}
		}
	}

	header ("Location: index.php?module=$returnModule&action=$returnAction&record=$returnId&parenttab=$parentTab&relmodule={$module}{$searchUrlPart}");
