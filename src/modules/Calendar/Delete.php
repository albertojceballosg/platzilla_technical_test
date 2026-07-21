<?php
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $currentModule;

	$activityId   = PlatzillaUtils::purify ($_POST, 'record');
	$returnAction = PlatzillaUtils::purify ($_POST, 'return_action', 'ListView');
	$returnModule = PlatzillaUtils::purify ($_POST, 'return_module', 'Calendar');
	$function     = PlatzillaUtils::purify ($_POST, 'function', null);

	try {
		$workIds = array ();
		// Capturar trabajos relacionados (orden_de_trabajo) antes de eliminar la actividad
		if (!empty ($activityId)) {
			$result = $adb->pquery (
				'SELECT ce.crmid
				   FROM vtiger_seactivityrel sar
				   INNER JOIN vtiger_crmentity ce ON ce.crmid = sar.crmid AND ce.deleted = 0
				  WHERE sar.activityid = ? AND ce.setype = ?'
				,
				array ($activityId, 'orden_de_trabajo')
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$workIds[] = (int)$row['crmid'];
				}
			}
		}
		
		$focus = CRMEntity::getInstance ($currentModule);

		DeleteEntity ($currentModule, $returnModule, $focus, $activityId, null);

		$adb->pquery (
			'UPDATE vtiger_activity SET eventstatus=? WHERE activityid=?',
			array ('Held', $activityId)
		);
		
		// Recalcular fechas estimadas de los trabajos afectados
		if (!empty ($workIds)) {
			require_once ('modules/orden_de_trabajo/handlers/taskToWork.class.php');
			$taskHandler = taskToWork::getInstance ($adb);
			$workIds     = array_unique ($workIds);
			foreach ($workIds as $workId) {
				$taskHandler->recalculateWorkEstimatedDatesFromDb ($workId);
			}
		}
		
		if ($function == 'TASK_FROM_MODULE') {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => 'OK'));
			exit();
		}

		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'La tarea ha sido eliminada',
		);
	} catch (Exception $e) {
		if ($function == 'TASK_FROM_MODULE') {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage ()));
		}
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ("Location: index.php?module={$returnModule}&action={$returnAction}");
	exit ();
