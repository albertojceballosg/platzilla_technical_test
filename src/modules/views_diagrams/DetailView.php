<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Data/ApplicationsManager.php');
	require_once ('include/platzilla/Data/BoxScoreManager.php');
	require_once ('include/platzilla/Data/GraphicManager.php');
	require_once ('include/platzilla/Managers/PlatformSubscriptionManager.php');
	require_once ('include/platzilla/Managers/UserManager.php');
	require_once ('include/platzilla/Utils/JSGraphicUtils.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/DataViewUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/GanttModuleViewUtils.class.php');
	require_once ('modules/Courses/lib/CoursesHelper.php');
	require_once ('modules/grid_view/lib/GridViewHelper.class.php');
	require_once ('modules/Home/lib/HomeUtils.class.php');
	require_once ('modules/News/lib/NewsUtils.php');
	require_once ('modules/notification_center/lib/NotificationHelper.class.php');
	require_once ('modules/notifications/lib/NotificationUtils.class.php');
	require_once ('modules/notifications/lib/NotificationPeriodUtils.class.php');
	require_once ('modules/operating_modes/lib/OperatingModesHelper.class.php');
	require_once ('modules/panelusuarios/lib/UsersHelper.class.php');
	require_once ('modules/preloaded_tasks/lib/PrecreatedTaskUtils.class.php');
	require_once ('modules/store/lib/StoreUtils.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');
	
	global $adb, $app_strings, $current_user, $current_module, $mod_strings, $site_URL, $theme;
	
	$moduleName      = PlatzillaUtils::purify ($_REQUEST, 'flmodule', null);
	$periodTask      = PlatzillaUtils::purify ($_REQUEST, 'periodtask', 'today');
	$inviteesId      = PlatzillaUtils::purify ($_POST,   'inviteesid', null);
	$customStartDate = PlatzillaUtils::purify ($_REQUEST, 'custom_start_date', null);
	$customEndDate   = PlatzillaUtils::purify ($_REQUEST, 'custom_end_date', null);
	$isInstance = !empty ($_SESSION ['platInstancia']);
	$masterAdb  = AdbManager::getInstance ()->getMasterAdb ();
	try {
		if ($isInstance) {
			if (!StoreUtils::isInstanceVerified ($_SESSION ['platInstancia'])) {
				throw new Exception ('Debes verificar tu cuenta', 400);
			}
			$psm          = PlatformSubscriptionManager::getInstance ($masterAdb);
			$subscription = $psm->fetchSubscription ($_SESSION ['platInstancia']);
			if ((empty ($subscription)) || ($subscription->getStatus () == PlatformSubscription::STATUS_INACTIVE)) {
				throw new Exception ('Tu suscripción se encuentra inactiva', 403);
			}
			
			$canCreateRecords = true;
		} else {
			$canCreateRecords = true;
		}
		
		$tasksView = DataViewUtils::fetchView ($adb, 'Calendar', 'ALL');
		//ALL TASK
		if (empty ($tasksView)) {
			throw new Exception ('La vista solicitada no se encuentra registrada');
		}
		
		// Si se proporcionaron fechas personalizadas, usarlas en lugar del período predefinido
		if (!empty($customStartDate) && !empty($customEndDate)) {
			// Convertir formato dd/mm/yyyy a yyyy-mm-dd
			$startParts = explode('/', $customStartDate);
			$endParts   = explode('/', $customEndDate);
			
			if (count($startParts) == 3 && count($endParts) == 3) {
				$periodDates = array(
					'startdate' => $startParts[2] . '-' . $startParts[1] . '-' . $startParts[0],
					'enddate'   => $endParts[2] . '-' . $endParts[1] . '-' . $endParts[0]
				);
				$periodTask = 'custom'; // Marcar como personalizado
			} else {
				$periodDates = NotificationPeriodUtils::getStandarFiltersStartAndEndDate ($periodTask);
			}
		} else {
			$periodDates = NotificationPeriodUtils::getStandarFiltersStartAndEndDate ($periodTask);
		}
		$queryGenerator   = new QueryGenerator ('Calendar', $current_user);
		$queryGenerator->initForCustomViewById ($tasksView->getId());
		$queryGenerator->getQuery ();
		if (empty ($periodTask)) {
			$conditionalWhere     = $queryGenerator->getConditionalWhere ();
		}
		
		$tasksViewPermissions = DataViewUtils::fetchViewPermissions ($adb, $tasksView, $current_user);
		if ((!is_array ($tasksViewPermissions)) || (!in_array (DataViewUtils::PERMISSION_CAN_USE, $tasksViewPermissions))) {
			throw new Exception ('Acceso denegado');
		}
		$users             = (empty ($inviteesId)) ? array($current_user->id) : explode (',', $inviteesId);
		$tasksData         = DataViewUtils::fetchTaskToMatrix ($adb, $periodDates, $users);
		$taskCategories    = DataViewUtils::getAvailableTaskCategories ($adb, $current_user->id);
		$taskCategoriesIds =  array_values ($taskCategories);
		$activitiesRecords = array();
		$taskToGantt       = array ();
		$ganttHierarchy    = array ();
		$jobsWithProject   = array(); // cache: crmid_job => id de proyecto asociado (0 si no tiene)
		$projectsWithTasks = array(); // proyectos que tienen al menos una tarea en el periodo
		$kanbanData        = array ();
		$kanbanBlocks      = array (
			'Planned'   => 'Planeado',
			'Postponed' => 'Pospuesto',
			'Not Held'  => 'Pendiente',
			'Held'      => 'Realizado',
		);
		$smarty = new vtigerCRM_Smarty ();
		$orphanTasks       = array(); // Tareas cuyo registro relacionado fue eliminado
		$recordExistsCache = array(); // Cache: crmid => true/false (existe o no)
		$recordNameCache   = array(); // Cache: crmid => nombre obtenido de entityname
		
		// =====================================================================
		// CASO ESPECIAL: Gantt para ListView de orden_de_trabajo
		// Usa jerarquía: Proyecto (L1) -> Etapa (L2) -> Trabajo (L3) -> Tareas (L4)
		// =====================================================================
		if ($moduleName == 'orden_de_trabajo') {
			
			// Obtener IDs de trabajos con fechas en el período
			$workOrderQuery = "SELECT odt.orden_de_trabajoid
				FROM vtiger_orden_de_trabajo odt
				INNER JOIN vtiger_crmentity ce ON ce.crmid = odt.orden_de_trabajoid AND ce.deleted = 0
				WHERE (
					(odt.fecha_prevista BETWEEN ? AND ?) OR
					(odt.fecha_estim_fin BETWEEN ? AND ?) OR
					(odt.fecha_prevista <= ? AND odt.fecha_estim_fin >= ?)
				)
				ORDER BY odt.fecha_prevista ASC";
			
			$workOrderParams = array(
				$periodDates['startdate'], $periodDates['enddate'],
				$periodDates['startdate'], $periodDates['enddate'],
				$periodDates['startdate'], $periodDates['enddate']
			);
			
			$workOrderResult = $adb->pquery($workOrderQuery, $workOrderParams);
			$workOrderIds = array();
			
			while ($row = $adb->fetchByAssoc($workOrderResult)) {
				$workOrderIds[] = intval($row['orden_de_trabajoid']);
			}
			
			
			// Construir Gantt usando el método especializado
			if (!empty($workOrderIds)) {
				$taskToGantt = GanttModuleViewUtils::buildWorkOrdersListViewGantt($adb, $workOrderIds, $current_user);
			} else {
			}
			
			// Saltar el procesamiento normal y ir directo a renderizar
			$smarty->assign('THEME', $theme);
			$smarty->assign('PERIOD_TASK', $periodTask);
			$smarty->assign('PERIOD_START', $periodDates['startdate']);
			$smarty->assign('PERIOD_END', $periodDates['enddate']);
			$smarty->assign('GANTT_VIEW_NAME', 'Trabajos planificados');
			$smarty->assign('GANTT_TASKS', $taskToGantt);
			$smarty->assign('GANTT_TASKS_JSON', json_encode($taskToGantt, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
			$smarty->assign('TOTAL_WORK_ORDERS', count($workOrderIds));
			$smarty->assign('FL_MODULE', $moduleName);
			
			$smarty->display('centaurus/GanttDiagram.tpl');
			return; // Terminar aquí para orden_de_trabajo
		}
		// =====================================================================
		
		/**
		 * Función helper para validar existencia de registro y obtener su nombre
		 * usando vtiger_entityname (fieldidentifier o fieldname).
		 *
		 * @param PearDatabase $adb
		 * @param int $recordId
		 * @param string $moduleName
		 * @param array &$existsCache
		 * @param array &$nameCache
		 * @return array ['exists' => bool, 'name' => string|null]
		 */
		$getRecordInfo = function ($adb, $recordId, $moduleName, &$existsCache, &$nameCache) {
			// Si no hay ID válido, no existe
			if (empty($recordId) || $recordId <= 0) {
				return array('exists' => false, 'name' => null);
			}
			
			// Revisar cache de existencia
			if (isset($existsCache[$recordId])) {
				return array(
					'exists' => $existsCache[$recordId],
					'name'   => isset($nameCache[$recordId]) ? $nameCache[$recordId] : null
				);
			}
			
			// 1. Validar que el registro exista en vtiger_crmentity (deleted = 0)
			$checkResult = $adb->pquery(
				'SELECT crmid FROM vtiger_crmentity WHERE crmid = ? AND deleted = 0 LIMIT 1',
				array($recordId)
			);
			if (!$checkResult || $adb->num_rows($checkResult) == 0) {
				$existsCache[$recordId] = false;
				return array('exists' => false, 'name' => null);
			}
			
			// 2. Obtener definición de entityname para el módulo
			$entityResult = $adb->pquery(
				'SELECT tablename, fieldname, entityidcolumn, fieldidentifier 
				 FROM vtiger_entityname 
				 WHERE modulename = ? LIMIT 1',
				array($moduleName)
			);
			if (!$entityResult || $adb->num_rows($entityResult) == 0) {
				// No hay definición de entityname, marcar como no existente
				$existsCache[$recordId] = false;
				$nameCache[$recordId] = null;
				return array('exists' => false, 'name' => null);
			}
			$entityRow = $adb->fetchByAssoc($entityResult, -1, false);
			
			$tableName      = $entityRow['tablename'];
			$entityIdColumn = $entityRow['entityidcolumn'];
			// Usar fieldidentifier si tiene valor, si no, usar fieldname
			$fieldToUse = !empty($entityRow['fieldidentifier']) 
				? $entityRow['fieldidentifier'] 
				: $entityRow['fieldname'];
			
			if (empty($tableName) || empty($entityIdColumn) || empty($fieldToUse)) {
				$existsCache[$recordId] = false;
				$nameCache[$recordId] = null;
				return array('exists' => false, 'name' => null);
			}
			
			// 3. Verificar que el registro exista en la tabla del módulo Y obtener su nombre
			// fieldToUse puede tener múltiples campos separados por coma (ej: "firstname,lastname")
			$fields = array_map('trim', explode(',', $fieldToUse));
			$selectFields = implode(', ', $fields);
			
			$nameResult = $adb->pquery(
				"SELECT {$selectFields} FROM {$tableName} WHERE {$entityIdColumn} = ? LIMIT 1",
				array($recordId)
			);
			
			// Si no hay fila en la tabla del módulo, el registro está incompleto/huérfano
			if (!$nameResult || $adb->num_rows($nameResult) == 0) {
				$existsCache[$recordId] = false;
				$nameCache[$recordId] = null;
				return array('exists' => false, 'name' => null);
			}
			
			$nameRow = $adb->fetchByAssoc($nameResult, -1, false);
			
			// Concatenar valores de los campos (si hay varios)
			$nameParts = array();
			foreach ($fields as $field) {
				if (!empty($nameRow[$field])) {
					$nameParts[] = $nameRow[$field];
				}
			}
			$recordName = !empty($nameParts) ? implode(' ', $nameParts) : null;
			
			$existsCache[$recordId] = true;
			$nameCache[$recordId] = $recordName;
			
			return array('exists' => true, 'name' => $recordName);
		};
		
		// Construir jerarquía flexible: Registro -> (Categoría interna) -> Tareas
		// Para módulos distintos de 'proyectos', la categoría se usa solo como agrupador lógico,
		// pero no se dibuja como barra independiente en el Gantt.
		foreach ($tasksData as $taskData) {
			$moduleName  = $taskData->getRelatedModule ();
			$recordId    = $taskData->getRelatedId ();
			$moduleLabel = ($moduleName != 'Tarea') ? getTabIdLabelByName ($moduleName) : 'Tareas sin relacionar';
			
			// Si es un trabajo que pertenece a un proyecto, no lo mostramos como registro independiente
			// en el Home: su información se visualizará dentro del Gantt del proyecto.
			if ($moduleName == 'orden_de_trabajo' && !empty ($recordId)) {
				if (!isset ($jobsWithProject[$recordId])) {
					$jobsWithProject[$recordId] = 0;
					$jobResult = $adb->pquery ('SELECT crmid FROM vtiger_project_works WHERE crmid_job = ? LIMIT 1', array ($recordId));
					if ($jobResult && $adb->num_rows ($jobResult) > 0) {
						$jobRow = $adb->fetchByAssoc ($jobResult, -1, false);
						$jobsWithProject[$recordId] = intval ($jobRow['crmid']); // ID del proyecto
					}
				}
				if (!empty ($jobsWithProject[$recordId])) {
					$projectId = $jobsWithProject[$recordId];
					$projectsWithTasks[$projectId] = true;
					continue;
				}
			}
			
			// Validar existencia del registro relacionado y obtener su nombre
			$isOrphanTask = false;
			$recordName   = null;
			
			// También validar que la propia tarea (Calendar) exista
			$activityId = $taskData->getActivityId();
			$taskExistsResult = $adb->pquery(
				'SELECT crmid FROM vtiger_crmentity WHERE crmid = ? AND deleted = 0 LIMIT 1',
				array($activityId)
			);
			if (!$taskExistsResult || $adb->num_rows($taskExistsResult) == 0) {
				// La tarea fue eliminada, no mostrarla en absoluto
				continue;
			}
			
			if (!empty($recordId) && $recordId > 0 && $moduleName != 'Tarea' && $moduleName != 'Calendar') {
				$recordInfo = $getRecordInfo($adb, $recordId, $moduleName, $recordExistsCache, $recordNameCache);
				
				if (!$recordInfo['exists']) {
					// El registro fue eliminado -> tarea huérfana
					$isOrphanTask = true;
					$orphanTasks[] = $taskData;
					continue; // No agregar a la jerarquía normal, se procesará aparte
				}
				
				// Registro existe, usar nombre obtenido o fallback
				$recordName = $recordInfo['name'];
				if (empty($recordName)) {
					$recordName = $moduleLabel . ' #' . $recordId;
				}
			} else {
				// Tarea sin registro relacionado (related_id = 0 o módulo Tarea)
				$recordName = 'Tareas sin relacionar';
			}
			
			// Determinar etiqueta según el tipo de módulo
			$moduleLabelParenthesis = '';
			if ($moduleName == 'proyectos') {
				$moduleLabelParenthesis = 'PROYECTO';
			} elseif ($moduleName == 'orden_de_trabajo') {
				$moduleLabelParenthesis = 'TRABAJOS';
			} elseif ($moduleName == 'Calendar' || $moduleName == 'Tarea') {
				$moduleLabelParenthesis = '';
			} else {
				$moduleLabelParenthesis = strtoupper($moduleLabel);
			}
			
			// Si el propio registro es un proyecto, marcarlo también
			if ($moduleName == 'proyectos' && !empty ($recordId)) {
				$projectsWithTasks[$recordId] = true;
			}
			
			$categoryId = $taskData->getGroupId ();
			if (!in_array ($categoryId, $taskCategoriesIds)) {
				$category = DataViewUtils::getCategoryByRelatedTo ($adb, $categoryId, $moduleName);
				$categoryName = (!empty ($category)) ? $category[$categoryId] : 'Tareas/Acciones';
			} else {
				$categoryName = $taskCategories[$categoryId];
			}
			
			// Crear claves únicas para cada nivel lógico
			$recordKey   = "record-{$moduleName}-{$recordId}";
			$categoryKey = "category-{$categoryId}";
			
			// Inicializar estructura si no existe
			if (!isset ($ganttHierarchy[$recordKey])) {
				$ganttHierarchy[$recordKey] = array (
					'name' => $recordName,
					'moduleName' => $moduleName,
					'moduleLabelParenthesis' => $moduleLabelParenthesis,
					'recordId' => $recordId,
					'categories' => array ()
				);
			}
			
			if (!isset ($ganttHierarchy[$recordKey]['categories'][$categoryKey])) {
				$ganttHierarchy[$recordKey]['categories'][$categoryKey] = array (
					'name' => $categoryName,
					'tasks' => array ()
				);
			}
			
			// Agregar tarea al grupo lógico
			$ganttHierarchy[$recordKey]['categories'][$categoryKey]['tasks'][] = $taskData;
		}
		
		// Construir Gantt para todos los proyectos que tienen al menos una tarea en el periodo
		if (!empty ($projectsWithTasks)) {
			$projectIds = array_keys ($projectsWithTasks);
			$viewConfig = array (
				'hierarchy_config' => array(),
				'display_config'   => array(),
			);
			try {
				$projectTasks = GanttModuleViewUtils::buildGanttData(
					$adb,
					'proyectos',
					$projectIds,
					$viewConfig,
					$current_user
				);
				if (!empty ($projectTasks)) {
					foreach ($projectTasks as $projTask) {
						$taskToGantt[] = $projTask;
					}
				}
			} catch (Exception $e) {
				// Error construyendo Gantt para proyectos
			}
		}
		
		// Convertir jerarquía a estructura plana para Gantt (módulos distintos de 'proyectos')
		foreach ($ganttHierarchy as $recordKey => $recordData) {
			$recordModuleName = $recordData['moduleName'];
			
			// Los proyectos ya se han añadido arriba
			if ($recordModuleName == 'proyectos') {
				continue;
			}
			
			// Caso general: otros módulos -> Registro (nivel 1) + Tareas (nivel 4)
			// NIVEL 1: Registro (Oportunidad, Caso, Trabajo sin proyecto, etc.)
			$recordTaskId = "task-record-" . uniqid ();
			$recordTask = new stdClass ();
			$recordTask->id = $recordTaskId;
			if (!empty($recordData['moduleLabelParenthesis'])) {
				$recordTask->name = $recordData['name'] . ' (' . $recordData['moduleLabelParenthesis'] . ')';
			} else {
				$recordTask->name = $recordData['name'];
			}
			$recordTask->custom_class = 'task-level-1 task-record';
			$recordTask->dependencies = '';
			$recordTask->level = 1;
			$recordTask->start = null;
			$recordTask->end = null;
			$recordTask->progress = 0;
			$recordTask->totalGroup = 0;
			$taskToGantt[] = $recordTask;
			$recordIndex = count ($taskToGantt) - 1;
			
			// NIVELES INTERNOS: recorrer categorías lógicas, pero sólo crear barras de tareas (nivel 4)
			foreach ($recordData['categories'] as $categoryKey => $categoryData) {
				foreach ($categoryData['tasks'] as $taskData) {
					// Excluir tareas tipo "Job" del Gantt
					if ($taskData->getActivityType() == 'Job') {
						continue;
					}
					
					$task = new stdClass ();
					$task->id = $taskData->getActivityId ();
					$task->name = $taskData->getSubject () . ' (Tarea/Acción)';
					$task->start = $taskData->getDateInit ();
					$task->end = $taskData->getDateEnd ();
					$task->progress = $taskData->getProgress ();
					$task->dependencies = $recordTaskId;                // cuelga directamente del registro
					$task->custom_class = 'task-level-4 task-item';      // mismo estilo que tareas de trabajos
					$task->level = 4;
					$task->relModule = $taskData->getRelatedModule ();
					$task->actProgress = $taskData->getProgress ();
					$taskToGantt[] = $task;
					
					// Actualizar fechas y progreso del registro a partir de las tareas
					$taskStartDate = date_create ($taskData->getDateInit ());
					$taskDueDate   = date_create ($taskData->getDateEnd ());
					
					if (!empty ($taskData->getDateInit ())) {
						if (empty ($taskToGantt[$recordIndex]->start) || 
							$taskStartDate < date_create ($taskToGantt[$recordIndex]->start)) {
							$taskToGantt[$recordIndex]->start = $taskData->getDateInit ();
						}
					}
					if (!empty ($taskData->getDateEnd ())) {
						if (empty ($taskToGantt[$recordIndex]->end) || 
							$taskDueDate > date_create ($taskToGantt[$recordIndex]->end)) {
							$taskToGantt[$recordIndex]->end = $taskData->getDateEnd ();
						}
					}
					$taskToGantt[$recordIndex]->progress += $taskData->getProgress ();
					$taskToGantt[$recordIndex]->totalGroup++;
				}
			}
			
			// Calcular progreso promedio de registro
			if ($taskToGantt[$recordIndex]->totalGroup > 0) {
				$taskToGantt[$recordIndex]->progress = 
					$taskToGantt[$recordIndex]->progress / $taskToGantt[$recordIndex]->totalGroup;
			}
		}
		
		// Procesar tareas huérfanas PRIMERO (registro relacionado eliminado)
		// Se muestran al inicio del Gantt, con clase especial para texto rojo
		$orphanGanttTasks = array();
		if (!empty($orphanTasks)) {
			// Crear un grupo contenedor para tareas huérfanas
			$orphanGroupId = "orphan-tasks-" . uniqid();
			$orphanGroup = new stdClass();
			$orphanGroup->id = $orphanGroupId;
			$orphanGroup->name = '⚠ Tareas/Acciones con registro padre eliminado';
			$orphanGroup->custom_class = 'task-level-1 task-record task-orphan-group';
			$orphanGroup->dependencies = '';
			$orphanGroup->level = 1;
			$orphanGroup->start = null;
			$orphanGroup->end = null;
			$orphanGroup->progress = 0;
			$orphanGroup->totalGroup = 0;
			$orphanGanttTasks[] = $orphanGroup;
			$orphanGroupIndex = 0;
			
			foreach ($orphanTasks as $taskData) {
				// Excluir tareas tipo "Job" del Gantt
				if ($taskData->getActivityType() == 'Job') {
					continue;
				}
				
				$task = new stdClass();
				$task->id = $taskData->getActivityId();
				$task->name = $taskData->getSubject() . ' (Registro eliminado)';
				$task->start = $taskData->getDateInit();
				$task->end = $taskData->getDateEnd();
				$task->progress = $taskData->getProgress();
				$task->dependencies = $orphanGroupId;
				$task->custom_class = 'task-level-4 task-item task-orphan'; // Clase especial para estilo rojo
				$task->level = 4;
				$task->relModule = $taskData->getRelatedModule();
				$task->actProgress = $taskData->getProgress();
				$task->isOrphan = true; // Flag para identificar tareas huérfanas
				$orphanGanttTasks[] = $task;
				
				// Actualizar fechas del grupo huérfano
				$taskStartDate = date_create($taskData->getDateInit());
				$taskDueDate   = date_create($taskData->getDateEnd());
				
				if (!empty($taskData->getDateInit())) {
					if (empty($orphanGanttTasks[$orphanGroupIndex]->start) || 
						$taskStartDate < date_create($orphanGanttTasks[$orphanGroupIndex]->start)) {
						$orphanGanttTasks[$orphanGroupIndex]->start = $taskData->getDateInit();
					}
				}
				if (!empty($taskData->getDateEnd())) {
					if (empty($orphanGanttTasks[$orphanGroupIndex]->end) || 
						$taskDueDate > date_create($orphanGanttTasks[$orphanGroupIndex]->end)) {
						$orphanGanttTasks[$orphanGroupIndex]->end = $taskData->getDateEnd();
					}
				}
				$orphanGanttTasks[$orphanGroupIndex]->progress += $taskData->getProgress();
				$orphanGanttTasks[$orphanGroupIndex]->totalGroup++;
			}
			
			// Calcular progreso promedio del grupo huérfano
			if ($orphanGanttTasks[$orphanGroupIndex]->totalGroup > 0) {
				$orphanGanttTasks[$orphanGroupIndex]->progress = 
					$orphanGanttTasks[$orphanGroupIndex]->progress / $orphanGanttTasks[$orphanGroupIndex]->totalGroup;
			}
		}
		
		// Construir datos para Kanban (mantener lógica original)
		foreach ($tasksData as $taskData) {
			$taskStarDate = date_create ($taskData->getDateInit ());
			$taskDueDate  = date_create ($taskData->getDateEnd ());
			
			$eventStatus = (!empty ($taskData->getStatus ())) ? $taskData->getStatus () : 'Planned';
			$smarty->assign ('assignedUser', $taskData->getUserName ());
			$smarty->assign ('relatedModule', $taskData->getRelatedModule ());
			$smarty->assign ('title', $taskData->getSubject ());
			$smarty->assign ('userAvatar', (empty($taskData->getUserAvatar ())) ? '/Image/avatar/png/man.png' : "{$_SESSION ['plat']}/user_images/{$taskData->getUserAvatar ()}");
			
			$kanbanTitle  = $smarty->fetch ('utils/kanbanTitle.tpl');
			
			$smarty->assign ('dateStart', date_format ($taskStarDate, 'd-m-Y g:ia'));
			$smarty->assign ('dueDate',date_format ($taskDueDate, 'd-m-Y g:ia'));
			$smarty->assign ('progress', $taskData->getProgress ());
			$kanbanFooter      = $smarty->fetch ('utils/kanbanFooter.tpl');
			$kanban            = new stdClass ();
			$kanban->id        = $taskData->getActivityId ();
			$kanban->title     = $kanbanTitle;
			$kanban->helper    = $taskData->getRelatedModule ();
			$kanban->block     = $kanbanBlocks[ $eventStatus ];
			$kanban->link      =  $taskData->getActivityId ();
			$kanban->link_text = $taskData->getRelatedModule ();
			$kanban->footer    = $kanbanFooter;
			$kanbanData[]      = $kanban;
		}
		$selectedUsers = array ();
		$availableUsers = DataViewUtils::getAvailableUserAndAvatar ($adb, $current_user);
		if (count ($availableUsers) && count ($users)) {
			foreach ($users as $userId) {
				$selectedUsers [] = $availableUsers[ trim ($userId) ]['name'];
			}
		}
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('AVAILABLE_GROUPS', DataViewUtils::getAvailableGroups($adb));
		$smarty->assign ('AVAILABLE_MODULES',(isset($tabModules)) ? $tabModules : null);
		$smarty->assign ('AVAILABLE_SYSTEM_USERS', UserManager::getInstance ($adb, null)->fetchUsers ());
		$smarty->assign ('AVAILABLE_TASK_PRIORITIES', DataViewUtils::getTaskPriorities ($adb));
		$smarty->assign ('AVAILABLE_USERS', $availableUsers);
		$smarty->assign ('CURRENT_USER_ID', $current_user->id);
		$smarty->assign ('FLMODULE', '');
		$smarty->assign ('KANBAN_BLOCKS', (count ($kanbanData)) ? json_encode ($kanbanData) : null);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('PERIOD_DATES', NotificationPeriodUtils::getAvailablePeriods ());
		$smarty->assign ('PERIOD_SELECTED', $periodTask);
		$smarty->assign ('CUSTOM_START_DATE', $customStartDate);
		$smarty->assign ('CUSTOM_END_DATE', $customEndDate);
		$smarty->assign ('RELATED_MODULE', '');
		// Combinar: tareas huérfanas primero (arriba) + resto de tareas
		$finalGanttTasks = array_merge($orphanGanttTasks, $taskToGantt);
		$smarty->assign ('TASKS_GANTT', (count ($finalGanttTasks) ? json_encode ($finalGanttTasks, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) : null));
		$smarty->assign ('USERS', $users);
		$smarty->assign ('USERS_ID_LIST', (!empty ($users)) ? join (',',$users) : null);
		$smarty->assign ('USERS_NAME', (count ($selectedUsers)) ? join (', ', $selectedUsers) : null);
		$smarty->display  ('ViewsDiagrams.tpl');
	} catch (Exception $e) {
		header ('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode(array('error' => $e->getMessage()));
	}
