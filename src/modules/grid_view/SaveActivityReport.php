<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('modules/Calendar/Activity.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/grid_view/lib/GridViewHelper.class.php');

	global $adb, $app_strings, $current_user, $mod_strings, $theme, $site_URL;
	setBugSnag ($site_URL);

	$smarty = new vtigerCRM_Smarty ();

	$activityId   = PlatzillaUtils::purify ($_POST, 'task');
	$actionReport = PlatzillaUtils::purify ($_POST, 'action_report');
	$flModule     = PlatzillaUtils::purify ($_POST, 'fl_module');
	$progress     = PlatzillaUtils::purify ($_POST, 'progress', 1);
	$reportText   = PlatzillaUtils::purify ($_POST, 'description');
	$record       = PlatzillaUtils::purify ($_POST, 'record');
	$reportOn     = PlatzillaUtils::purify ($_POST, 'reportOn', 'TASK');
	$reportId     = PlatzillaUtils::purify ($_POST, 'reportid');
	$timeDuration       = PlatzillaUtils::purify ($_POST,'timeduration');
	$title              = PlatzillaUtils::purify ($_POST, 'title');
	$actualCost         = PlatzillaUtils::purify ($_POST, 'actualcost');
	$activityReportDate = PlatzillaUtils::purify ($_POST, 'activity_report_date');
	// Normalizar a yyyy-mm-dd para la BD (el JS ya envía este formato en el hidden,
	// pero como salvaguarda se convierte si llega en otro formato)
	if (!empty($activityReportDate) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $activityReportDate)) {
		$ts = strtotime($activityReportDate);
		$activityReportDate = ($ts !== false) ? date('Y-m-d', $ts) : null;
	}
	
	// Función para convertir número formateado a formato de BD (punto decimal)
	function convertToDBFormat($formattedValue, $userFormat) {
		if (empty($formattedValue)) return 0;
		
		$cleanValue = preg_replace('/[^\d.,-]/', '', $formattedValue);
		if ($cleanValue === '') return 0;
		
		$hasComma = strpos($cleanValue, ',') !== false;
		$hasDot = strpos($cleanValue, '.') !== false;
		
		if ($hasComma && $hasDot) {
			// Tiene ambos separadores: determinar cuál es decimal por posición
			$lastCommaIndex = strrpos($cleanValue, ',');
			$lastDotIndex = strrpos($cleanValue, '.');
			if ($lastCommaIndex > $lastDotIndex) {
				// Coma está después del punto: formato europeo (1.234,56)
				$numValue = floatval(str_replace('.', '', str_replace(',', '.', $cleanValue)));
			} else {
				// Punto está después de la coma: formato americano (1,234.56)
				$numValue = floatval(str_replace(',', '', $cleanValue));
			}
		} elseif ($hasComma) {
			// Solo coma: depende del formato del usuario
			if ($userFormat === 'EUROPEAN_FORMAT') {
				// En formato europeo, la coma es decimal
				$numValue = floatval(str_replace(',', '.', $cleanValue));
			} else {
				// En formato americano, la coma es separador de miles
				$numValue = floatval(str_replace(',', '', $cleanValue));
			}
		} else {
			// Solo punto o ninguno: ya está en formato correcto
			$numValue = floatval($cleanValue);
		}
		
		return is_nan($numValue) ? 0 : $numValue;
	}
	
	// Obtener formato numérico del usuario
	$userFormat = isset($current_user->column_fields['numbering_format']) ? $current_user->column_fields['numbering_format'] : 'AMERICAN_FORMAT';
	
	// Convertir timeDuration del formato del usuario al formato de BD (punto decimal)
	if (!empty($timeDuration)) {
		$timeDuration = convertToDBFormat($timeDuration, $userFormat);
	}
	
	// Convertir actualCost del formato del usuario al formato de BD (punto decimal)
	if (!empty($actualCost)) {
		$actualCost = convertToDBFormat($actualCost, $userFormat);
	}
	
	try {
		if (empty ($record)) {
			throw new Exception ('No se encontró el el registro asociado!');
		}
		
		// Al editar un reporte existente, preservar el activityId original del reporte
		// para evitar que se reasigne accidentalmente a otra tarea
		$originalActivityId = null;
		if ($actionReport == 'edit' && !empty($reportId)) {
			$existingReport = ActivityReportManager::getInstance($adb)->fetchActivityReportById($reportId, null, $current_user->id);
			if (!empty($existingReport)) {
				$originalActivityId = $existingReport->getActivityId();
			} else {
				throw new Exception('No se encontró el reporte o no tienes permisos para editarlo!');
			}
		}
		
		// Validar activityId solo si es modo creación y reportOn es TASK
		// En modo edición, el activityId se recupera del reporte existente
		if ($reportOn == 'TASK' && empty ($activityId) && empty($originalActivityId)) {
			throw new Exception ('No se encontró la actividad asociada!');
		}
		
		if ($reportOn == 'JOB') {
			$entityJob       = CRMEntity::getInstance ('orden_de_trabajo');
			$entityJob->id   = $record;
			$entityJob->mode = 'edit';
			$entityJob->retrieve_entity_info ($record, 'orden_de_trabajo');
			$orderStatus = $entityJob->column_fields ['estado_de_la_orden'];
			$jobTitle    = substr ($entityJob->column_fields ['titulo'], 0, 80);
			$jobUnit     = isset($entityJob->column_fields ['unidades_de_medida']) ? $entityJob->column_fields ['unidades_de_medida'] : 'Hora';
			
			// Para reportes de JOB, si el usuario ingresó un título personalizado, usarlo
			// Si no, usar el título del job
			if (!empty($title)) {
				// Mantener el título que el usuario ingresó
			} else {
				// Usar el título del job
				$title = $jobTitle;
			}
			$arm = ActivityReportManager::getInstance ($adb);
			$arm->updateTaskByJob ($record, floatval ($progress), $orderStatus);
			$activityJobId   = $arm->getTaskFromJobId ($record);
			$isNewTask    = false;
			$prevProgress = 0;
			$entity =  CRMEntity::getInstance ('Activity');
			if (empty ($activityJobId)) {
				$prevDuration = 0;
				$isNewTask    = true;
				$entity->id   = null;
				$entity->mode = 'create';
				$entity->column_fields = getColumnFields ('Calendar');
				$entity->column_fields ['activitytype']     = 'Job';
				$entity->column_fields ['subject']          = "Rep. trabajo {$jobTitle}";
				$entity->column_fields ['date_start']       = date ('Y-m-d');
				$entity->column_fields ['time_start']       = date ('H:i:s');
				$entity->column_fields ['eventstatus']      = ($progress < 100) ? 'Not Held' : 'Held';
				$entity->column_fields ['show_in_matrix']   = 'YES';
				$entity->column_fields ['assigned_user_id'] = $current_user->id;
				$entity->column_fields ['estimated_time']   = 0;
				$entity->column_fields ['estimated_time_unit'] = $jobUnit;
				$entity->column_fields ['related_id']       = $record;
			} else {
				$entity->id   = $activityJobId;
				$entity->mode = 'edit';
				$entity->retrieve_entity_info ($activityJobId, 'Calendar');
				$prevDuration = floatval ($entity->column_fields ['duration_hours']);
			}
			$entity->column_fields ['progress'] = floatval ($progress);
			if (floatval ($progress) >= 100) {
				$entity->column_fields ['due_date']    = date ('Y-m-d');
				$entity->column_fields ['time_end']    = date ('H:i:s');
				$entity->column_fields ['eventstatus'] = 'Held';
			}
			$entity->column_fields ['duration_hours'] = $timeDuration;
			$entity->save ('Calendar');
			$activityJobId = $entity->id;
			if ($isNewTask) {
				$adb->pquery ('INSERT IGNORE INTO vtiger_seactivityrel (crmid, activityid) VALUES (?, ?)', array($record, $activityJobId));
			}
		} else {
			// Al editar un reporte, usar el activityId original si está disponible
			// para evitar reasignaciones accidentales
			if ($actionReport == 'edit' && !empty($originalActivityId)) {
				$activityJobId = $originalActivityId;
			} else {
				$activityJobId = $activityId;
			}
			
			$entity        =  CRMEntity::getInstance ('Calendar');
			$entity->id    = $activityJobId;
			$entity->mode  = 'edit';
			$entity->retrieve_entity_info ($activityJobId, 'Calendar');
			
			if (floatval ($progress) >= 100) {
				$entity->column_fields ['due_date']    = (empty ($entity->column_fields ['due_date'])) ? date ('Y-m-d') : $entity->column_fields ['due_date'];
				$entity->column_fields ['eventstatus'] = 'Held';
			} else if (floatval ($progress) > 0 && floatval ($progress) < 100) {
				$entity->column_fields ['eventstatus'] = 'Not Held';
			}
			if (empty ($entity->column_fields ['date_start'])) {
				$entity->column_fields ['date_start'] = date ('Y-m-d');
			}
			$prevProgress                             = floatval ($entity->column_fields ['progress']);
			$entity->column_fields ['progress']       = $progress;
			$prevDuration                             = floatval ($entity->column_fields ['duration_hours']);
			$entity->column_fields ['duration_hours'] = floatval ($timeDuration);
			$entity->save ('Calendar');
			$jobTitle   = substr ($entity->column_fields ['subject'], 0, 80);
			
			// Si el usuario ingresó un título personalizado, usarlo
			// Si no, usar el valor por defecto "Reporte de actividad: <subject>"
			if (!empty($title)) {
				// Mantener el título que el usuario ingresó
			} else {
				// Usar el valor por defecto
				$title = "Reporte de actividad: {$jobTitle}";
			}
			unset ($entity);
		}
		if ($flModule == 'orden_de_trabajo') {
			$entityJob = CRMEntity::getInstance ('orden_de_trabajo');
			$entityJob->id = $record;
			$entityJob->mode = 'edit';
			$entityJob->retrieve_entity_info ($record, 'orden_de_trabajo');
			if ($actionReport == 'create') {
				$entityJob->column_fields ['unidades_consumidas'] += floatval ($timeDuration);
			} else {
				$entityJob->column_fields ['unidades_consumidas'] -= floatval ($prevDuration);
				$entityJob->column_fields ['unidades_consumidas'] += floatval ($timeDuration);
			}
			$calculatedProgress = ActivityReportManager::getInstance ($adb)->calculateProgress ($record);
			$entityJob->column_fields ['overall_progress_perc'] = $calculatedProgress;
			if (
				(floatval ($calculatedProgress) >= 100) &&
				($entityJob->column_fields ['estado_de_la_orden'] != 'Terminado') &&
				($reportOn == 'JOB')
			) {
				$entityJob->column_fields ['estado_de_la_orden'] = 'Terminado';
			} else if (
				(floatval ($progress) < 100) &&
				(
					($entityJob->column_fields ['estado_de_la_orden'] == 'Definido') ||
					($entityJob->column_fields ['estado_de_la_orden'] == 'Programado')
				)
			) {
				$entityJob->column_fields ['estado_de_la_orden'] = 'En curso';
			}
			
			$entityJob->save ('orden_de_trabajo');
			unset ($entityJob);
		}
		
		$activityReport = ActivityReport::getInstance ()
			->setId ($reportId)
			->setActivityId ($activityJobId)
			->setProgress (floatval ($progress))
			->setReport ($reportText)
			->setReportOn ($reportOn)
			->setActivityReportDate (!empty($activityReportDate) ? $activityReportDate : null)
			->setTimeDuration (floatval ($timeDuration))
			->setActualCost (!empty($actualCost) ? floatval($actualCost) : null)
			->setTitle ($title)
			->setUserId ($current_user->id);
			
		ActivityReportManager::getInstance ($adb)->saveActivityReport ($activityReport);
		
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
