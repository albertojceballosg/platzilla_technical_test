<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/AttachmentsUtils.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/grid_view/lib/GridViewHelper.class.php');

	global $adb, $app_strings, $current_user, $mod_strings, $theme, $site_URL;
	setBugSnag ($site_URL);

	$activityId = PlatzillaUtils::purify ($_GET, 'activityid');
	$entityId   = PlatzillaUtils::purify ($_GET, 'record');
	$flModule   = PlatzillaUtils::purify ($_GET, 'formodule', null);
	$reportId   = PlatzillaUtils::purify ($_GET, 'reportid');
	$feedbackId = PlatzillaUtils::purify ($_GET, 'feedbackid');
	$report     = null;
	$feedback   = null;
	$smarty = new vtigerCRM_Smarty ();
	
	try {
		if (!empty($reportId)) {
			$actionReport = 'edit';
			
			$report = ActivityReportManager::getInstance ($adb)->fetchActivityReportById ($reportId, $activityId, $current_user->id);
			if (empty($report)) {
				throw new Exception ('Informe de actividad no encontrado o no tienes permisos para editarlo!');
			}
		} else {
			$actionReport = 'create';
		}
		
		// Cargar datos de feedback si se pasa feedbackid
		$associatedReportId = null;
		if (!empty($feedbackId)) {
			require_once('include/platzilla/Data/ActivityFeedbackManager.php');
			$feedbackManager = ActivityFeedbackManager::getInstance($adb);
			$feedbacks = $feedbackManager->fetchActivityFeedbackByActivity($activityId);
			
			// Buscar el feedback específico
			foreach ($feedbacks as $fb) {
				if ($fb->getId() == $feedbackId) {
					// Verificar permisos: solo el propietario puede editar
					if ($fb->getUserId() == $current_user->id) {
						$feedback = $fb;
						
						// Obtener el ID del reporte asociado al feedback
						$reportRelationSql = "SELECT activityreportid FROM vtiger_activity_report2feedback WHERE activityfeedbackid = ?";
						$reportRelationResult = $adb->pquery($reportRelationSql, array($feedbackId));
						if ($adb->num_rows($reportRelationResult) > 0) {
							$associatedReportId = $adb->query_result($reportRelationResult, 0, 'activityreportid');
						}
						break;
					} else {
						throw new Exception ('No tienes permisos para editar este feedback!');
					}
				}
			}
			
			if (empty($feedback)) {
				throw new Exception ('Feedback no encontrado!');
			}
		}
		
		if (empty ($entityId)) {
			throw new Exception ('No hay registro asociado!');
		}
		
		// Lógica de carga de tareas según el contexto:
		// 1. EDITAR REPORTE EXISTENTE: Solo la tarea del reporte (sin importar estado)
		// 2. CREAR DESDE TAREA ESPECÍFICA: Solo esa tarea (como edición, pero creando)
		// 3. CREAR SIN TAREA: Todas las tareas disponibles
	
		if (!empty($report)) {
			// CASO 1: EDITAR REPORTE EXISTENTE
			$reportActivityId = $report->getActivityId();
			
			$taskResult = $adb->pquery(
				"SELECT a.*, crm.description, crm.smownerid, a.estimated_time_unit
				 FROM vtiger_activity a
				 INNER JOIN vtiger_crmentity crm ON crm.crmid = a.activityid
				 WHERE a.activityid = ? AND crm.deleted = 0",
				array($reportActivityId)
			);
			
			$availableTask = array();
			if ($adb->num_rows($taskResult) > 0) {
				$taskData = $adb->fetchByAssoc($taskResult);
				$taskObj = new BoxTaskContent();
				$taskObj->setActivityId($taskData['activityid']);
				$taskObj->setSubject($taskData['subject']);
				$taskObj->setDescription($taskData['description']);
				$taskObj->setProgress($taskData['progress']);
				$taskObj->setActivityType($taskData['activitytype']);
				$taskObj->setEventStatus($taskData['eventstatus']);
				$taskObj->setEstimatedTimeUnit($taskData['estimated_time_unit']);
				
				$availableTask[] = $taskObj;
			}
		} elseif (!empty($activityId)) {
			// CASO 2: CREAR DESDE TAREA ESPECÍFICA
			
			$taskResult = $adb->pquery(
				"SELECT a.*, crm.description, crm.smownerid, a.estimated_time_unit
				 FROM vtiger_activity a
				 INNER JOIN vtiger_crmentity crm ON crm.crmid = a.activityid
				 WHERE a.activityid = ? AND crm.deleted = 0",
				array($activityId)
			);
			
			// Obtener el máximo entre: progreso reportado en informes previos y progreso actual de la tarea
			$maxProgressResult = $adb->pquery(
				"SELECT COALESCE(MAX(ar.progress), 0) AS max_report_progress, COALESCE(a.progress, 0) AS task_progress
				 FROM vtiger_activity a
				 LEFT JOIN vtiger_activity_report ar ON ar.activityid = a.activityid AND (ar.deleted = 0 OR ar.deleted IS NULL)
				 WHERE a.activityid = ?
				 GROUP BY a.activityid",
				array($activityId)
			);
			$initialProgress = 0;
			if ($adb->num_rows($maxProgressResult) > 0) {
				$row = $adb->fetchByAssoc($maxProgressResult);
				$initialProgress = max(floatval($row['max_report_progress']), floatval($row['task_progress']));
			}
			DatabaseUtils::closeResult($maxProgressResult);

			$availableTask = array();
			if ($adb->num_rows($taskResult) > 0) {
				$taskData = $adb->fetchByAssoc($taskResult);
				$taskObj = new BoxTaskContent();
				$taskObj->setActivityId($taskData['activityid']);
				$taskObj->setSubject($taskData['subject']);
				$taskObj->setDescription($taskData['description']);
				$taskObj->setProgress($initialProgress);
				$taskObj->setActivityType($taskData['activitytype']);
				$taskObj->setEventStatus($taskData['eventstatus']);
				$taskObj->setEstimatedTimeUnit($taskData['estimated_time_unit']);
				
				$availableTask[] = $taskObj;
			}
		} else {
			// CASO 3: CREAR SIN TAREA ESPECÍFICA
			$availableTask = GridViewHelper::fetchTaskList ($adb, $entityId, array ('Activity', 'Call', 'Meeting', 'Job'), $current_user);
			if (empty($availableTask)) {
				$availableTask = array();
			}
		}

		$activities = array ();
		$preselectedTask = null;
		foreach ($availableTask as $task) {
			$taskDescriptionForJs = html_entity_decode($task->getDescription(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
			// Garantizar UTF-8 valido, de lo contrario json_encode falla silenciosamente (retorna false)
			// y rompe el JS generado (ReprtActivityUtils.init(, ...))
			if (!empty($taskDescriptionForJs) && !mb_check_encoding($taskDescriptionForJs, 'UTF-8')) {
				$taskDescriptionForJs = mb_convert_encoding($taskDescriptionForJs, 'UTF-8', 'UTF-8');
			}
			$activities[ $task->getActivityId()] = array (
				'description' => $taskDescriptionForJs,
				'progress'    => $task->getProgress (),
				'timeUnit'    => $task->getEstimatedTimeUnit (),
			);
			
			// Si se pasó un activityId por URL, preseleccionar esa tarea
			if (!empty($activityId) && $task->getActivityId() == $activityId) {
				$preselectedTask = $task;
			}
			
			// Si se está editando un reporte existente, preseleccionar la tarea asociada
			if (!empty($report) && $task->getActivityId() == $report->getActivityId()) {
				$preselectedTask = $task;
				$activityId = $report->getActivityId();
			}
		}

		// Cargar reportes existentes SOLO para el dropdown de feedbacks
		// No para la edición del reporte específico
		$activityReports = array();
		if (!empty($activityId)) {
			$activityReports = GridViewHelper::fetchActivityReport($adb, $entityId, $current_user, $activityId);
		}
		$smarty->assign ('ACTIVITY_REPORTS', $activityReports);

		// Cargar feedbacks para el dropdown en la pestaña Feedback
		$activityFeedbacks = array();
		if (!empty($activityId)) {
			// Cargando feedbacks para el dropdown en la pestaña Feedback
			require_once('include/platzilla/Data/ActivityFeedbackManager.php');
			$feedbackManager = ActivityFeedbackManager::getInstance($adb);
			$activityFeedbacks = $feedbackManager->fetchActivityFeedbackByActivity($activityId);
			// Feedbacks encontrados: (count($activityFeedbacks)) 
		}

		$smarty->assign ('ACTIVITY_FEEDBACKS', $activityFeedbacks);
		$smarty->assign ('ACTION_REPORT', (isset ($actionReport)) ? $actionReport : 'create');
		$smarty->assign ('AVAILABLE_TASK',$availableTask);
		$encodedActivities = json_encode ($activities, JSON_FORCE_OBJECT);
		$smarty->assign ('AVAILABLE_OBJETCS_TASK', ($encodedActivities !== false) ? $encodedActivities : '{}');
		$smarty->assign ('CURRENT_USER', $current_user);
		$smarty->assign ('FL_MODULE',$flModule);
		$smarty->assign ('ID', $entityId);
		$smarty->assign ('FEEDBACK', $feedback);
		$smarty->assign ('ACTIVE_TAB', (!empty($feedbackId)) ? 'FEEDBACK' : 'REPORT');
		$smarty->assign ('ASSOCIATED_REPORT_ID', $associatedReportId);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('NUMBERING_FORMAT', isset($current_user->column_fields['numbering_format']) ? $current_user->column_fields['numbering_format'] : 'AMERICAN_FORMAT');
		$smarty->assign ('USER_DATE_FORMAT',  isset($current_user->column_fields['date_format'])     ? $current_user->column_fields['date_format']     : 'dd/mm/yyyy');
		$smarty->assign ('REPORT', $report);
		$smarty->assign ('PRESELECTED_ACTIVITY_ID', $activityId);
		$smarty->assign ('PRESELECTED_TASK', $preselectedTask);
		$smarty->assign ('TASK_ATTACHMENTS', (!empty($reportId)) ? AttachmentsUtils::fetchActivityReportAttachments($adb, $reportId) : array());
		$smarty->assign ('UPLOAD_MAXSIZE', (PlatzillaUtils::getMaxFileSizeInMb() * 1024 * 1024));
		
		// Asignar las variables que el template necesita para mostrar los datos del reporte
		if (!empty($report)) {
			$smarty->assign ('reportTitle', $report->getTitle());
			$smarty->assign ('reportTime',  $report->getTimeDuration() !== null ? $report->getTimeDuration() : 0.0);
			$smarty->assign ('reportCost',  $report->getActualCost()   !== null ? $report->getActualCost()   : 0.0);
			$smarty->assign ('taskReport', $report->getReport());
		} else {
			$smarty->assign ('reportTitle', '');
			$smarty->assign ('reportTime',  '');
			$smarty->assign ('reportCost',  '');
			$smarty->assign ('taskReport', '');
		}
	} catch (Exception $e) {
		$smarty->assign ('IS_ERROR', true);
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
	}
	$smarty->display ('modules/grid_view/BoxContenets/EditActivityReport.tpl');
