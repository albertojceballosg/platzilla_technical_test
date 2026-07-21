<?php
	require_once ('Smarty_setup.php');
	require_once('include/utils/DataViewUtils.php');
	require_once ('include/utils/NumberHelper.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('modules/Calendar/Activity.php');
	require_once ('modules/notifications/lib/NotificationPeriodUtils.class.php');
	require_once ('modules/operating_modes/lib/ManagementModeHelper.class.php');
	require_once ('modules/operating_modes/Objects/OperatingModesInterface.php');
	require_once ('modules/Settings/lib/PanelViewHelper.class.php');
    
    global $adb, $app_strings, $current_user, $current_module, $mod_strings, $site_URL, $theme;
    
    setBugSnag ($site_URL);
	$calendarFunction = array ('CALENDAR_CREATE_ACTIVITY','CALENDAR_RESIZE_EVENT','CALENDAR_UPDATE_DATE');
	$function         = PlatzillaUtils::purify ($_REQUEST, 'function');
    $isInstance       = ! empty ($_SESSION ['platInstancia']);
	if (!in_array ($function, $calendarFunction)) {
		try {
			$calendarView = PlatzillaUtils::purify ($_REQUEST, 'calendar_view', 'NO');
			$function     = PlatzillaUtils::purify ($_REQUEST, 'function');
			$dueDate      = PlatzillaUtils::purify ($_POST, 'duedate', null);
			$moduleName   = PlatzillaUtils::purify ($_REQUEST, 'flmodule');
			$page         = PlatzillaUtils::purify ($_POST, 'page');
			$periodTask   = PlatzillaUtils::purify ($_POST, 'periodtask');
			$startDate    = PlatzillaUtils::purify ($_POST, 'datestart', null);
			$totalRecords = PlatzillaUtils::purify ($_POST, 'total_records');
			$usersId      = PlatzillaUtils::purify ($_POST, 'inviteesid');
			$workId       = PlatzillaUtils::purify ($_POST, 'hometabid');
			
			if ($periodTask == 'custom') {
				// Convertir fechas - soporta formatos dd/mm/yyyy y Y-m-d
				$periodDates ['startdate'] = date('Y-m-d');
				$periodDates ['enddate'] = date('Y-m-d');
				
				if (!empty($startDate)) {
					$dateObj = DateTime::createFromFormat('d/m/Y', $startDate);
					if ($dateObj === false) {
						$dateObj = DateTime::createFromFormat('Y-m-d', $startDate);
					}
					if ($dateObj !== false) {
						$periodDates ['startdate'] = $dateObj->format('Y-m-d');
					}
				}
				
				if (!empty($dueDate)) {
					$dateObj = DateTime::createFromFormat('d/m/Y', $dueDate);
					if ($dateObj === false) {
						$dateObj = DateTime::createFromFormat('Y-m-d', $dueDate);
					}
					if ($dateObj !== false) {
						$periodDates ['enddate'] = $dateObj->format('Y-m-d');
					}
				}
			} else {
				$periodDates = NotificationPeriodUtils::getStandarFiltersStartAndEndDate ($periodTask);
			}
			$recordPerPage = ManagementModeHelper::RECORDS_PER_PAGE;
			if ((empty ($page)) || ($page <= 0)) {
				$startRecord = 0;
			} else {
				$startRecord = (($page - 1) * $recordPerPage);
			}
			$smarty = new vtigerCRM_Smarty ();
			if ($function == 'WORK_IN_PROGRESS') {
				$moduleInProgress = ManagementModeHelper::fetchWorkInProgress ($adb, $usersId, $periodDates, $startRecord);
				$tableHeader      = ManagementModeHelper::WORK_TABLE_HEADER;
				$rowFields        = ManagementModeHelper::WORK_TABLE_ROW;
				$preference       = array ('HIDDEN' => array ('ALL'), 'SHOW' => array ('orden_de_trabajo'));
				$availableModules = PanelViewHelper::fetchAvailableModules ($adb, $current_user->id, $preference);
				$calendarType     = 'task';
			} else if ($function == 'PROJECT_IN_PROGRESS') {
				$moduleInProgress = ManagementModeHelper::fetchProjectsInProgress ($adb, $usersId, $periodDates, $startRecord);
				$tableHeader      = ManagementModeHelper::PROJECT_TABLE_HEADER;
				$rowFields        = ManagementModeHelper::PROJECT_TABLE_ROW;
				$preference       = array ('HIDDEN' => array ('ALL'), 'SHOW' => array ('proyectos'));
				$availableModules = PanelViewHelper::fetchAvailableModules ($adb, $current_user->id, $preference);
				$calendarType     = 'task';
			} else if ($function == 'ORDERS_TO_PROCESSED') {
				$moduleInProgress = ManagementModeHelper::fetchOrdersToProcessed ($adb, $usersId, $periodDates, $startRecord);
				$tableHeader      = ManagementModeHelper::ORDERS_TABLE_HEADER;
				$rowFields        = ManagementModeHelper::ORDERS_TABLE_ROW;
			} else if ($function == 'ISSUES_TO_PROCESSED') {
				$moduleInProgress = ManagementModeHelper::fetchIssuesToProcessed ($adb, $usersId, $periodDates, $startRecord);
				$tableHeader      = ManagementModeHelper::ISSUES_TABLE_HEADER;
				$rowFields = ManagementModeHelper::ISSUES_TABLE_ROW;
			} else if ($function == 'OPPORTUNITIES_TO_PROCESSED') {
				$moduleInProgress = ManagementModeHelper::fetchOpportunitiesToProcessed ($adb, $usersId, $periodDates, $startRecord, $current_user);
				$tableHeader = ManagementModeHelper::OPPORTUNITIES_TABLE_HEADER;
				$rowFields   = ManagementModeHelper::OPPORTUNITIES_TABLE_ROW;
			} else if ($function == 'ACTONS_IN_PROGRESS') {
				$moduleInProgress = ManagementModeHelper::fetchActionTasksInProgress ($adb, $usersId, $periodDates, $startRecord);
				$tableHeader      = ManagementModeHelper::ACTIONS_TABLE_HEADER;
				$rowFields        = ManagementModeHelper::ACTIONS_TABLE_ROW;
				$preference       = array ('HIDDEN' => array ('orden_de_trabajo', 'proyectos'));
				$availableModules = PanelViewHelper::fetchAvailableModules ($adb, $current_user->id, $preference);
				$calendarType     = 'task';
			} else if ($function == 'WORK_BY_SUPPLIER') {
				$supplierId = PlatzillaUtils::purify ($_POST, 'supplierid');
				$moduleInProgress = ManagementModeHelper::fetchWorkBySupplier ($adb, $supplierId, $periodDates, $startRecord);
				$tableHeader      = ManagementModeHelper::SUPPLIER_WORK_TABLE_HEADER;
				$rowFields        = ManagementModeHelper::SUPPLIER_WORK_TABLE_ROW;
				$useRowsOnlyTemplate = true;
			} else if ($function == 'ACTIVITY_REPORT') {
				$users = explode (',', $usersId);
				$activityReport = ManagementModeHelper::fetchActivityReport ($adb, $current_user, $periodDates, true, $users);
				header ('Access-Control-Allow-Origin: *');
				header ('HTTP/1.1 200 OK');
				header ('Content-Type: application/json; charset=utf-8');
				echo json_encode (array('error' => 'OK', 'html' => $activityReport));
				exit ();
			} else if ($function == 'REFRESH_SUPPLIERS') {
				// Actualizar lista de proveedores con conteo de tareas según el período seleccionado
				$suppliers = ManagementModeHelper::fetchSuppliersWithTasks ($adb, $periodDates);
				header ('Access-Control-Allow-Origin: *');
				header ('HTTP/1.1 200 OK');
				header ('Content-Type: application/json; charset=utf-8');
				echo json_encode (array('error' => 'OK', 'suppliers' => $suppliers));
				exit ();
			} else if ($function == 'SUPPLIER_GANTT') {
				require_once('include/utils/GanttModuleViewUtils.class.php');
				$supplierId = PlatzillaUtils::purify ($_POST, 'supplierid');
				
				if (empty($supplierId)) {
					throw new Exception('Proveedor no especificado');
				}
				
				// Obtener las tareas del proveedor con información de jerarquía completa
				$supplierTasks = ManagementModeHelper::fetchWorkBySupplier ($adb, $supplierId, $periodDates, 0);
				
				if (empty($supplierTasks) || empty($supplierTasks[0])) {
					throw new Exception('No hay tareas para mostrar en el diagrama Gantt');
				}
				
				// Construir datos para el Gantt con jerarquía: Proyecto → Etapa → Trabajo → Tarea
				$ganttData = array();
				$projectsAdded = array();
				$stagesAdded = array();
				$jobsAdded = array();
				
				// Constantes para trabajos sin proyecto
				$INDEPENDENT_PROJECT_ID = 'independent';
				$INDEPENDENT_PROJECT_NAME = 'Trabajos independientes';
				$INDEPENDENT_STAGE_NAME = 'Sin etapa';
				
				foreach ($supplierTasks[0] as $task) {
					$taskId = $task['activityid'];
					$workOrderId = $task['orden_de_trabajoid'];
					$projectId = !empty($task['proyectoid']) ? $task['proyectoid'] : null;
					$projectName = !empty($task['project_name']) ? $task['project_name'] : null;
					$workTitle = !empty($task['titulo']) ? $task['titulo'] : 'Trabajo sin título';
					
					$hasProject = !empty($projectId) && !empty($projectName);
					
					// ============================================
					// NIVEL 1: Proyecto (real o virtual)
					// ============================================
					$projectTaskId = null;
					$currentProjectId = $hasProject ? $projectId : $INDEPENDENT_PROJECT_ID;
					
					if (!isset($projectsAdded[$currentProjectId])) {
						$projectTaskId = "project-{$currentProjectId}";
						$ganttData[] = array(
							'id' => $projectTaskId,
							'name' => $hasProject ? $projectName . ' (PROYECTO)' : $INDEPENDENT_PROJECT_NAME,
							'start' => null,
							'end' => null,
							'progress' => 0,
							'dependencies' => '',
							'custom_class' => 'task-level-1 task-project' . ($hasProject ? '' : ' task-independent')
						);
						$projectsAdded[$currentProjectId] = array(
							'id' => $projectTaskId,
							'index' => count($ganttData) - 1
						);
					} else {
						$projectTaskId = $projectsAdded[$currentProjectId]['id'];
					}
					
					// ============================================
					// NIVEL 2: Etapa (virtual basada en proyecto)
					// ============================================
					$stageName = $hasProject ? 'Etapa del proyecto' : $INDEPENDENT_STAGE_NAME;
					$stageKey = "{$currentProjectId}-stage";
					$stageTaskId = null;
					
					if (!isset($stagesAdded[$stageKey])) {
						$stageTaskId = "stage-{$currentProjectId}";
						$ganttData[] = array(
							'id' => $stageTaskId,
							'name' => $stageName,
							'start' => null,
							'end' => null,
							'progress' => 0,
							'dependencies' => $projectTaskId,
							'custom_class' => 'task-level-2 task-stage'
						);
						$stagesAdded[$stageKey] = array(
							'id' => $stageTaskId,
							'index' => count($ganttData) - 1
						);
					} else {
						$stageTaskId = $stagesAdded[$stageKey]['id'];
					}
					
					// ============================================
					// NIVEL 3: Trabajo (orden_de_trabajo)
					// ============================================
					$jobTaskId = "job-{$workOrderId}";
					
					if (!isset($jobsAdded[$workOrderId])) {
						$ganttData[] = array(
							'id' => $jobTaskId,
							'name' => $workTitle . ' (TRABAJO)',
							'start' => null,
							'end' => null,
							'progress' => 0,
							'dependencies' => $stageTaskId,
							'custom_class' => 'task-level-3 task-job'
						);
						$jobsAdded[$workOrderId] = array(
							'id' => $jobTaskId,
							'index' => count($ganttData) - 1
						);
					} else {
						$jobTaskId = $jobsAdded[$workOrderId]['id'];
					}
					
					// ============================================
					// NIVEL 4: Tarea
					// ============================================
					$startDate = !empty($task['date_start']) ? $task['date_start'] : date('Y-m-d');
					$endDate = !empty($task['due_date']) ? $task['due_date'] : $startDate;
					
					// Calcular progreso basado en estado
					$progress = 0;
					$status = strtolower($task['eventstatus']);
					if ($status == 'completado' || $status == 'completed' || $status == 'held') {
						$progress = 100;
					} elseif ($status == 'en progreso' || $status == 'in progress') {
						$progress = 50;
					}
					
					$ganttData[] = array(
						'id' => $taskId,  // ID numérico para que funcione la actualización de fechas
						'name' => $task['subject'] . ' (Tarea)',
						'start' => $startDate,
						'end' => $endDate,
						'progress' => $progress,
						'dependencies' => $jobTaskId,
						'custom_class' => 'task-level-4 task-item',
						'relModule' => 'Calendar'  // Módulo relacionado para la actualización AJAX
					);
					
					// Actualizar fechas de padres (trabajo, etapa, proyecto)
					$taskStart = $startDate;
					$taskEnd = $endDate;
					
					// Actualizar trabajo
					$jobIndex = $jobsAdded[$workOrderId]['index'];
					if (empty($ganttData[$jobIndex]['start']) || $taskStart < $ganttData[$jobIndex]['start']) {
						$ganttData[$jobIndex]['start'] = $taskStart;
					}
					if (empty($ganttData[$jobIndex]['end']) || $taskEnd > $ganttData[$jobIndex]['end']) {
						$ganttData[$jobIndex]['end'] = $taskEnd;
					}
					
					// Actualizar etapa
					$stageIndex = $stagesAdded[$stageKey]['index'];
					if (empty($ganttData[$stageIndex]['start']) || $taskStart < $ganttData[$stageIndex]['start']) {
						$ganttData[$stageIndex]['start'] = $taskStart;
					}
					if (empty($ganttData[$stageIndex]['end']) || $taskEnd > $ganttData[$stageIndex]['end']) {
						$ganttData[$stageIndex]['end'] = $taskEnd;
					}
					
					// Actualizar proyecto
					$projectIndex = $projectsAdded[$currentProjectId]['index'];
					if (empty($ganttData[$projectIndex]['start']) || $taskStart < $ganttData[$projectIndex]['start']) {
						$ganttData[$projectIndex]['start'] = $taskStart;
					}
					if (empty($ganttData[$projectIndex]['end']) || $taskEnd > $ganttData[$projectIndex]['end']) {
						$ganttData[$projectIndex]['end'] = $taskEnd;
					}
				}
				
				// Renderizar template del Gantt
				$smarty = new vtigerCRM_Smarty();
				$smarty->assign('TASKS_GANTT', json_encode($ganttData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
				$smarty->assign('RELATED_MODULE', 'Calendar');
				$smarty->assign('idGanttDiagram', 'supplier-gantt-' . $supplierId);
				
				$htmlOutput = $smarty->fetch('GanttDiagram.tpl');
				
				header ('Access-Control-Allow-Origin: *');
				header ('HTTP/1.1 200 OK');
				header ('Content-Type: application/json; charset=utf-8');
				echo json_encode (array('error' => 'OK', 'html' => $htmlOutput, 'total' => count($ganttData)));
				exit ();
			}
			$toRecord = ($recordPerPage + $startRecord);
			if ($toRecord > $moduleInProgress[1]) {
				$toRecord = $moduleInProgress[1];
			}
			if ($calendarView == 'YES') {
				$calendarType = isset ($calendarType) ? $calendarType : null;
				$calenderViewData = ManagementModeHelper::fetchCalendarViewData ($function, $moduleInProgress[0], $calendarType);
				$objectDate = new DateTime();
				$today      = $objectDate->format ('Y-m-d');
				$objectDate = new DateTime();
				$objectDate->modify ('+1 day');
				$tomorrow   = $objectDate->format ('Y-m-d');
				$smarty->assign('AVAILABLE_ACTIVITY_TYPES', DataViewUtils::fetchActivityType ($adb, $moduleName, $mod_strings));
				$smarty->assign('AVAILABLE_EVENT_STATUSES', DataViewUtils::getAvailableEventStatuses($adb, $mod_strings));
				$smarty->assign('AVAILABLE_GROUPS', DataViewUtils::getAvailableGroups($adb));
				$smarty->assign('AVAILABLE_IMPORTANCE', DataViewUtils::getAvailableImportanceOfTasks());
				$smarty->assign ('AVAILABLE_MODULES', isset($availableModules) ? $availableModules : null);
				$smarty->assign('AVAILABLE_SYSTEM_USERS', UserManager::getInstance($adb, null)->fetchUsers());
				$smarty->assign('AVAILABLE_TASK_PRIORITIES', DataViewUtils::getTaskPriorities($adb));
				$smarty->assign('AVAILABLE_USERS', DataViewUtils::getAvailableUserAndAvatar($adb, $current_user));
				$smarty->assign('AVAILABLE_ESTIMATED_TIME_UNITS', getAvailableEstimatedTimeUnits());
				$smarty->assign('DEFAULT_ESTIMATED_TIME_UNIT', 'Hora');
				$smarty->assign('CATEGORIES',  DataViewUtils::getAvailableTaskCategories($adb, $current_user->id));
				$smarty->assign('CURRENT_USER_ID', $current_user->id);
				$smarty->assign('CURRENT_USER_NAME', $current_user->first_name . ' ' . $current_user->last_name);
				$smarty->assign('FLMODULE', $moduleName);
				$smarty->assign ('TODAY', $today);
				$smarty->assign ('TOMORROW', $tomorrow);
				$template = 'Home/ActionTabs/CalendarTabView.tpl';
			} else {
				// Usar template de solo filas para WORK_BY_SUPPLIER para evitar headers duplicados
				$template = (isset($useRowsOnlyTemplate) && $useRowsOnlyTemplate) 
					? 'Home/ActionTabs/rowsOnlyBlock.tpl' 
					: 'Home/ActionTabs/rowsTableBlock.tpl';
			}
			$startRecord = (empty($startRecord)) ? 1 : $startRecord;
			$smarty->assign ('ACTION_TABLE_HEADER', $tableHeader);
			$smarty->assign ('CALENDER_DATA', $calenderViewData);
			//$smarty->assign ('MODAL_DIMENSIONS', OperatingModesInterface::CALENDAR_MODAL_CONFIG);
			$smarty->assign ('MODULE', $moduleName);
			$smarty->assign ('ROWS', (!empty($moduleInProgress)) ? $moduleInProgress[0] : null);
			$smarty->assign ('ROWS_FIELDS', $rowFields);
			$smarty->assign ('URL_AVATARS', "{$_SESSION ['plat']}/user_images");
			$htmlRows = $smarty->fetch ($template);
			if ($calendarView == 'NO') {
				$paginator = ManagementModeHelper::configPaginator ($moduleInProgress[1], $workId);
				$paginator->currentPage = $page;
				$recordsLink = $paginator->createLinks ();
				if (empty($recordsLink)) {
					$recordsLink = '<li class="Pages"><a href="#"><strong>1</strong></a></li>';
				}
				$outputArray = array(
					'rows'      => $smarty->fetch ($template),
					'paginator' => $recordsLink,
					'records'   => "<span>Mostrando registros&nbsp;{$startRecord} - {$toRecord}&nbsp;de&nbsp;{$moduleInProgress[1]}</span>",
				);
			} else {
				$outputArray = $htmlRows;
			}
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => 'OK', 'html' => $outputArray));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage ()));
		}
	}  else if ($function == 'CALENDAR_CREATE_ACTIVITY' ) {
		try {
			// Crear nueva actividad en el calendario
			$activityData = array (
				'subject'          => PlatzillaUtils::purify ($_POST, 'subject'),
				'date_start'       => PlatzillaUtils::purify ($_POST, 'date_start'),
				'due_date'         => PlatzillaUtils::purify ($_POST, 'due_date'),
				'time_start'       => PlatzillaUtils::purify ($_POST, 'time_start', '00:00'),
				'time_end'         => PlatzillaUtils::purify ($_POST, 'time_end', '00:00'),
				'activitytype'     => PlatzillaUtils::purify ($_POST, 'activitytype', 'Task'),
				'assigned_user_id' => $current_user->id,
				'description'      => PlatzillaUtils::purify ($_POST, 'description', '')
			);
			$activity = new Activity();
			foreach ($activityData as $key => $value) {
				$activity->column_fields[$key] = $value;
			}
			$activity->save ('Calendar');
			
			$response = array(
				'error'       => 'OK',
				'activity_id' => $activity->id,
				'message'     => 'Actividad creada exitosamente'
			);
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode ($response);
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage()));
		}
	 } else if ($function == 'CALENDAR_RESIZE_EVENT') {
		try {
			// Actualizar duración de actividad (para resize)
			$activityId = PlatzillaUtils::purify ($_POST, 'activity_id');
			$newDueDate = PlatzillaUtils::purify ($_POST, 'new_due_date');
			$newTimeEnd = PlatzillaUtils::purify ($_POST, 'new_time_end', '00:00');
			
			$activity = new Activity();
			$activity->retrieve_entity_info ($activityId, 'Calendar');
			$activity->column_fields ['due_date'] = $newDueDate;
			$activity->column_fields ['time_end'] = $newTimeEnd;
			$activity->mode = 'edit';
			$activity->save ('Calendar');
			
			$response = array(
				'error'   => 'OK',
				'message' => 'Duración actualizada exitosamente'
			);
			
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode ($response);
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage()));
		}
	 } else if ($function == 'CALENDAR_UPDATE_DATE') {
		try {
			// Actualizar duración de actividad (para resize)
			$dataUpdate = array (
				'activityId'   => PlatzillaUtils::purify ($_POST, 'activity_id'),
				'calendarType' => PlatzillaUtils::purify ($_POST,'calendar_type'),
				'crmId'        => PlatzillaUtils::purify ($_POST, 'crmid_entity'),
				'dueDate'      => PlatzillaUtils::purify ($_POST, 'due_date'),
				'flModule'     => PlatzillaUtils::purify ($_POST, 'fl_module'),
				'newStartDate' => PlatzillaUtils::purify ($_POST, 'new_start_date'),
				'newDueDate'   => PlatzillaUtils::purify ($_POST, 'new_due_date'),
				'startDate'    => PlatzillaUtils::purify ($_POST, 'start_date'),
			);
			$numberingFormat = NumberHelper::getInstance ($adb);
			$numberingFormat->setUserNumberingFormat ($current_user, true);
			$message = DataViewUtils::updateCalendarDate ($adb, $current_user, $dataUpdate);
			$numberingFormat->setUserNumberingFormat ($current_user);
			$response = array ('error' => 'OK', 'message' => $message);
			
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode ($response);
			} catch (Exception $e) {
			$numberingFormat->setUserNumberingFormat ($current_user);
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage()));
		}
	 }
    exit();
