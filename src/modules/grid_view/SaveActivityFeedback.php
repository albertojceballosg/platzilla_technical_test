<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Data/ActivityFeedbackManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/grid_view/lib/GridViewHelper.class.php');

	global $adb, $app_strings, $current_user, $mod_strings, $theme, $site_URL;
	setBugSnag ($site_URL);

	$smarty = new vtigerCRM_Smarty ();

	$activityId   = PlatzillaUtils::purify ($_POST, 'taskFeedback');
	$feedbackText = PlatzillaUtils::purify ($_POST, 'feedback');
	$record       = PlatzillaUtils::purify ($_POST, 'record');
	$reportId     = PlatzillaUtils::purify ($_POST, 'taskreport');
	$title        = PlatzillaUtils::purify ($_POST, 'titlefeedback');
	$feedbackId   = PlatzillaUtils::purify ($_POST, 'feedbackid');
	
	try {
		if (empty ($record)) {
			throw new Exception ('No se encontró el el registro asociado!');
		}

		if (empty ($activityId)) {
			throw new Exception ('actividad no encontrada o no registrada');
		}

		// Verificar si es una actualización o creación
		if (!empty($feedbackId)) {
			// Es una actualización - obtener el feedback existente
			$existingFeedback = ActivityFeedbackManager::getInstance($adb)->fetchActivityFeedbackById($feedbackId);
			if (empty($existingFeedback)) {
				throw new Exception('Feedback no encontrado para actualizar');
			}
			
			// Verificar permisos - solo el propietario puede actualizar
			if ($existingFeedback->getUserId() != $current_user->id) {
				throw new Exception('No tienes permisos para actualizar este feedback');
			}
			
			// Actualizar el feedback existente
			$activityFeedback = ActivityFeedback::getInstance ()
				->setId($feedbackId)
				->setActivityId ($activityId)
				->setFeedback ($feedbackText)
				->setTitle ($title)
				->setUserId ($current_user->id);
			
			$activityFeedback = ActivityFeedbackManager::getInstance ($adb)->saveActivityFeedback ($activityFeedback);
		} else {
			// Es una creación - feedback nuevo
			$activityFeedback = ActivityFeedback::getInstance ()
				->setActivityId ($activityId)
				->setFeedback ($feedbackText)
				->setTitle ($title)
				->setUserId ($current_user->id);
			
			$activityFeedback = ActivityFeedbackManager::getInstance ($adb)->saveActivityFeedback ($activityFeedback);
		}
		
		// Manejar la relación con el reporte
		if (!empty($activityId) && !empty($reportId) && !empty($activityFeedback->getId ())) {
			// Si es actualización, eliminar relaciones anteriores y crear la nueva
			if (!empty($feedbackId)) {
				ActivityFeedbackManager::getInstance ($adb)->deleteReportFromFeedback ($activityFeedback->getId ());
			}
			ActivityFeedbackManager::getInstance ($adb)->saveReportToFeedback ($activityId, $reportId, $activityFeedback->getId ());
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
