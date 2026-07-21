<?php
	require_once ('Smarty_setup.php');
	require_once ('include/fields/DateTimeField.php');
	require_once ('include/fields/CurrencyField.php');
	require_once ('include/QueryGenerator/QueryGenerator.php');
	require_once ('include/platzilla/Data/ActivityReportManager.php');
	require_once ('include/platzilla/Managers/UserManager.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/DataViewUtils.php');
	require_once ('include/utils/NumberHelper.class.php');
	require_once ('include/database/PearDatabase_Fix.php');
	require_once ('modules/Calendar/Activity.php');
	require_once ('modules/Settings/lib/HowToHelper.class.php');
	require_once ('modules/etapas_proyecto/etapas_proyecto.php');
	require_once ('modules/grid_view/lib/GridViewHelper.class.php');
	require_once ('modules/preloaded_tasks/lib/PrecreatedTaskUtils.class.php');
	class taskToWork {
		
		const TASK_RELATED_TO = 'orden_de_trabajo';
		
		const COPY_ACTION_PLAN = 'AjaxModelActionPlanUtils';
		
		const ORDER_CLOSING_STATUS = array ('Terminado', 'Cancelado');
		
		/** @var PearDatabase */
		protected $masterAdb;
		
		/** @var PearDatabase */
		protected $adb;
		
		/**
		 * taskToWork constructor.
		 * @param PearDatabase $adb
		 */
		public function __construct ($adb) {
			$this->masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			$this->adb       = $adb;
		}
		
		/**
		 * @param integer $crmId
		 * @param integer $userId
		 * @param string $workTitle
		 *
		 * @throws Exception
		 * @throws WebServiceException
		 */
		private function createDefaultTask ($crmId, $userId, $workTitle) {
			$jobEntity = CRMEntity::getInstance (self::TASK_RELATED_TO);
			$jobEntity->retrieve_entity_info ($crmId, self::TASK_RELATED_TO);
			$jobFields        = $jobEntity->column_fields;
			$startDate        = $this->resolveJobDate ($jobFields, array ('fecha_de_inicio', 'fecha_prevista', 'estimated_due_date'));
			$dueDate          = $this->resolveJobDate ($jobFields, array ('fecha_estim_fin', 'estimated_due_date', 'fecha_prevista'));
			$startDate        = !empty ($startDate) ? $startDate : date ('Y-m-d');
			if (empty ($dueDate)) {
				$dueDateObj = new DateTime ($startDate);
				$dueDateObj->modify ('+1 day');
				$dueDate = $dueDateObj->format ('Y-m-d');
			}
			$workTitle        = substr ($workTitle, 0, 80);
			$description      = !empty ($jobFields ['descripcion']) ? $jobFields ['descripcion'] : $workTitle;
			$estimatedTime    = isset ($jobFields ['numero_unidades_planificadas']) ? (float) $jobFields ['numero_unidades_planificadas'] : 0;
			$estimatedTime    = ($estimatedTime > 0) ? $estimatedTime : 0.01;
			$progress         = isset ($jobFields ['overall_progress_perc']) ? (float) $jobFields ['overall_progress_perc'] : 0;
			$assignedOwner    = !empty ($jobFields ['assigned_user_id']) ? $jobFields ['assigned_user_id'] : $userId;
			$dataTask         = array (
				'activityId'    => null,
				'activityType'  => 'Activity',
				'categoryId'    => 10,
				'dateStart'     => $startDate,
				'description'   => $description,
				'dueDate'       => $dueDate,
				'estimatedTime' => $estimatedTime,
				'eventStatus'   => 'Planned',
				'importance'    => 'HIGH',
				'mode'          => 'create',
				'ownerUserId'   => $assignedOwner,
				'plannedTask'   => 'PLANNED_AND_RECORDED',
				'priority'      => 'Bajo',
				'progress'      => $progress,
				'related_id'    => $crmId,
				'related_to'    => self::TASK_RELATED_TO,
				'showMatrix'    => 'YES',
				'subject'       => $workTitle,
			);
			$this->saveActivity ($dataTask);
			$this->updateInitDateToWork ($startDate, $crmId);
			unset ($jobEntity);
		}

		private function resolveJobDate (array $jobFields, array $fieldCandidates) {
			foreach ($fieldCandidates as $fieldName) {
				if (!isset ($jobFields [ $fieldName ])) {
					continue;
				}
				$value = $jobFields [ $fieldName ];
				if (!empty ($value) && $value !== '0000-00-00') {
					return substr ($value, 0, 10);
				}
			}
			return null;
		}

		private function hasRelatedTasks ($crmId) {
			$result = $this->adb->pquery (
				'SELECT act.activityid, act.activitytype, act.subject, act.eventstatus, act.date_start, act.due_date
				 FROM vtiger_seactivityrel rel
				 INNER JOIN vtiger_activity act ON act.activityid = rel.activityid
				 INNER JOIN vtiger_crmentity crm ON crm.crmid = rel.activityid AND crm.deleted = 0
				 WHERE rel.crmid = ? AND act.activitytype <> ?',
				array ($crmId, 'Job')
			);
			$rows    = ($result) ? $this->adb->num_rows ($result) : 0;
			$hasTasks = ($rows > 0);
			DatabaseUtils::closeResult ($result);
			return $hasTasks;
		}

		private function ensureDefaultTask ($crmId, $user, $workTitle) {
			if (empty ($crmId) || $this->hasRelatedTasks ($crmId)) {
				return;
			}
			$this->createDefaultTask ($crmId, $user->id, $workTitle);
		}
		
		/**
		 * @param integer $crmId
		 */
		private function delRelatedTask ($crmId) {
			if (empty ($crmId)) {
				return;
			}
			
			// Obtener tareas antes de eliminar para auditoría
			$tasksBefore = $this->getTasksForAudit($crmId);
			
			$globalTaskId    = ActivityReportManager::getInstance ($this->adb)->getTaskFromJobId ($crmId);
			$whereGlobalTask = (empty ($globalTaskId)) ? '' : "AND activityid != {$globalTaskId} ";
			$this->adb->pquery ("DELETE FROM vtiger_seactivityrel WHERE crmid=? {$whereGlobalTask}", array ($crmId));
			DatabaseUtils::closeResult ($result);
			
			// Registrar auditoría de eliminación masiva
			if (!empty($tasksBefore)) {
				$this->auditTasksOperation($crmId, 'ELIMINACIÓN MASIVA', $tasksBefore, array());
			}
		}
		
		/**
		 * @param integer $crmId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		private function fetchRelatedTask ($crmId, $currentUser) {
			$numberingHelper = NumberHelper::getInstance ($this->adb, $currentUser);

			$result = $this->adb->pquery ('SELECT activityid FROM vtiger_seactivityrel WHERE crmid=?', array ($crmId));
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$sql = $this->adb->pquery (
						'SELECT
								act.activityid,
								act.activitytype,
								act.categoryid,
								act.date_start,
								act.due_date,
								act.estimated_time,
								act.estimated_time_unit,
								act.estimated_cost,
								act.duration_hours,
								act.progress,
								act.progress_weighting_factor,
								act.eventstatus,
								act.subject,
								act.combined_condition,
								crm.smownerid,
								crm.description,
								srel.proveedoresid,
								prov.alias AS supplier_name,
								COALESCE(rep.reported_hours, 0) AS reported_hours,
								COALESCE(rep.reported_cost, 0) AS reported_cost
							  FROM
							  	vtiger_activity act
							  INNER JOIN vtiger_crmentity crm ON crm.crmid = act.activityid AND crm.deleted=0
							  LEFT JOIN vtiger_supplieractivityrel srel ON srel.activityid = act.activityid
							  LEFT JOIN vtiger_proveedores prov ON prov.proveedoresid = srel.proveedoresid
							  LEFT JOIN (
							  	SELECT activityid, SUM(duration_time) AS reported_hours, SUM(actual_cost) AS reported_cost
							  	FROM vtiger_activity_report
							  	WHERE deleted = 0
							  	GROUP BY activityid
							  ) rep ON rep.activityid = act.activityid
							  WHERE
							  	crm.setype=? AND
							  	act.activityid=? 
							  ORDER BY act.activityid ASC',
						array ('Calendar', $row ['activityid'])
					);
					if ($this->adb->num_rows ($sql) > 0) {
						while ($taskRow = $this->adb->fetchByAssoc ($sql, -1, false)) {	
							$estimatedTime = $numberingHelper->setNumberFormat ($taskRow ['estimated_time'], 'estimated_time');
							$estimatedCost = $numberingHelper->setNumberFormat ($taskRow ['estimated_cost'], 'estimated_cost');
							$progress      = $numberingHelper->setNumberFormat ($taskRow ['progress'], 'progress');
							$durationHours = $numberingHelper->setNumberFormat ($taskRow ['duration_hours']);
							
							// Convertir fechas al formato del usuario
							$startDateDisplay = '';
							$dueDateDisplay = '';
							if (!empty($taskRow['date_start']) && $taskRow['date_start'] !== '0000-00-00') {
								$startDateDisplay = DateTimeField::convertToUserFormat($taskRow['date_start'], $currentUser);
							}
							if (!empty($taskRow['due_date']) && $taskRow['due_date'] !== '0000-00-00') {
								$dueDateDisplay = DateTimeField::convertToUserFormat($taskRow['due_date'], $currentUser);
							}
							
							$records[] = array (
								'taskId'	     => $taskRow ['activityid'],
								'types'	         => $taskRow ['activitytype'],
								'stage'          => $taskRow ['categoryid'],
								'start_date'     => $startDateDisplay,
								'due_date'	     => $dueDateDisplay,
								'duration'	     => $estimatedTime,
								'estimated_time_unit' => !empty($taskRow['estimated_time_unit']) ? $taskRow['estimated_time_unit'] : 'Hora',
								'estimated_cost' => $estimatedCost,
								'progress'       => $progress,
								'progress_weighting_factor' => $taskRow ['progress_weighting_factor'],
								'hours'		     => $durationHours,
								'status'	     => $taskRow ['eventstatus'],
								'task_title'     => $taskRow ['subject'],
								'task'           => $taskRow ['description'],
								'assigned'       => $taskRow ['smownerid'],
								'estimated_time' => $taskRow ['estimated_time'],
								'estimated_cost_raw' => $taskRow ['estimated_cost'],
								'combined_condition' => $taskRow ['combined_condition'],
								'howToId'        => HowToHelper::hasHowTo ($this->adb, 'Calendar', $taskRow ['activityid'], 'DetailView_Task'),
								'supplierId'     => $taskRow ['proveedoresid'],
								'supplierName'   => $taskRow ['supplier_name'],
								'reported_hours' => $numberingHelper->setNumberFormat ($taskRow['reported_hours'], 'reported_hours'),
								'reported_cost'  => $numberingHelper->setNumberFormat ($taskRow['reported_cost'], 'reported_cost'),
							);
						}
					}
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			DatabaseUtils::closeResult ($sql);
			$sql = null;
			return (isset ($records)) ? $records : null;
		}
		
		private function calculateTaskTotals($crmId, $currentUser) {
			if (empty($crmId)) {
				return array(
					'reported_hours_total' => 0,
					'reported_cost_total' => 0
				);
			}
			
			// Obtener la unidad de medida del trabajo
			$workUnitResult = $this->adb->pquery('SELECT unidades_de_medida FROM vtiger_orden_de_trabajo WHERE orden_de_trabajoid = ?', array($crmId));
			$workUnit = ($workUnitResult && $this->adb->num_rows($workUnitResult) > 0) ? $this->adb->query_result($workUnitResult, 0, 'unidades_de_medida') : null;
			
			$sql = 'SELECT 
						act.estimated_time_unit,
						act.activitytype,
						COALESCE(rep.reported_hours, 0) AS reported_hours,
						COALESCE(rep.reported_cost, 0) AS reported_cost
					FROM vtiger_seactivityrel rel
					INNER JOIN vtiger_activity act ON act.activityid = rel.activityid
					INNER JOIN vtiger_crmentity crm ON crm.crmid = act.activityid AND crm.deleted = 0
					INNER JOIN (
						SELECT activityid, SUM(duration_time) AS reported_hours, SUM(actual_cost) AS reported_cost
						FROM vtiger_activity_report
						WHERE deleted = 0
						GROUP BY activityid
					) rep ON rep.activityid = act.activityid
					WHERE rel.crmid = ?';
			
			$result = $this->adb->pquery($sql, array($crmId));
			$reportedHoursTotal = 0;
			$reportedCostTotal = 0;
			
			if ($result && $this->adb->num_rows($result) > 0) {
				while ($row = $this->adb->fetchByAssoc($result, -1, false)) {
					// Para Unidades Ejecutadas: sumar solo si la unidad coincide con la del trabajo
					// Esto aplica tanto para tareas normales como para tareas tipo Job
					$taskUnit = !empty($row['estimated_time_unit']) ? $row['estimated_time_unit'] : 'Hora';
					if (!empty($workUnit) && !empty($taskUnit) && $taskUnit === $workUnit) {
						$reportedHoursTotal += floatval($row['reported_hours']);
					}
					
					// Para Costo Ejecutado: SIEMPRE sumar todos los costos reportados (incluyendo Job)
					$reportedCostTotal += floatval($row['reported_cost']);
				}
			}
			
			DatabaseUtils::closeResult($result);
			
			return array(
				'reported_hours_total' => $reportedHoursTotal,
				'reported_cost_total' => $reportedCostTotal
			);
		}
		
		private function getAvailableEventStatuses ($mod_strings) {
			$result = $this->adb->query ('SELECT * FROM vtiger_eventstatus ORDER BY eventstatusid');
			if ($this->adb->num_rows ($result) > 0) {
				$availableEventStatuses = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$eventStatus                             = $row ['eventstatus'];
					$availableEventStatuses [ $eventStatus ] = $mod_strings [ $eventStatus ];
				}
			}
			DatabaseUtils::closeResult ($result);
			return isset ($availableEventStatuses) ? $availableEventStatuses : null;
		}
		
		/**
		 * Obtiene la lista de proveedores disponibles para asignar como ejecutores
		 * @return array Lista de proveedores con id y nombre
		 */
		private function fetchAvailableSuppliers () {
			$result = $this->adb->pquery (
				'SELECT p.proveedoresid, p.alias, p.nombre_de_la_sociedad
				 FROM vtiger_proveedores p
				 INNER JOIN vtiger_crmentity c ON c.crmid = p.proveedoresid AND c.deleted = 0
				 ORDER BY p.alias ASC',
				array ()
			);
			
			$suppliers = array ();
			while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
				$displayName = !empty ($row ['alias']) ? $row ['alias'] : $row ['nombre_de_la_sociedad'];
				$suppliers [] = array (
					'id'   => $row ['proveedoresid'],
					'name' => $displayName
				);
			}
			DatabaseUtils::closeResult ($result);
			return $suppliers;
		}
		
		/**
		 * @return null
		 * @throws Exception
		 */
		private function getAvailableActivityTypes () {
			$modStrings = return_module_language('es_es','Calendar');
			$result     = $this->adb->query ('SELECT * FROM vtiger_activitytype ORDER BY activitytype');
			$activityTypes = array();
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					// Retornar array asociativo: tipo => label traducido
					$type = $row['activitytype'];
					$label = isset($modStrings[$type]) ? $modStrings[$type] : $type;
					$activityTypes[$type] = $label;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $activityTypes;
		}
		
		/**
		 * Obtiene los valores del picklist de unidades de medida
		 * @return array
		 */
		private function getAvailableEstimatedTimeUnits () {
			$result = $this->adb->query ('SELECT estimated_time_unit FROM vtiger_estimated_time_unit ORDER BY estimated_time_unitid');
			$units = array();
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$unit = $row['estimated_time_unit'];
					$units[$unit] = $unit;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $units;
		}
		
		/**
		 * @param integer $crmId
		 *
		 * @return TaskActivity|null
		 * @throws Exception
		 */
		private function getGlobalReportData ($crmId) {
			if (empty ($crmId)) {
				return null;
			}
			$arm        = ActivityReportManager::getInstance ($this->adb);
			$activityId = $arm->getTaskFromJobId ($crmId);
			$taskWork   = $arm->fetchActivityTaskById ($activityId);
			return $taskWork;
		}
		
		/**
	 * Convierte una fecha de display (formato usuario) al formato DB (Y-m-d).
	 *
	 * @param string $dateValue
	 * @param Users $currentUser
	 *
	 * @return string
	 */
	private function convertTaskDateToDbFormat ($dateValue, $currentUser) {
		if (empty ($dateValue) || $dateValue === '0000-00-00') {
			return '';
		}
		if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateValue)) {
			return $dateValue;
		}
		return DateTimeField::convertToDBFormat($dateValue, $currentUser);
	}
	
	/**
	 * @param array $relatedTasks
	 * @param Users $currentUser
	 *
	 * @return void
	 * @throws Exception
	 */
	private function setDuplicateTask (&$relatedTasks, $currentUser) {
		if (empty ($relatedTasks)) {
			return;
		}
		
		// Convertir fechas de display a formato DB para todos los cálculos
		$dbDates = array ();
		$minDate = null;
		foreach ($relatedTasks as $key => $task) {
			if ($task['types'] == 'Job') {
				continue;
			}
			$startDateDb = $this->convertTaskDateToDbFormat($task['start_date'], $currentUser);
			$dueDateDb   = $this->convertTaskDateToDbFormat($task['due_date'], $currentUser);
			$dbDates[$key] = array (
				'start_date' => $startDateDb,
				'due_date'   => $dueDateDb,
			);
			if (!empty($startDateDb) && ($minDate === null || $startDateDb < $minDate)) {
				$minDate = $startDateDb;
			}
		}
		
		// Usar la fecha base GLOBAL calculada en EditView.php, o la más antigua como fallback
		$globalBaseDate = isset($_REQUEST['_global_base_date']) ? $_REQUEST['_global_base_date'] : null;
		if (!empty($globalBaseDate)) {
			$olderInitDay = new DateTime($this->convertTaskDateToDbFormat($globalBaseDate, $currentUser));
		} elseif ($minDate !== null) {
			$olderInitDay = new DateTime($minDate);
		} else {
			$olderInitDay = new DateTime();
		}
		
		$baseToday = new DateTime();
		
		$unSetIds = array ();
		foreach ($relatedTasks as $key => &$task) {
			if ($task['types'] == 'Job') {
				$unSetIds[] = $key;
				continue;
			}
			
			// Crear nueva referencia para cada tarea para evitar acumulación
			$taskToday = clone $baseToday;
			
			// Calcular diferencia desde la fecha de inicio original
			$starDate = null;
			$dueDate = null;
			$originalStartDate = '';
			$originalDueDate = '';
			
			$startDateDb = $dbDates[$key]['start_date'];
			$dueDateDb   = $dbDates[$key]['due_date'];
			
			if (!empty ($startDateDb)) {
				$originalStartDate = $task['start_date'];
				$taskIniDate = new DateTime($startDateDb);
				$diff        = $olderInitDay->diff ($taskIniDate);
				$starDate    = $taskToday->add (new DateInterval('P' . $diff->days . 'D'))->format('Y-m-d');
			}
			
			// Aplicar el mismo desplazamiento relativo a la fecha de fin estimada
			if (!empty ($dueDateDb)) {
				$originalDueDate = $task['due_date'];
				$taskEndDate = new DateTime($dueDateDb);
				$diff        = $olderInitDay->diff ($taskEndDate);
				$dueToday    = clone $baseToday;
				$dueDate     = $dueToday->add (new DateInterval('P' . $diff->days . 'D'))->format('Y-m-d');
			}
			
			// La fecha estimada de finalización no puede ser anterior a la de inicio.
			// En ese caso se asigna inicio + 5 días.
			if (!empty($starDate) && !empty($dueDate) && $dueDate < $starDate) {
				$dueDate = (new DateTime($starDate))->add(new DateInterval('P5D'))->format('Y-m-d');
			}
			
			$task ['status']     = 'Planned';
			$task ['progress']   = '1.00';
			$task ['start_date'] = !empty($starDate) ? DateTimeField::convertToUserFormat($starDate, $currentUser) : null;
			$task ['due_date']   = !empty($dueDate) ? DateTimeField::convertToUserFormat($dueDate, $currentUser) : null;
			// NO sobrescribir 'duration' - debe mantener el valor original de estimated_time
			// $task ['duration'] ya contiene el valor correcto de estimated_time desde fetchRelatedTask
			// NO modificar estimated_time - debe mantener el valor original
		}
		
		foreach ($unSetIds as $id) {
			unset ($relatedTasks[$id]);
		}
	}
		
		/**
		 * @param array $dataTask
		 * @return int ID de la actividad guardada
		 * @throws Exception
		 * @throws WebServiceException
		 */
		private function saveActivity ($dataTask) {
				// Obtener datos antiguos para auditoría si es edición
				$oldTaskData = null;
				if ($dataTask ['mode'] == 'edit' && !empty($dataTask ['activityId'])) {
					$oldTaskData = $this->getTaskDataForAudit($dataTask ['activityId']);
				}
				
				$activity =  CRMEntity::getInstance ('Calendar');
				if ($dataTask ['mode'] == 'edit' && !empty( $dataTask ['activityId'])) {
					$activity->mode = 'edit';
					$activity->id   = $dataTask ['activityId'];
					$activity->retrieve_entity_info ($dataTask ['activityId'], 'Calendar');
					$activity->column_fields ['assigned_user_id'] = $dataTask ['ownerUserId'];
					$activity->column_fields ['categoryid']       = $dataTask ['categoryId'];
					$activity->column_fields ['date_start']       = $dataTask ['dateStart'];
					$activity->column_fields ['description']      = $dataTask ['description'];
					$activity->column_fields ['due_date']         = $dataTask ['dueDate'];
					$activity->column_fields ['estimated_time']   = $dataTask ['estimatedTime'];
					$activity->column_fields ['estimated_time_unit'] = isset($dataTask['estimatedTimeUnit']) ? $dataTask['estimatedTimeUnit'] : 'Hora';
					$activity->column_fields ['estimated_cost']   = $dataTask ['estimatedCost'];
					$activity->column_fields ['eventstatus']      = $dataTask ['eventStatus'];
					$activity->column_fields ['subject']          = $dataTask ['subject'];
					if (empty($activity->column_fields ['importance']) || !in_array($activity->column_fields ['importance'], array('HIGH', 'LOW'))) {
						$activity->column_fields ['importance'] = 'HIGH';
					}
					
					$activity->save ('Calendar');
				} else {
					$activity->mode                               = 'create';
					$activity->id                                 = null;
					$activity->column_fields ['activitytype']     = $dataTask ['activityType'];
					$activity->column_fields ['assigned_user_id'] = $dataTask ['ownerUserId'];
					$activity->column_fields ['categoryid']       = $dataTask ['categoryId'];
					$activity->column_fields ['date_start']       = $dataTask ['dateStart'];
					$activity->column_fields ['description']      = $dataTask ['description'];
					$activity->column_fields ['due_date']         = $dataTask ['dueDate'];
					$activity->column_fields ['estimated_time']   = $dataTask ['estimatedTime'];
					$activity->column_fields ['estimated_time_unit'] = isset($dataTask['estimatedTimeUnit']) ? $dataTask['estimatedTimeUnit'] : 'Hora';
					$activity->column_fields ['estimated_cost']   = $dataTask ['estimatedCost'];
					$activity->column_fields ['eventstatus']      = $dataTask ['eventStatus'];
					$activity->column_fields ['importance']       = $dataTask ['importance'];
					$activity->column_fields ['notime']           = 0;
					$activity->column_fields ['planned_task']     = $dataTask ['plannedTask'];
					$activity->column_fields ['progress']         = $dataTask ['progress'];
					$activity->column_fields ['recurringtype']    = '--None--';
					$activity->column_fields ['related_id']       = $dataTask ['related_id'];
					$activity->column_fields ['related_to']       = $dataTask ['related_to'];
					$activity->column_fields ['sendnotification'] = 0;
					$activity->column_fields ['show_in_matrix']   = $dataTask ['showMatrix'];
					$activity->column_fields ['subject']          = mb_substr($dataTask ['subject'], 0, 100, 'UTF-8');
					$activity->column_fields ['taskpriority']     = $dataTask ['priority'];
					$activity->column_fields ['time_end']         = null;
					$activity->column_fields ['time_start']       = null;
					$activity->column_fields ['visibility']       = 'Public';

					// Guardar la actividad sin los campos excluidos primero
					// Evitar que CRMEntity::save interprete la duplicación del trabajo como
					// duplicación de la tarea Calendar usando el ID del trabajo original
					$savedIsDuplicate = isset($_REQUEST['isDuplicate']) ? $_REQUEST['isDuplicate'] : null;
					$savedRecord = isset($_REQUEST['record']) ? $_REQUEST['record'] : null;
					unset($_REQUEST['isDuplicate']);
					unset($_REQUEST['record']);
					$activity->save ('Calendar');
					if ($savedIsDuplicate !== null) {
						$_REQUEST['isDuplicate'] = $savedIsDuplicate;
					}
					if ($savedRecord !== null) {
						$_REQUEST['record'] = $savedRecord;
					}
					
					// Insertar explícitamente los campos excluidos con sus valores correctos (caso particular para creación de tareas)
					$activityId = $activity->id;
					$this->adb->pquery(
						'UPDATE vtiger_activity SET feedbacks = 0, reports = 0, sendnotification = 0, notime = 0, categoryid = ?, related_id = ? WHERE activityid = ?',
						array($dataTask['categoryId'], $dataTask['related_id'], $activityId)
					);
				}
			// Obtener el ID de la actividad (en modo edit ya está definido, en create se asigna después del save)
			$activityId = $activity->id;
			
			// Guardar progress_weighting_factor directamente (no está mapeado en CRMEntity)
			if (isset($dataTask['progressWeightingFactor'])) {
				$weightingValue = !empty($dataTask['progressWeightingFactor']) ? floatval($dataTask['progressWeightingFactor']) : null;
				$this->adb->pquery(
					'UPDATE vtiger_activity SET progress_weighting_factor = ? WHERE activityid = ?',
					array($weightingValue, $activityId)
				);
			}

			// Asegurar valores correctos para campos excluidos del INSERT (modo edición también)
			if ($dataTask ['mode'] == 'edit') {
				$this->adb->pquery(
					'UPDATE vtiger_activity SET feedbacks = COALESCE(feedbacks, 0), sendnotification = 0, notime = 0, categoryid = ?, related_id = ? WHERE activityid = ?',
					array($dataTask['categoryId'], $dataTask['related_id'], $activityId)
				);
			}
		
			// Eliminar relación existente para prevenir duplicados
			$this->adb->pquery('DELETE FROM vtiger_seactivityrel WHERE crmid=? AND activityid=?', array($dataTask['related_id'], $activityId));
			// Insertar relación
			$this->adb->pquery('INSERT INTO vtiger_seactivityrel (crmid, activityid) VALUES (?, ?)', array($dataTask['related_id'], $activityId));
			
			// Guardar relación con proveedor ejecutor
			$this->adb->pquery ('DELETE FROM vtiger_supplieractivityrel WHERE activityid = ?', array ($activityId));
			if (!empty ($dataTask ['supplierId'])) {
				$this->adb->pquery (
					'INSERT INTO vtiger_supplieractivityrel (proveedoresid, activityid) VALUES (?, ?)',
					array (intval ($dataTask ['supplierId']), intval ($activityId))
				);
			}
			
			// Preparar datos para auditoría individual
			$taskDataForAudit = array(
				'id' => $activityId,
				'subject' => $dataTask['subject'],
				'date_start' => $dataTask['dateStart'],
				'due_date' => $dataTask['dueDate'],
				'status' => $dataTask['eventStatus'],
				'progress' => $dataTask['progress'],
				'assigned_to' => $dataTask['ownerUserId']
			);
			
			// Obtener nombre del usuario asignado
			if (!empty($dataTask['ownerUserId'])) {
				$userResult = $this->adb->pquery(
					'SELECT first_name, last_name FROM vtiger_users WHERE id = ?',
					array($dataTask['ownerUserId'])
				);
				if ($this->adb->num_rows($userResult) > 0) {
					$userRow = $this->adb->fetchByAssoc($userResult, -1, false);
					$taskDataForAudit['assigned_name'] = trim($userRow['first_name'] . ' ' . $userRow['last_name']);
				}
				DatabaseUtils::closeResult($userResult);
			}
			
			// NO registrar auditoría individual aquí - se maneja a nivel masivo en saveTaskInWork
			// $this->auditIndividualTask($dataTask['related_id'], $taskDataForAudit, $oldTaskData);

			// Calcular combined_condition, estimated_progress, progress_ratio y executed_cost
			$this->adb->pquery('CALL sp_update_activity_progress_clean(?, ?)', array($activityId, true));
			pearDatabase_FlushResults($this->adb);

			unset ($activity);
			return $activityId;
		}
		
		/**
		 * @param integer $crmId
		 * @param string $stateWork
		 *
		 * @return void
		 */
		private function updateJobStates ($crmId, $stateWork) {
			if (empty ($crmId) || !in_array ($stateWork,self::ORDER_CLOSING_STATUS)) {
				return;
			}
			$this->adb->pquery (
				'UPDATE vtiger_activity SET eventstatus=? WHERE activitytype=? AND related_id=?',
				array('Held', 'Job', $crmId));
		}
		
		/**
		 * @param array $dataTask
		 *
		 * @throws Exception
		 */
		private function updateJobTask ($relatedTask) {
			if (!count ($relatedTask)) {
				return;
			}
			$modifiedTime = date ('Y-m-d H:i:s');
			$today 	      = date ('Y-m-d');
			foreach ($relatedTask as $task) {
				if (empty( $task ['activityId'])) {
					continue;
				}
				$this->adb->pquery ('UPDATE vtiger_crmentity SET modifiedtime = ? WHERE crmid = ?', array($modifiedTime, $task['activityid']));
			$this->adb->pquery ('UPDATE vtiger_activity SET  date_start=?, due_date=?  WHERE activityid = ?', array($today, $today, $task['activityid']));
		}
	}
	
	/**
	 * @param string $dateStart
	 * @param integer $crmId
	 *
	 * @return void
	 */
	private function updateInitDateToWork ($dateStart, $crmId) {
		if (empty($dateStart)) {
			return;
		}
		
		$currentSnapshot = $this->getWorkSnapshot($crmId);
		$currentValue = isset($currentSnapshot['fecha_de_inicio']) ? $currentSnapshot['fecha_de_inicio'] : null;
		if ($currentValue == $dateStart) {
			return;
		}
		
		// Actualizar directamente; la auditoría consolidada se registra al final
		$this->adb->pquery(
			'UPDATE vtiger_orden_de_trabajo SET fecha_de_inicio=? WHERE orden_de_trabajoid = ?',
			array($dateStart, $crmId)
		);
	}
	
	private function updateEstimatedDatesToWork ($crmId, $startDate, $endDate) {
		if (empty ($crmId) || (empty ($startDate) && empty ($endDate))) {
			return;
		}
		
		require_once('data/CRMEntity.php');
		
		try {
			$entity = CRMEntity::getInstance('orden_de_trabajo');
			$entity->id = $crmId;
			$entity->mode = 'edit';
			$entity->retrieve_entity_info($crmId, 'orden_de_trabajo');
			$oldData = $entity->column_fields;
		} catch (Exception $e) {
			return;
		}
		
		$fields = array();
		$params = array();
		
		if (!empty($startDate)) {
			$currentStart = isset($oldData['fecha_prevista']) ? $oldData['fecha_prevista'] : null;
			if ($currentStart != $startDate) {
				$fields[] = 'fecha_prevista=?';
				$params[] = $startDate;
			}
		}
		if (!empty($endDate)) {
			$currentEnd = isset($oldData['fecha_estim_fin']) ? $oldData['fecha_estim_fin'] : null;
			if ($currentEnd != $endDate) {
				$fields[] = 'fecha_estim_fin=?';
				$params[] = $endDate;
			}
		}
		
		if (empty($fields)) {
			return;
		}
		
		$params[] = $crmId;
		$this->adb->pquery(
			'UPDATE vtiger_orden_de_trabajo SET ' . implode(', ', $fields) . ' WHERE orden_de_trabajoid = ?',
			$params
		);
	}
	
	/**
	 * Recalcula las fechas estimadas de inicio y fin de un trabajo
	 * a partir de las tareas relacionadas almacenadas en BD.
	 *
	 * Ignora:
	 *  - tareas de tipo 'Job'
	 *  - tareas eliminadas (vtiger_crmentity.deleted = 1)
	 *  - fechas vacías o '0000-00-00'
	 *
	 * @param integer $crmId
	 * @return void
	 */
	public function recalculateWorkEstimatedDatesFromDb ($crmId) {
			if (empty ($crmId)) {
				return;
			}
			$minStartDateDb = null;
			$maxDueDateDb   = null;
			
			$sql = $this->adb->pquery (
				'SELECT
						act.date_start,
						act.due_date
				   FROM
						vtiger_activity act
				   INNER JOIN vtiger_seactivityrel sar ON sar.activityid = act.activityid
				   INNER JOIN vtiger_crmentity crm ON crm.crmid = act.activityid AND crm.deleted = 0
				   WHERE
						sar.crmid = ? AND
						act.activitytype <> ?'
				,
				array ($crmId, 'Job')
			);
			
			if ($this->adb->num_rows ($sql) > 0) {
				while ($row = $this->adb->fetchByAssoc ($sql, -1, false)) {
					$startDateDb = $row['date_start'];
					$dueDateDb   = $row['due_date'];
					
					if (!empty ($startDateDb) && $startDateDb !== '0000-00-00') {
						if ($minStartDateDb === null || $startDateDb < $minStartDateDb) {
							$minStartDateDb = $startDateDb;
						}
					}
					if (!empty ($dueDateDb) && $dueDateDb !== '0000-00-00') {
						if ($maxDueDateDb === null || $dueDateDb > $maxDueDateDb) {
							$maxDueDateDb = $dueDateDb;
						}
					}
				}
			}
			DatabaseUtils::closeResult ($sql);
			$sql = null;
			
			$this->updateEstimatedDatesToWork ($crmId, $minStartDateDb, $maxDueDateDb);
		}
		
		/**
		 * @param integer $crmId
		 *
		 * @return boolean
		 */
		public function hasRelatedTask ($crmId) {
			if (empty ($crmId)) {
				return true;
			}

			$hasTask = false;
			$result = $this->adb->pquery ('SELECT activityid FROM vtiger_activity WHERE related_id=?', array ($crmId));
			if ($this->adb->num_rows ($result) > 0) {
				$hasTask = true;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $hasTask;
		}
		
		/**
		 * Verifica si todas las tareas relacionadas con un trabajo pueden ser eliminadas.
		 * Una tarea puede eliminarse si:
		 * - No tiene reportes de avance en vtiger_activity_report
		 * - No está relacionada con otros registros (excluyendo el trabajo actual)
		 *
		 * @param integer $crmId ID del trabajo
		 * @return boolean true si todas las tareas pueden eliminarse, false si alguna no puede
		 */
		public function areAllTasksRemovable ($crmId) {
			if (empty ($crmId)) {
				return false;
			}
			
			// Obtener todas las tareas relacionadas con el trabajo
			$result = $this->adb->pquery (
				'SELECT rel.activityid
				 FROM vtiger_seactivityrel rel
				 INNER JOIN vtiger_crmentity crm ON crm.crmid = rel.activityid AND crm.deleted = 0
				 WHERE rel.crmid = ?',
				array ($crmId)
			);
			
			$taskCount = $this->adb->num_rows ($result);
			
			// Si no hay tareas, el trabajo puede eliminarse
			if ($taskCount == 0) {
				DatabaseUtils::closeResult ($result);
				return true;
			}
			
			$taskIds = array ();
			while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
				$taskIds[] = $row['activityid'];
			}
			DatabaseUtils::closeResult ($result);
			
			// Verificar cada tarea
			foreach ($taskIds as $taskId) {
				$relationCheck = $this->checkTaskRelations ($taskId, $crmId);
				if (!$relationCheck['canDelete']) {
					return false;
				}
			}
			
			return true;
		}
		
		/**
		 * @param $crmId
		 * @param null $view
		 * @param $currentUser
		 *
		 * @return string
		 * @throws Exception
		 */
		public function run ($crmId, $view = null, $currentUser) {
		$modStrings = return_module_language ('es_es','Calendar');
			list ($prefix, $crm, $suffix) = explode ('_', $this->adb->dbName);
			unset ($prefix, $crm);
			$relatedTasks = null;
			$workUnitOfMeasure = null;
			if (!empty ($crmId)) {
				$relatedTasks = $this->fetchRelatedTask ($crmId, $currentUser);
				if (empty ($relatedTasks) && !empty ($view)) {
					return '<div class="alert alert-info" style="margin: 10px 0;">No hay tareas relacionadas con este trabajo</div>';
				}
				if (!empty ($relatedTasks) && isset($_REQUEST['isDuplicate']) && ($_REQUEST['isDuplicate'] === 'true')) {
					$this->setDuplicateTask ($relatedTasks, $currentUser);
				}
				$taskWork = $this->getGlobalReportData ($crmId);
				
				// Obtener la unidad de medida del trabajo
				$workResult = $this->adb->pquery('SELECT unidades_de_medida FROM vtiger_orden_de_trabajo WHERE orden_de_trabajoid = ?', array($crmId));
				if ($workResult && $this->adb->num_rows($workResult) > 0) {
					$workUnitOfMeasure = $this->adb->query_result($workResult, 0, 'unidades_de_medida');
				}
			}
			
			$eventStatus      = $this->getAvailableEventStatuses ($modStrings);
			$template         = (empty ($view)) ? 'taskWorkEditView' : 'taskWorkDetailView';
			$preCreatedTask   = new PrecreatedTaskUtils ();
			$moduleObjects    = GridViewHelper::fetchAvailableModules ($this->adb);
		
			// Convertir objetos Module a arrays para compatibilidad con template
			$availableModules = array();
			if (!empty($moduleObjects)) {
				foreach ($moduleObjects as $module) {
					// presence: 0 = visible, 1 = hidden
					$status = ($module->getPresence() == 0) ? 'VISIBLE' : 'HIDDEN';
					$availableModules[] = array(
						'name' => $module->getName(),
						'tabid' => $module->getId(),
						'tablabel' => $module->getLabel(),
						'status' => $status
					);
				}
			}
			
			$objectDate = new DateTime();
			$todayDb    = $objectDate->format ('Y-m-d');
			$today      = DateTimeField::convertToUserFormat($todayDb, $currentUser);
			$objectDate = new DateTime();
			$objectDate->modify ('+1 day');
			$tomorrowDb = $objectDate->format ('Y-m-d');
			$tomorrow   = DateTimeField::convertToUserFormat($tomorrowDb, $currentUser);
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('AREA_TASK', $preCreatedTask->fetchAreaActivity ());
			$smarty->assign ('AVAILABLE_ACTIVITY_TYPES', $this->getAvailableActivityTypes ());
			$smarty->assign ('AVAILABLE_EVENT_STATUSES', $this->getAvailableEventStatuses ($modStrings));
			$smarty->assign ('AVAILABLE_ESTIMATED_TIME_UNITS', $this->getAvailableEstimatedTimeUnits ());
			$smarty->assign ('DEFAULT_ESTIMATED_TIME_UNIT', 'Hora');
			$smarty->assign ('AVAILABLE_IMPORTANCE', DataViewUtils::getAvailableImportanceOfTasks());
			$smarty->assign ('AVAILABLE_MODULES',$availableModules);
			$smarty->assign ('CATEGORIES', DataViewUtils::getAvailableTaskCategories ($this->adb, $currentUser->id));
			$smarty->assign ('AVAILABLE_SUPPLIERS', $this->fetchAvailableSuppliers ());
			$smarty->assign ('AVAILABLE_SYSTEM_USERS', UserManager::getInstance ($this->adb, $suffix)->fetchUsers ());
			$smarty->assign ('AVAILABLE_TASK_PRIORITIES', DataViewUtils::getTaskPriorities ($this->adb));
			$smarty->assign ('AVAILABLE_USERS', DataViewUtils::getAvailableUserAndAvatar ($this->adb, $currentUser));
			$smarty->assign ('CURRENT_USER_ID', $currentUser->id);
			$smarty->assign ('CURRENT_USER_NAME', $currentUser->first_name . ' ' . $currentUser->last_name);
			$smarty->assign ('NUMBERING_FORMAT', $currentUser->numbering_format);
			$smarty->assign ('WORK_ID', $crmId);
			$smarty->assign ('WORK_UNIT_OF_MEASURE', $workUnitOfMeasure);
			$smarty->assign ('RELATED_TASK', $relatedTasks);
			
			// Calcular totales con filtrado correcto
			if (!empty($crmId)) {
				$totals = $this->calculateTaskTotals($crmId, $currentUser);
				$smarty->assign ('REPORTED_HOURS_TOTAL', $totals['reported_hours_total']);
				$smarty->assign ('REPORTED_COST_TOTAL', $totals['reported_cost_total']);
			}
			$smarty->assign ('TODAY', $today);
			$smarty->assign ('TOMORROW', $tomorrow);
			$smarty->assign ('TASK_LIST', $preCreatedTask->fetchPreCreatedTask ());
			$smarty->assign ('TASK_WORK', isset($taskWork) ? $taskWork : null);
			$smarty->assign ('USER_DATE_FORMAT', $currentUser->date_format ? $currentUser->date_format : 'yyyy-mm-dd');
			$smarty->assign ('VIEW', $view);
			$renderedHtml = $smarty->fetch ("modules/orden_de_trabajo/task_job/{$template}.tpl");
			return $renderedHtml;
		}
		
		/**
		 * @param integer $crmId
		 * @param Users $user
		 * @param string $workTitle
		 * @param string $mode
		 *
		 * @throws Exception
		 * @throws WebServiceException
		 */
		public function saveTaskInWork ($crmId, $user, $workTitle, $mode) {
			if (isset($_REQUEST['projec_task'])) {
			}
			try {
			$this->logFunctionTrace('taskToWork::saveTaskInWork', 'START', array(
				'workId' => $crmId,
				'mode' => $mode,
				'userId' => isset($user->id) ? $user->id : null,
			));
			$hasTaskPayload = !empty($_REQUEST['projec_task']);
			if (!$hasTaskPayload) {
				if ($mode == 'edit' && isset ($_REQUEST ['action']) && $_REQUEST ['action'] == self::COPY_ACTION_PLAN) {
					$relatedTask = $this->fetchRelatedTask ($crmId, $user);
					$this->updateJobTask ($relatedTask);
				} else {
					$this->ensureDefaultTask ($crmId, $user, $workTitle);
				}
				$this->logFunctionTrace('taskToWork::saveTaskInWork', 'END_NO_PAYLOAD');
				return;
			}
			
			// Obtener tareas y snapshot del trabajo antes de la operación para auditoría
			$tasksBefore = $this->getTasksForAudit($crmId);
			$workSnapshotBefore = $this->getWorkSnapshot($crmId);
			
			$this->logAuditComparison('SAVE_CONTEXT_START', array(
				'workId' => $crmId,
				'mode' => $mode,
				'stateWork' => isset($_REQUEST ['estado_de_la_orden']) ? $_REQUEST ['estado_de_la_orden'] : null,
				'isTaskPayloadPresent' => !empty($_REQUEST['projec_task']),
				'beforeTasksCount' => count($tasksBefore),
				'workSnapshotBefore' => $workSnapshotBefore,
			));
			
			$stateWork = $_REQUEST ['estado_de_la_orden'];
			$tasks     = $_REQUEST['projec_task'];
			$totalTask = count ( $tasks['types']);
			
			// En modo edición, construir mapa de tareas existentes para guardado incremental
			$existingTasksMap = array();
			$tasksToKeep = array();
			if ($mode == 'edit') {
				foreach ($tasksBefore as $task) {
					$existingTasksMap[$task['id']] = $task;
				}
			}
			
			$this->updateJobStates ($crmId, $stateWork);
			$today           = date ('Y-m-d');
			$numberingHelper = NumberHelper::getInstance ($this->adb, $user);
			$minStartDateDb  = null;
			$maxDueDateDb    = null;
			$totalEstimatedCostTasks = 0;
			$totalEstimatedUnits = 0;
			$totalReportedCost = 0;
			$userWorkEstimatedCost = isset($_REQUEST['work_estimated_cost']) ? $numberingHelper->setSaveNumberFormat($_REQUEST['work_estimated_cost']) : 0;
			
			// Obtener la unidad de medida del trabajo
			$workUnitResult = $this->adb->pquery('SELECT unidades_de_medida FROM vtiger_orden_de_trabajo WHERE orden_de_trabajoid = ?', array($crmId));
			$workUnit = ($workUnitResult && $this->adb->num_rows($workUnitResult) > 0) ? $this->adb->query_result($workUnitResult, 0, 'unidades_de_medida') : null;
			
			for ($k = 0; $k < $totalTask; $k++) {
				$taskMode = 'create';
				
				// Determinar si es actualización o creación
				$taskId = null;
				if (!empty ($tasks['taskId'][ $k ])) {
					$taskId = $tasks['taskId'][ $k ];
					if (isset($existingTasksMap[$taskId])) {
						$taskMode = 'edit';
						$tasksToKeep[] = $taskId;
					}
				}
				
				// Convertir fechas del formato del usuario al formato de BD
				$startDateDb      = $today;
				$dueDateDb        = '';
				$startDateForCalc = null;
				$dueDateForCalc   = null;
				$isJobTask        = (isset ($tasks['types'][ $k ]) && $tasks['types'][ $k ] === 'Job');
				
				if (!empty($tasks['start_date'][$k]) && $tasks['start_date'][$k] !== '0000-00-00') {
					$startDateDb      = DateTimeField::convertToDBFormat($tasks['start_date'][$k], $user);
					$startDateForCalc = $startDateDb;
				}
				if (!empty($tasks['due_date'][$k]) && $tasks['due_date'][$k] !== '0000-00-00') {
					$dueDateDb      = DateTimeField::convertToDBFormat($tasks['due_date'][$k], $user);
					$dueDateForCalc = $dueDateDb;
				}
				if (!$isJobTask) {
					if (!empty ($startDateForCalc) && ($minStartDateDb === null || $startDateForCalc < $minStartDateDb)) {
						$minStartDateDb = $startDateForCalc;
					}
					if (!empty ($dueDateForCalc) && ($maxDueDateDb === null || $dueDateForCalc > $maxDueDateDb)) {
						$maxDueDateDb = $dueDateForCalc;
					}
				}
				$taskNumber = $k + 1;

				$estimatedTimeProcessed = $numberingHelper->setSaveNumberFormat($tasks['duration'][$k]);
				$estimatedCostProcessed = $numberingHelper->setSaveNumberFormat($tasks['estimated_cost'][$k]);
				$taskUnit = !empty($tasks['estimated_time_unit'][$k]) ? $tasks['estimated_time_unit'][$k] : 'Hora';
			
			if (!$isJobTask) {
				$totalEstimatedCostTasks += floatval($estimatedCostProcessed);
				
				// Solo sumar unidades si ambas unidades tienen valor y coinciden (comparación insensible a mayúsculas/minúsculas)
				$workUnitNormalized = !empty($workUnit) ? trim(strtolower($workUnit)) : '';
				$taskUnitNormalized = !empty($taskUnit) ? trim(strtolower($taskUnit)) : '';
				
				if (!empty($workUnitNormalized) && !empty($taskUnitNormalized) && $taskUnitNormalized === $workUnitNormalized) {
					$unitsToAdd = floatval($estimatedTimeProcessed);
					$totalEstimatedUnits += $unitsToAdd;
				}
			}

				$dataTask = array (
						'activityId'    => $taskId,
						'activityType'  => $tasks['types'][ $k ],
						'categoryId'    => 10,
						'dateStart'     => $startDateDb,
						'description'   => $tasks['task'][ $k ],
						'dueDate'       => $dueDateDb,
						'estimatedTime' => $estimatedTimeProcessed,
						'estimatedTimeUnit' => !empty($tasks['estimated_time_unit'][$k]) ? $tasks['estimated_time_unit'][$k] : 'Hora',
						'estimatedCost' => $estimatedCostProcessed,
						'eventStatus'   => (in_array ($stateWork,self::ORDER_CLOSING_STATUS)) ? 'Held' : $tasks['status'][ $k ],
						'importance'    => 'HIGH',
						'mode'          => $taskMode,
						'ownerUserId'   => $tasks['assigned'][ $k ],
						'plannedTask'   => 'PLANNED_AND_RECORDED',
						'priority'      => 'Bajo',
						'progress'      => 0,
						'progressWeightingFactor' => isset($tasks['progress_weighting_factor'][$k]) && $tasks['progress_weighting_factor'][$k] !== '' ? $numberingHelper->setSaveNumberFormat($tasks['progress_weighting_factor'][$k]) : null,
						'related_id'    => $crmId,
						'related_to'    => self::TASK_RELATED_TO,
						'showMatrix'    => 'YES',
						'subject'       => $tasks['task_title'][ $k ],
						'supplierId'    => !empty ($tasks['supplier'][ $k ]) ? $tasks['supplier'][ $k ] : null,
				);
				$savedActivityId = $this->saveActivity ($dataTask);
				
				// Registrar el ID de la tarea guardada para no eliminarla después
				if (!empty($savedActivityId)) {
					$tasksToKeep[] = $savedActivityId;
				}
				if ($k == 0) {
					$this->updateInitDateToWork ($startDateDb, $crmId);
				}
		}
			
			// Eliminar solo las tareas que ya no están en el payload (guardado incremental)
			if ($mode == 'edit' && !empty($existingTasksMap)) {
				$globalTaskId = ActivityReportManager::getInstance ($this->adb)->getTaskFromJobId ($crmId);
				foreach ($existingTasksMap as $existingTaskId => $existingTask) {
					// No eliminar si es la tarea global o si está en el payload actual
					if ($existingTaskId == $globalTaskId || in_array($existingTaskId, $tasksToKeep)) {
						continue;
					}
					// Eliminar tarea usando el método que verifica relaciones
					$this->deleteTaskFromWork($existingTaskId, $crmId);
				}
			}
			
			// Calcular el total de costos reportados de todas las tareas
			$totalReportedCost = $this->calculateTotalReportedCost($crmId);
			
			// Calcular el total de unidades ejecutadas (con filtrado por tipo de unidad)
			$totalReportedUnits = $this->calculateTotalReportedUnits($crmId);
			
			$this->updateEstimatedDatesToWork ($crmId, $minStartDateDb, $maxDueDateDb);
			$this->updateWorkEstimatedCost($crmId, $totalEstimatedCostTasks, $userWorkEstimatedCost);
			$this->updateWorkEstimatedUnits($crmId, $totalEstimatedUnits);
			$this->updateWorkPerformedCost($crmId, $totalReportedCost);
			$this->updateWorkConsumedUnits($crmId, $totalReportedUnits);
			$this->calculateAndUpdateProgressWeightingFactors($crmId);
			$this->updateExpectedWorkProgress($crmId);
			$this->updateWorkSituation($crmId);
			
			$workSnapshotAfter = $this->getWorkSnapshot($crmId);
			$workFieldChanges = $this->getWorkFieldChanges($workSnapshotBefore, $workSnapshotAfter);
			
			// Obtener tareas después de la operación y registrar auditoría
			$tasksAfter = $this->getTasksForAudit($crmId);
			$operation = ($mode == 'edit') ? 'ACTUALIZACIÓN MASIVA' : 'CREACIÓN MASIVA';
			$taskDifferences = $this->getTaskDifferences($tasksBefore, $tasksAfter);
			
			$this->logAuditComparison('SAVE_CONTEXT_END', array(
				'workId' => $crmId,
				'operation' => $operation,
				'tasksAfterCount' => count($tasksAfter),
				'workSnapshotAfter' => $workSnapshotAfter,
				'workFieldChanges' => $workFieldChanges,
				'taskDifferences' => $taskDifferences,
			));
			
			$this->auditTasksOperation($crmId, $operation, $tasksBefore, $tasksAfter, $taskDifferences, $workFieldChanges);
			
			$this->logFunctionTrace('taskToWork::saveTaskInWork', 'END', array(
				'workId' => $crmId,
				'operation' => $operation,
				'tasksAfterCount' => count($tasksAfter),
			));
			} catch (Exception $e) {
				throw $e;
			}
		}
		
		private function updateWorkEstimatedCost($crmId, $totalEstimatedCostTasks, $userWorkEstimatedCost) {
			if (empty($crmId)) {
				return;
			}
			$desiredValue = max(floatval($totalEstimatedCostTasks), floatval($userWorkEstimatedCost));
			
			$workSnapshot = $this->getWorkSnapshot($crmId);
			$currentValue = isset($workSnapshot['work_estimated_cost']) ? floatval($workSnapshot['work_estimated_cost']) : null;
			if ($currentValue !== null && $currentValue == $desiredValue) {
				return;
			}
			
			$this->adb->pquery(
				'UPDATE vtiger_orden_de_trabajo SET work_estimated_cost=? WHERE orden_de_trabajoid = ?',
				array($desiredValue, $crmId)
			);
		}
		
		private function updateWorkEstimatedUnits($crmId, $totalEstimatedUnits) {
			if (empty($crmId)) {
				return;
			}
			
			$workSnapshot = $this->getWorkSnapshot($crmId);
			$currentValue = isset($workSnapshot['numero_unidades_planificadas']) ? floatval($workSnapshot['numero_unidades_planificadas']) : null;
			if ($currentValue !== null && $currentValue == floatval($totalEstimatedUnits)) {
				return;
			}
			
			$this->adb->pquery(
				'UPDATE vtiger_orden_de_trabajo SET numero_unidades_planificadas=? WHERE orden_de_trabajoid = ?',
				array(floatval($totalEstimatedUnits), $crmId)
			);
		}
		
		private function calculateTotalReportedCost($crmId) {
			if (empty($crmId)) {
				return 0;
			}
			
			// Para Costo Ejecutado: incluir TODAS las tareas (incluyendo Job)
			$sql = 'SELECT SUM(COALESCE(rep.reported_cost, 0)) AS total_reported_cost
					FROM vtiger_seactivityrel rel
					INNER JOIN vtiger_activity act ON act.activityid = rel.activityid
					INNER JOIN vtiger_crmentity crm ON crm.crmid = act.activityid AND crm.deleted = 0
					LEFT JOIN (
						SELECT activityid, SUM(actual_cost) AS reported_cost
						FROM vtiger_activity_report
						WHERE deleted = 0
						GROUP BY activityid
					) rep ON rep.activityid = act.activityid
					WHERE rel.crmid = ?';
			
			$result = $this->adb->pquery($sql, array($crmId));
			if ($result && $this->adb->num_rows($result) > 0) {
				$total = $this->adb->query_result($result, 0, 'total_reported_cost');
				return floatval($total);
			}
			return 0;
		}
		
		private function updateWorkPerformedCost($crmId, $totalReportedCost) {
			if (empty($crmId)) {
				return;
			}
			
			$workSnapshot = $this->getWorkSnapshot($crmId);
			$currentValue = isset($workSnapshot['cost_work_performed']) ? floatval($workSnapshot['cost_work_performed']) : null;
			if ($currentValue !== null && $currentValue == floatval($totalReportedCost)) {
				return;
			}
			
			$this->adb->pquery(
				'UPDATE vtiger_orden_de_trabajo SET cost_work_performed=? WHERE orden_de_trabajoid = ?',
				array(floatval($totalReportedCost), $crmId)
			);
		}
		
		private function calculateTotalReportedUnits($crmId) {
			if (empty($crmId)) {
				return 0;
			}
			
			// Obtener la unidad de medida del trabajo
			$workUnitResult = $this->adb->pquery('SELECT unidades_de_medida FROM vtiger_orden_de_trabajo WHERE orden_de_trabajoid = ?', array($crmId));
			$workUnit = ($workUnitResult && $this->adb->num_rows($workUnitResult) > 0) ? $this->adb->query_result($workUnitResult, 0, 'unidades_de_medida') : null;
			
			// Para Unidades Ejecutadas: incluir todas las tareas (incluyendo Job) si la unidad coincide
			$sql = 'SELECT 
						act.estimated_time_unit,
						act.activitytype,
						COALESCE(rep.reported_hours, 0) AS reported_hours
					FROM vtiger_seactivityrel rel
					INNER JOIN vtiger_activity act ON act.activityid = rel.activityid
					INNER JOIN vtiger_crmentity crm ON crm.crmid = act.activityid AND crm.deleted = 0
					LEFT JOIN (
						SELECT activityid, SUM(duration_time) AS reported_hours
						FROM vtiger_activity_report
						WHERE deleted = 0
						GROUP BY activityid
					) rep ON rep.activityid = act.activityid
					WHERE rel.crmid = ?';
			
			$result = $this->adb->pquery($sql, array($crmId));
			$totalReportedUnits = 0;
			
			if ($result && $this->adb->num_rows($result) > 0) {
				while ($row = $this->adb->fetchByAssoc($result, -1, false)) {
					// Solo sumar si la unidad coincide con la del trabajo
					$taskUnit = !empty($row['estimated_time_unit']) ? $row['estimated_time_unit'] : 'Hora';
					if (!empty($workUnit) && !empty($taskUnit) && $taskUnit === $workUnit) {
						$totalReportedUnits += floatval($row['reported_hours']);
					}
				}
			}
			
			DatabaseUtils::closeResult($result);
			return $totalReportedUnits;
		}
		
		private function updateWorkConsumedUnits($crmId, $totalReportedUnits) {
			if (empty($crmId)) {
				return;
			}
			
			$workSnapshot = $this->getWorkSnapshot($crmId);
			$currentValue = isset($workSnapshot['unidades_consumidas']) ? floatval($workSnapshot['unidades_consumidas']) : null;
			if ($currentValue !== null && $currentValue == floatval($totalReportedUnits)) {
				return;
			}
			
			$this->adb->pquery(
				'UPDATE vtiger_orden_de_trabajo SET unidades_consumidas=? WHERE orden_de_trabajoid = ?',
				array(floatval($totalReportedUnits), $crmId)
			);
		}
		
		/**
		 * Calcula y actualiza automáticamente el progress_weighting_factor de las tareas
		 * cuando es 0 o NULL, basándose en la proporción de costos estimados
		 * 
		 * @param integer $crmId ID del trabajo
		 * @return void
		 */
		private function calculateAndUpdateProgressWeightingFactors($crmId) {
			if (empty($crmId)) {
				return;
			}
			
			// Obtener todas las tareas del trabajo (excluyendo tipo Job)
			$tasksQuery = 'SELECT 
							a.activityid,
							a.estimated_cost,
							a.progress_weighting_factor
						FROM
							vtiger_activity a
						INNER JOIN vtiger_crmentity crm ON crm.crmid = a.activityid AND crm.deleted=0
						INNER JOIN vtiger_seactivityrel sa ON sa.activityid = a.activityid
						WHERE
							sa.crmid=? AND
							a.activitytype !=?';
			
			$result = $this->adb->pquery($tasksQuery, array($crmId, 'Job'));
			
			if (!$result || $this->adb->num_rows($result) == 0) {
				return;
			}
			
			// Recopilar tareas y calcular total de costos
			$tasks = array();
			$totalEstimatedCost = 0;
			$tasksWithoutWeighting = array();
			$totalWeightingUsed = 0;
			
			while ($row = $this->adb->fetchByAssoc($result, -1, false)) {
				$taskId = $row['activityid'];
				$estimatedCost = floatval($row['estimated_cost']);
				$weightingFactor = $row['progress_weighting_factor'];
				
				$tasks[$taskId] = array(
					'estimated_cost' => $estimatedCost,
					'weighting_factor' => $weightingFactor
				);
				
				// Si ya tiene un weighting_factor definido, sumarlo al total usado
				if (!empty($weightingFactor) && floatval($weightingFactor) > 0) {
					$totalWeightingUsed += floatval($weightingFactor);
				} else {
					// Tarea sin weighting_factor - acumular costo para cálculo
					$tasksWithoutWeighting[$taskId] = $estimatedCost;
					$totalEstimatedCost += $estimatedCost;
				}
			}
			
			DatabaseUtils::closeResult($result);
			
			// Si no hay tareas sin weighting o no hay costo total, salir
			if (empty($tasksWithoutWeighting) || $totalEstimatedCost == 0) {
				return;
			}
			
			// Calcular el porcentaje restante disponible
			$remainingPercentage = 100 - $totalWeightingUsed;
			
			// Si no hay porcentaje restante o es negativo, no calcular
			if ($remainingPercentage <= 0) {
				return;
			}
			
			// Calcular y actualizar el weighting_factor para cada tarea sin valor
			foreach ($tasksWithoutWeighting as $taskId => $estimatedCost) {
				// Calcular proporción basada en costo
				$proportion = $estimatedCost / $totalEstimatedCost;
				
				// Calcular weighting_factor como proporción del porcentaje restante
				$calculatedWeighting = $proportion * $remainingPercentage;
				
				// Actualizar en la base de datos
				$this->adb->pquery(
					'UPDATE vtiger_activity SET progress_weighting_factor=? WHERE activityid=?',
					array($calculatedWeighting, $taskId)
				);
			}
		}
		
		/**
		 * Calcula la situación del trabajo basada en progreso, costos y unidades
		 * 
		 * Estados posibles:
		 * - Óptima: Progreso >= esperado, costo <= estimado, unidades <= planificadas
		 * - En control: Progreso dentro del 95-105% del esperado, costo <= estimado
		 * - Alerta de eficiencia: Progreso <= esperado*1.05, pero (costo > estimado O unidades > planificadas)
		 * - Retraso operativo: Progreso < esperado, costo <= estimado
		 * - Crítica: Progreso < esperado, costo > estimado
		 * 
		 * @param integer $crmId ID del trabajo
		 * @return string Situación calculada
		 */
		private function calculateWorkSituation($crmId) {
			if (empty($crmId)) {
				return null;
			}
			
			// Obtener los valores actuales del trabajo
			$query = 'SELECT 
							unidades_consumidas,
							numero_unidades_planificadas,
							cost_work_performed,
							work_estimated_cost,
							overall_progress_perc,
							expected_work_progress
					  FROM vtiger_orden_de_trabajo
					  WHERE orden_de_trabajoid = ?';
			
			$result = $this->adb->pquery($query, array($crmId));
			
			if (!$result || $this->adb->num_rows($result) == 0) {
				return null;
			}
			
			$row = $this->adb->fetchByAssoc($result, -1, false);
			DatabaseUtils::closeResult($result);
			
			// Convertir valores a float
			$unidadesConsumidas = floatval($row['unidades_consumidas']);
			$unidadesPlanificadas = floatval($row['numero_unidades_planificadas']);
			$costoEjecutado = floatval($row['cost_work_performed']);
			$costoEstimado = floatval($row['work_estimated_cost']);
			$progresoReal = floatval($row['overall_progress_perc']);
			$progresoEsperado = floatval($row['expected_work_progress']);
			
			// Evaluar condiciones en orden de prioridad
			
			// 1. Óptima: Progreso >= esperado, costo <= estimado, unidades <= planificadas (o sin unidades planificadas)
			if ($progresoReal >= $progresoEsperado && 
				$costoEjecutado <= $costoEstimado && 
				($unidadesPlanificadas == 0 || $unidadesConsumidas <= $unidadesPlanificadas)) {
				return 'Óptima';
			}
			
			// 2. Crítica: Progreso < esperado Y costo > estimado
			if ($progresoReal < $progresoEsperado && 
				$costoEjecutado > $costoEstimado) {
				return 'Crítica';
			}
			
			// 3. Alerta de eficiencia: Progreso <= esperado*1.05 Y (costo > estimado O (unidades > planificadas Y planificadas > 0))
			if ($progresoReal <= ($progresoEsperado * 1.05) && 
				($costoEjecutado > $costoEstimado || ($unidadesPlanificadas > 0 && $unidadesConsumidas > $unidadesPlanificadas))) {
				return 'Alerta de eficiencia';
			}
			
			// 4. Alerta de eficiencia (caso adelantado): Progreso > esperado PERO (costo > estimado O unidades > planificadas)
			if ($progresoReal > $progresoEsperado && 
				($costoEjecutado > $costoEstimado || ($unidadesPlanificadas > 0 && $unidadesConsumidas > $unidadesPlanificadas))) {
				return 'Alerta de eficiencia';
			}
			
			// 5. En control: Progreso dentro del 95-105% del esperado Y costo <= estimado
			if ($progresoReal >= ($progresoEsperado * 0.95) && 
				$progresoReal <= ($progresoEsperado * 1.05) && 
				$costoEjecutado <= $costoEstimado) {
				return 'En control';
			}
			
			// 6. Retraso operativo: Progreso < esperado Y costo <= estimado
			if ($progresoReal < $progresoEsperado && 
				$costoEjecutado <= $costoEstimado) {
				return 'Retraso operativo';
			}
			
			// Por defecto, si no cumple ninguna condición específica
			return 'En control';
		}
		
		/**
		 * Actualiza el campo work_situation del trabajo y registra el cambio en el histórico
		 * 
		 * @param integer $crmId ID del trabajo
		 * @return void
		 */
		private function updateWorkSituation($crmId) {
			if (empty($crmId)) {
				return;
			}
			
			// FIX: Limpiar resultados pendientes de la conexión (Commands out of sync)
			pearDatabase_FlushResults($this->adb);
			
			// Obtener el valor actual de work_situation
			$currentQuery = 'SELECT work_situation FROM vtiger_orden_de_trabajo WHERE orden_de_trabajoid = ?';
			$currentResult = $this->adb->pquery($currentQuery, array($crmId));
			
			if (!$currentResult || $this->adb->num_rows($currentResult) == 0) {
				return;
			}
			
			$currentRow = $this->adb->fetchByAssoc($currentResult, -1, false);
			$currentSituation = $currentRow['work_situation'];
			DatabaseUtils::closeResult($currentResult);
			
			// Calcular la nueva situación
			$newSituation = $this->calculateWorkSituation($crmId);
			
			if ($newSituation === null) {
				return;
			}
			
			// Solo actualizar si el valor cambió
			if ($currentSituation === $newSituation) {
				return;
			}
			
			// Actualizar el campo en la base de datos
			$this->adb->pquery(
				'UPDATE vtiger_orden_de_trabajo SET work_situation=? WHERE orden_de_trabajoid = ?',
				array($newSituation, $crmId)
			);
			
			// Nota: El cambio de work_situation se actualiza directamente en la BD.
			// Para registrar en el histórico, el campo debe estar configurado en vtiger_field
			// y ModTracker debe estar habilitado para el módulo orden_de_trabajo.
		}
		
		/**
		 * Actualiza el progreso esperado del trabajo
		 * Siempre calcula automáticamente basado en las tareas del trabajo
		 * 
		 * @param integer $crmId ID del trabajo
		 * @return void
		 */
		private function updateExpectedWorkProgress($crmId) {
			if (empty($crmId)) {
				return;
			}
			
			// Calcular automáticamente basado en las tareas
			require_once('include/platzilla/Data/ActivityReportManager.php');
			$arm = ActivityReportManager::getInstance($this->adb);
			$desiredValue = $arm->calculateExpectedProgress($crmId);
			
			// Siempre actualizar el valor calculado (sin optimización de comparación)
			// Esto garantiza que expected_work_progress siempre refleje el estado actual de las tareas
			$this->adb->pquery(
				'UPDATE vtiger_orden_de_trabajo SET expected_work_progress=? WHERE orden_de_trabajoid = ?',
				array(floatval($desiredValue), $crmId)
			);
			
			// Actualizar el progreso esperado de los proyectos asociados a este trabajo
			$this->updateRelatedProjectsEstimatedProgress($crmId);
		}
		
		/**
		 * Actualiza el progreso esperado de los proyectos asociados a un trabajo
		 * Solo actualiza si el trabajo está relacionado con algún proyecto en vtiger_project_works
		 * 
		 * @param integer $workId ID del trabajo
		 * @return void
		 */
		private function updateRelatedProjectsEstimatedProgress($workId) {
			if (empty($workId)) {
				return;
			}
			
			// Verificar si el trabajo está relacionado con algún proyecto
			$checkQuery = 'SELECT COUNT(*) as count 
		               FROM vtiger_project_works 
		               WHERE crmid_job = ?';
			$checkResult = $this->adb->pquery($checkQuery, array($workId));
			
			if (!$checkResult || $this->adb->query_result($checkResult, 0, 'count') == 0) {
				// El trabajo no está relacionado con ningún proyecto, no hacer nada
				return;
			}
			
			// Cargar el gestor de progreso esperado de proyectos
			require_once('modules/proyectos/handlers/ProjectEstimatedProgressManager.class.php');
			
			try {
				$projectManager = ProjectEstimatedProgressManager::getInstance($this->adb);
				$projectManager->updateProjectsByWorkId($workId);
			} catch (Exception $e) {
				// Registrar el error pero no interrumpir el flujo principal
				error_log("Error al actualizar progreso esperado de proyectos para trabajo {$workId}: " . $e->getMessage());
			}
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return taskToWork
		 */
		public static function getInstance (PearDatabase $adb) {
			return new self ($adb);
		}
		
		/**
		 * Obtiene las tareas de un trabajo para auditoría
		 * @param integer $crmId
		 * @return array
		 */
		private function getTasksForAudit($crmId) {
			$this->logFunctionTrace('taskToWork::getTasksForAudit', 'START', array('workId' => $crmId));
			if (empty($crmId)) {
				$this->logFunctionTrace('taskToWork::getTasksForAudit', 'END_EMPTY');
				return array();
			}
			
			// FIX: Limpiar resultados pendientes de la conexión (Commands out of sync)
			pearDatabase_FlushResults($this->adb);
			
			$result = $this->adb->pquery(
				'SELECT DISTINCT act.activityid, act.subject, act.date_start, act.due_date, 
				        act.eventstatus, act.progress, crm.smownerid, usr.first_name, usr.last_name,
				        srel.proveedoresid, prov.alias AS supplier_name
				   FROM vtiger_activity act
				   INNER JOIN vtiger_crmentity crm ON crm.crmid = act.activityid AND crm.deleted = 0
				   INNER JOIN vtiger_seactivityrel rel ON rel.activityid = act.activityid
				   LEFT JOIN vtiger_users usr ON usr.id = crm.smownerid
				   LEFT JOIN vtiger_supplieractivityrel srel ON srel.activityid = act.activityid
				   LEFT JOIN vtiger_proveedores prov ON prov.proveedoresid = srel.proveedoresid
				   WHERE rel.crmid = ? AND act.activitytype <> ?',
				array($crmId, 'Job')
			);
			
			$tasks = array();
			if ($this->adb->num_rows($result) > 0) {
				while ($row = $this->adb->fetchByAssoc($result, -1, false)) {
					$tasks[] = array(
						'id' => $row['activityid'],
						'subject' => $row['subject'],
						'date_start' => $row['date_start'],
						'due_date' => $row['due_date'],
						'status' => $row['eventstatus'],
						'progress' => $row['progress'],
						'assigned_to' => $row['smownerid'],
						'assigned_name' => trim($row['first_name'] . ' ' . $row['last_name']),
						'supplier_id' => $row['proveedoresid'],
						'supplier_name' => $row['supplier_name'] ? $row['supplier_name'] : ''
					);
				}
			}
			DatabaseUtils::closeResult($result);
			$this->logFunctionTrace('taskToWork::getTasksForAudit', 'END', array('count' => count($tasks)));
			return $tasks;
		}
		
		/**
		 * Obtiene datos específicos de una tarea para auditoría
		 * @param integer $taskId
		 * @return array|null
		 */
		private function getTaskDataForAudit($taskId) {
			$this->logFunctionTrace('taskToWork::getTaskDataForAudit', 'START', array('taskId' => $taskId));
			if (empty($taskId)) {
				$this->logFunctionTrace('taskToWork::getTaskDataForAudit', 'END_EMPTY');
				return null;
			}
			
			$result = $this->adb->pquery(
				'SELECT act.activityid, act.subject, act.date_start, act.due_date, 
				        act.eventstatus, act.progress, crm.smownerid, usr.first_name, usr.last_name,
				        srel.proveedoresid, prov.alias AS supplier_name
				   FROM vtiger_activity act
				   INNER JOIN vtiger_crmentity crm ON crm.crmid = act.activityid AND crm.deleted = 0
				   LEFT JOIN vtiger_users usr ON usr.id = crm.smownerid
				   LEFT JOIN vtiger_supplieractivityrel srel ON srel.activityid = act.activityid
				   LEFT JOIN vtiger_proveedores prov ON prov.proveedoresid = srel.proveedoresid
				   WHERE act.activityid = ?',
				array($taskId)
			);
			
			$taskData = null;
			if ($this->adb->num_rows($result) > 0) {
				$row = $this->adb->fetchByAssoc($result, -1, false);
				$taskData = array(
					'id' => $row['activityid'],
					'subject' => $row['subject'],
					'date_start' => $row['date_start'],
					'due_date' => $row['due_date'],
					'status' => $row['eventstatus'],
					'progress' => $row['progress'],
					'assigned_to' => $row['smownerid'],
					'assigned_name' => trim($row['first_name'] . ' ' . $row['last_name']),
					'supplier_id' => $row['proveedoresid'],
					'supplier_name' => $row['supplier_name'] ? $row['supplier_name'] : ''
				);
			}
			DatabaseUtils::closeResult($result);
			$this->logFunctionTrace('taskToWork::getTaskDataForAudit', 'END', array('hasData' => !empty($taskData)));
			return $taskData;
		}
		
		/**
		 * Calcula diferencias entre listas de tareas antes/después
		 * @param array $tasksBefore
		 * @param array $tasksAfter
		 * @return array
		 */
		private function getTaskDifferences($tasksBefore, $tasksAfter) {
			$this->logFunctionTrace('taskToWork::getTaskDifferences', 'START', array(
				'countBefore' => count($tasksBefore),
				'countAfter' => count($tasksAfter),
			));
			$beforeById = array();
			foreach ($tasksBefore as $task) {
				if (!empty($task['id'])) {
					$beforeById[$task['id']] = $task;
				}
			}
			
			$afterById = array();
			foreach ($tasksAfter as $task) {
				if (!empty($task['id'])) {
					$afterById[$task['id']] = $task;
				}
			}
			
			$differences = array(
				'added' => array(),
				'removed' => array(),
				'updated' => array(),
			);
			
			foreach ($afterById as $taskId => $task) {
				if (!isset($beforeById[$taskId])) {
					$taskDetail = '"' . $task['subject'] . '"';
					if (!empty($task['date_start']) || !empty($task['due_date'])) {
						$taskDetail .= ' (';
						if (!empty($task['date_start'])) {
							$taskDetail .= 'Inicio: ' . $task['date_start'];
						}
						if (!empty($task['due_date'])) {
							if (!empty($task['date_start'])) {
								$taskDetail .= ', ';
							}
							$taskDetail .= 'Fin: ' . $task['due_date'];
						}
						$taskDetail .= ')';
					}
					$differences['added'][] = $taskDetail;
				}
			}
			
			foreach ($beforeById as $taskId => $task) {
				if (!isset($afterById[$taskId])) {
					$taskDetail = '"' . $task['subject'] . '"';
					if (!empty($task['date_start']) || !empty($task['due_date'])) {
						$taskDetail .= ' (';
						if (!empty($task['date_start'])) {
							$taskDetail .= 'Inicio: ' . $task['date_start'];
						}
						if (!empty($task['due_date'])) {
							if (!empty($task['date_start'])) {
								$taskDetail .= ', ';
							}
							$taskDetail .= 'Fin: ' . $task['due_date'];
						}
						$taskDetail .= ')';
					}
					$differences['removed'][] = $taskDetail;
				}
			}
			
			foreach ($afterById as $taskId => $task) {
				if (isset($beforeById[$taskId])) {
					$changes = $this->getTaskChanges($beforeById[$taskId], $task);
					if (!empty($changes)) {
						$differences['updated'][] = array(
							'subject' => $task['subject'],
							'changes' => $changes,
						);
					}
				}
			}
			
			$this->logAuditComparison('TASK_DIFF_INPUT', array(
				'beforeById' => $beforeById,
				'afterById' => $afterById,
				'result' => $differences,
			));
			
			$this->logFunctionTrace('taskToWork::getTaskDifferences', 'END', array(
				'added' => count($differences['added']),
				'removed' => count($differences['removed']),
				'updated' => count($differences['updated']),
			));
			
			return $differences;
		}
		
		/**
		 * Obtiene un snapshot del trabajo para auditoría
		 * @param integer $workId
		 * @return array
		 */
		private function getWorkSnapshot($workId) {
			$this->logFunctionTrace('taskToWork::getWorkSnapshot', 'START', array('workId' => $workId));
			if (empty($workId)) {
				$this->logFunctionTrace('taskToWork::getWorkSnapshot', 'END_EMPTY');
				return array();
			}
			
			// FIX: Limpiar resultados pendientes de la conexión (Commands out of sync)
			pearDatabase_FlushResults($this->adb);
			
			$fieldsMap = $this->getWorkAuditFieldLabels();
			$fieldNames = array_keys($fieldsMap);
			
			$sql = 'SELECT ' . implode(', ', $fieldNames) . ' FROM vtiger_orden_de_trabajo WHERE orden_de_trabajoid = ?';
			$result = $this->adb->pquery($sql, array($workId));
			
			if ($result && $this->adb->num_rows($result) > 0) {
				$row = $this->adb->fetchByAssoc($result, -1, false);
				DatabaseUtils::closeResult($result);
				return $row;
			}
			
			DatabaseUtils::closeResult($result);
			$this->logFunctionTrace('taskToWork::getWorkSnapshot', 'END_NO_RESULT');
			return array();
		}
		
		/**
		 * Campos del trabajo relevantes para auditoría masiva
		 * @return array fieldName => label
		 */
		private function getWorkAuditFieldLabels() {
			$this->logFunctionTrace('taskToWork::getWorkAuditFieldLabels', 'START');
			$fields = array(
				'fecha_de_inicio' => 'Fecha de inicio',
				'fecha_prevista' => 'Fecha prevista',
				'fecha_estim_fin' => 'Fecha estimada fin',
				'fecha_de_emision' => 'Fecha de emisión',
				'numero_unidades_planificadas' => 'Trabajo a ejecutar en el lapso de',
				'work_estimated_cost' => 'Costo estimado del trabajo',
				'cost_work_performed' => 'Costo ejecutado',
				'overall_progress_perc' => 'Avance del trabajo',
				'expected_work_progress' => 'Progreso esperado del trabajo',
				'priority_index' => 'Índice de prioridad',
				'work_priority' => 'Prioridad del trabajo',
				'unidades_consumidas' => 'Unidades consumidas',
				'unidades_de_medida' => 'Unidades de medida',
			);
			$this->logFunctionTrace('taskToWork::getWorkAuditFieldLabels', 'END', array('fields' => array_keys($fields)));
			return $fields;
		}
		
		/**
		 * Calcula cambios de campos relevantes del trabajo
		 * @param array $before
		 * @param array $after
		 * @return array
		 */
		private function getWorkFieldChanges($before, $after) {
			$this->logFunctionTrace('taskToWork::getWorkFieldChanges', 'START', array(
				'hasBefore' => !empty($before),
				'hasAfter' => !empty($after),
			));
			if (empty($before) || empty($after)) {
				$this->logFunctionTrace('taskToWork::getWorkFieldChanges', 'END_EMPTY');
				return array();
			}
			
			$fields = $this->getWorkAuditFieldLabels();
			
			$changes = array();
			foreach ($fields as $field => $label) {
				if (
					array_key_exists($field, $before) &&
					array_key_exists($field, $after)
				) {
					// Normalizar valores para comparación
					$normalizedBefore = $this->normalizeValueForComparison($before[$field], $field);
					$normalizedAfter = $this->normalizeValueForComparison($after[$field], $field);
					
					// Comparar valores normalizados
					if ($normalizedBefore != $normalizedAfter) {
						$changes[] = "{$label}: {$before[$field]} → {$after[$field]}";
						$this->logAuditComparison('WORK_FIELD_CHANGE', array(
							'field' => $field,
							'label' => $label,
							'before' => $before[$field],
							'after' => $after[$field],
							'normalizedBefore' => $normalizedBefore,
							'normalizedAfter' => $normalizedAfter,
						));
					}
				}
			}
			
			$this->logAuditComparison('WORK_FIELD_COMPARE', array(
				'before' => $before,
				'after' => $after,
				'changes' => $changes,
			));
			
			$this->logFunctionTrace('taskToWork::getWorkFieldChanges', 'END', array('changesCount' => count($changes)));
			
			return $changes;
		}
		
		/**
		 * Registra auditoría de operaciones de tareas en el trabajo
		 * @param integer $workId
		 * @param string $operation
		 * @param array $tasksBefore
		 * @param array $tasksAfter
		 * @param array $taskDifferences
		 * @param array $workFieldChanges
		 */
		private function auditTasksOperation($workId, $operation, $tasksBefore, $tasksAfter, $taskDifferences = array(), $workFieldChanges = array()) {
			$this->logFunctionTrace('taskToWork::auditTasksOperation', 'START', array(
				'workId' => $workId,
				'operation' => $operation,
				'beforeCount' => count($tasksBefore),
				'afterCount' => count($tasksAfter),
			));
			require_once('data/CRMEntity.php');
			require_once('data/CrmEntityUtils.php');
			
			try {
				global $current_user;
				
				// Obtener datos antiguos del trabajo
				$workEntity = CRMEntity::getInstance('orden_de_trabajo');
				$workEntity->id = $workId;
				$workEntity->mode = 'edit';
				$workEntity->retrieve_entity_info($workId, 'orden_de_trabajo');
				$oldData = $workEntity->column_fields;
				
				// Normalizar estructura de diferencias
				$taskDifferences = array_merge(
					array(
						'added' => array(),
						'removed' => array(),
						'updated' => array(),
					),
					is_array($taskDifferences) ? $taskDifferences : array()
				);
				
				$countBefore = count($tasksBefore);
				$countAfter = count($tasksAfter);
				
				$this->logAuditComparison('AUDIT_OPERATION_INPUT', array(
					'workId' => $workId,
					'operation' => $operation,
					'tasksBefore' => $tasksBefore,
					'tasksAfter' => $tasksAfter,
					'taskDifferences' => $taskDifferences,
					'workFieldChanges' => $workFieldChanges,
				));
				
				$parts = array();
				$parts[] = "{$operation}";
				$parts[] = "Total tareas: {$countBefore} → {$countAfter}";
				
				if (!empty($taskDifferences['added'])) {
					$parts[] = 'Agregadas (' . count($taskDifferences['added']) . '): ' . implode(', ', $taskDifferences['added']);
				}
				if (!empty($taskDifferences['removed'])) {
					$parts[] = 'Eliminadas (' . count($taskDifferences['removed']) . '): ' . implode(', ', $taskDifferences['removed']);
				}
				if (!empty($taskDifferences['updated'])) {
					$formatted = array();
					foreach ($taskDifferences['updated'] as $item) {
						$line = '"' . $item['subject'] . '"';
						if (!empty($item['changes'])) {
							$line .= ' [' . implode('; ', $item['changes']) . ']';
						}
						$formatted[] = $line;
					}
					$parts[] = 'Modificadas (' . count($taskDifferences['updated']) . '): ' . implode(', ', $formatted);
				}
				
				if (!empty($workFieldChanges)) {
					$parts[] = 'Campos del trabajo: ' . implode('; ', $workFieldChanges);
				}
				
				$message = implode(' | ', $parts) . ' (Tarea)';
			if (mb_strlen($message) > 60000) {
				$message = mb_substr($message, 0, 59990) . '...(truncado)';
			}
			
			$this->logAuditComparison('AUDIT_OPERATION_MESSAGE', array(
				'workId' => $workId,
				'message' => $message,
			));
			
			// Actualizar modifiedtime
			$today = date('Y-m-d H:i:s');
			$this->adb->pquery(
				'UPDATE vtiger_crmentity SET modifiedtime=?, modifiedby=? WHERE crmid=?',
				array($today, $current_user->id, $workId)
			);
			
			// Obtener fieldid del campo task_work y tabid del módulo orden_de_trabajo
			$fieldResult = $this->adb->pquery(
				'SELECT f.fieldid, t.tabid 
				 FROM vtiger_field f
				 INNER JOIN vtiger_tab t ON t.tabid = f.tabid
				 WHERE t.name = ? AND f.fieldname = ?',
				array('orden_de_trabajo', 'task_work')
			);
			
			if ($this->adb->num_rows($fieldResult) > 0) {
				$fieldId = $this->adb->query_result($fieldResult, 0, 'fieldid');
				$tabId = $this->adb->query_result($fieldResult, 0, 'tabid');
				
				// Insertar en vtiger_crmentityutils (histórico visible al usuario)
				$this->adb->pquery(
					'INSERT INTO vtiger_crmentityutils (module, field, oldvalue, newvalue, modifiedby, modifiedon, registryid, date)
					 VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
					array($tabId, $fieldId, '', $message, $current_user->id, 1, $workId, $today)
				);
			}
				
				$this->logFunctionTrace('taskToWork::auditTasksOperation', 'END', array(
					'workId' => $workId,
					'message' => $message,
				));
			} catch (Exception $e) {
				$this->logFunctionTrace('taskToWork::auditTasksOperation', 'ERROR', array('error' => $e->getMessage()));
			}
		}
		
		/**
		 * Registra auditoría de operaciones individuales de tareas
		 * @param integer $workId
		 * @param array $taskData
		 * @param array|null $oldTaskData
		 */
		private function auditIndividualTask($workId, $taskData, $oldTaskData = null) {
			$this->logFunctionTrace('taskToWork::auditIndividualTask', 'START', array(
				'workId' => $workId,
				'taskId' => isset($taskData['id']) ? $taskData['id'] : null,
			));
			require_once('data/CRMEntity.php');
			require_once('data/CrmEntityUtils.php');
			
			try {
				global $current_user;
				
				// Obtener datos antiguos del trabajo
				$workEntity = CRMEntity::getInstance('orden_de_trabajo');
				$workEntity->id = $workId;
				$workEntity->mode = 'edit';
				$workEntity->retrieve_entity_info($workId, 'orden_de_trabajo');
				$oldData = $workEntity->column_fields;
				
				// Construir mensaje de auditoría
				if ($oldTaskData === null) {
					// Nueva tarea
					$message = "Tarea agregada: \"{$taskData['subject']}\" (Tarea)";
				} else {
					// Tarea modificada
					$changes = $this->getTaskChanges($oldTaskData, $taskData);
					if (!empty($changes)) {
						$message = "Tarea \"{$taskData['subject']}\" - Cambios: " . implode(', ', $changes) . " (Tarea)";
					} else {
						return; // No hay cambios significativos
					}
				}
				
				// Registrar auditoría
				$newData = $oldData;
				$newData['tareas_trabajo'] = $message;
				
				// Actualizar modifiedtime
				$today = date('Y-m-d H:i:s');
				$this->adb->pquery(
					'UPDATE vtiger_crmentity SET modifiedtime=?, modifiedby=? WHERE crmid=?',
					array($today, $current_user->id, $workId)
				);
				
				CrmEntityUtils::audit($this->adb, $workId, 'orden_de_trabajo', $oldData, $newData, $current_user->id);
				
				$this->logFunctionTrace('taskToWork::auditIndividualTask', 'END', array('message' => $message));
			} catch (Exception $e) {
				$this->logFunctionTrace('taskToWork::auditIndividualTask', 'ERROR', array('error' => $e->getMessage()));
			}
		}
		
		/**
		 * Compara datos de tarea para identificar cambios
		 * @param array $oldData
		 * @param array $newData
		 * @return array
		 */
		private function getTaskChanges($oldData, $newData) {
			$this->logFunctionTrace('taskToWork::getTaskChanges', 'START', array(
				'oldTaskId' => isset($oldData['id']) ? $oldData['id'] : null,
				'newTaskId' => isset($newData['id']) ? $newData['id'] : null,
			));
			$changes = array();
			
			// Comparar campos relevantes
			$fieldsToCompare = array(
				'subject' => 'Asunto',
				'date_start' => 'Fecha inicio',
				'due_date' => 'Fecha fin',
				'status' => 'Estado',
				'progress' => '% avance',
				'assigned_name' => 'Asignado',
				'supplier_name' => 'Proveedor'
			);
			
			foreach ($fieldsToCompare as $field => $label) {
				if (isset($oldData[$field]) && isset($newData[$field])) {
					$oldValue = $oldData[$field];
					$newValue = $newData[$field];
					
					// Comparación normalizada para evitar falsos cambios
					$normalizedOld = $this->normalizeValueForComparison($oldValue, $field);
					$normalizedNew = $this->normalizeValueForComparison($newValue, $field);
					
					$this->logAuditComparison('TASK_FIELD_COMPARE', array(
						'field' => $field,
						'label' => $label,
						'rawOld' => $oldValue,
						'rawNew' => $newValue,
						'normalizedOld' => $normalizedOld,
						'normalizedNew' => $normalizedNew,
						'taskOldId' => isset($oldData['id']) ? $oldData['id'] : null,
						'taskNewId' => isset($newData['id']) ? $newData['id'] : null,
					));
					
					if ($normalizedOld != $normalizedNew) {
						// Formatear valores para mejor legibilidad SOLO si hay cambio real
						if ($field == 'progress') {
							$oldValue = number_format(floatval($oldValue), 0) . '%';
							$newValue = number_format(floatval($newValue), 0) . '%';
						}
						
						$changes[] = "{$label}: {$oldValue} → {$newValue}";
						
						$this->logAuditComparison('TASK_FIELD_CHANGE_DETECTED', array(
							'field' => $field,
							'label' => $label,
							'formattedOld' => $oldValue,
							'formattedNew' => $newValue,
							'taskOld' => $oldData,
							'taskNew' => $newData,
						));
					}
				}
			}
			
			if (!empty($changes)) {
				$this->logAuditComparison('TASK_CHANGES_RESULT', array(
					'oldTask' => $oldData,
					'newTask' => $newData,
					'changes' => $changes,
				));
			}
			
			$this->logFunctionTrace('taskToWork::getTaskChanges', 'END', array('changesCount' => count($changes)));
			return $changes;
		}
		
		/**
		 * Normaliza valores para comparación precisa
		 * @param mixed $value
		 * @param string $field
		 * @return mixed
		 */
		private function normalizeValueForComparison($value, $field) {
			$this->logFunctionTrace('taskToWork::normalizeValueForComparison', 'START', array(
				'field' => $field,
				'valuePreview' => is_scalar($value) ? substr((string)$value, 0, 50) : gettype($value),
			));
			if ($value === null || $value === '') {
				$this->logFunctionTrace('taskToWork::normalizeValueForComparison', 'END_EMPTY');
				return '';
			}
			
			// Normalizar campos de fecha del trabajo
			if (in_array($field, array('fecha_de_inicio', 'fecha_prevista', 'fecha_estim_fin', 'fecha_de_emision', 'date_start', 'due_date'))) {
				$timestamp = strtotime($value);
				if ($timestamp !== false) {
					$result = date('Y-m-d', $timestamp);
					$this->logFunctionTrace('taskToWork::normalizeValueForComparison', 'END_DATE', array('result' => $result));
					return $result;
				}
			}
			
			// Normalizar campos numéricos del trabajo y tareas
			if (in_array($field, array('progress', 'numero_unidades_planificadas', 'work_estimated_cost', 'overall_progress_perc', 'expected_work_progress', 'priority_index', 'unidades_consumidas'))) {
				$result = floatval($value);
				$this->logFunctionTrace('taskToWork::normalizeValueForComparison', 'END_NUMERIC', array('result' => $result));
				return $result;
			}
			
			// Para otros campos, usar trim y comparación directa
			$result = trim((string)$value);
			$this->logFunctionTrace('taskToWork::normalizeValueForComparison', 'END_STRING', array('resultPreview' => substr($result, 0, 50)));
			return $result;
		}
		
		/**
		 * Registra auditoría detallada de nuevas tareas agregadas
		 * @param integer $workId
		 * @param array $newTasks
		 */
		private function auditNewTasksDetails($workId, $newTasks) {
			require_once('data/CRMEntity.php');
			require_once('data/CrmEntityUtils.php');
			
			try {
				global $current_user;
				
				// Obtener datos antiguos del trabajo
				$workEntity = CRMEntity::getInstance('orden_de_trabajo');
				$workEntity->id = $workId;
				$workEntity->mode = 'edit';
				$workEntity->retrieve_entity_info($workId, 'orden_de_trabajo');
				$oldData = $workEntity->column_fields;
				
				// Construir mensaje con detalles de tareas
				$taskDetails = array();
				foreach ($newTasks as $task) {
					$taskDetails[] = "\"{$task['subject']}\"";
				}
				
				$message = "Tareas agregadas: " . implode(', ', $taskDetails) . " (Tarea)";
				
				// Registrar auditoría
				$newData = $oldData;
				$newData['tareas_trabajo'] = $message;
				
				// Actualizar modifiedtime
				$today = date('Y-m-d H:i:s');
				$this->adb->pquery(
					'UPDATE vtiger_crmentity SET modifiedtime=?, modifiedby=? WHERE crmid=?',
					array($today, $current_user->id, $workId)
				);
				
				CrmEntityUtils::audit($this->adb, $workId, 'orden_de_trabajo', $oldData, $newData, $current_user->id);
			} catch (Exception $e) {
				// Error silencioso
			}
		}
		
		/**
		 * Helper para registrar comparaciones detalladas en el log
		 * @param string $label
		 * @param array $data
		 * @return void
		 */
		private function logAuditComparison($label, $data = array()) {
			$payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			if ($payload === false) {
				$payload = '[json_encode_failed:' . json_last_error_msg() . ']';
			}
			// Método deshabilitado
		}
		
		/**
		 * Helper para registrar la traza de entrada/salida de cada método
		 * @param string $method
		 * @param string $stage
		 * @param array $data
		 * @return void
		 */
		private function logFunctionTrace($method, $stage, $data = array()) {
			$filePath = 'modules/orden_de_trabajo/handlers/taskToWork.class.php';
			$payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			if ($payload === false) {
				$payload = '[json_encode_failed:' . json_last_error_msg() . ']';
			}
			// Método deshabilitado
		}
		
		/**
		 * Verifica las relaciones de una tarea para determinar si puede ser eliminada lógicamente
		 * @param integer $activityId ID de la tarea
		 * @param integer $currentWorkId ID del trabajo actual (para excluirlo del conteo)
		 * @return array Array con información de relaciones: ['canDelete' => bool, 'relations' => array, 'warnings' => array]
		 */
		public function checkTaskRelations($activityId, $currentWorkId = null) {
			if (empty($activityId)) {
				return array(
					'canDelete' => false,
					'relations' => array(),
					'warnings' => array('ID de tarea inválido')
				);
			}
			
			$relations = array();
			$warnings = array();
			
			// 1. Verificar relaciones con otros registros (excluyendo el trabajo actual)
			$sql = 'SELECT sar.crmid, ce.setype
					FROM vtiger_seactivityrel sar
					INNER JOIN vtiger_crmentity ce ON ce.crmid = sar.crmid AND ce.deleted = 0
					WHERE sar.activityid = ?';
			$params = array($activityId);
			
			if (!empty($currentWorkId)) {
				$sql .= ' AND sar.crmid != ?';
				$params[] = $currentWorkId;
			}
			
			$result = $this->adb->pquery($sql, $params);
			if ($this->adb->num_rows($result) > 0) {
				$otherRelations = array();
				while ($row = $this->adb->fetchByAssoc($result, -1, false)) {
					// Obtener el label usando getEntityName
					$entityNames = getEntityName($row['setype'], $row['crmid']);
					$label = isset($entityNames[$row['crmid']]) ? $entityNames[$row['crmid']] : 'ID: ' . $row['crmid'];
					
					$otherRelations[] = array(
					'id' => $row['crmid'],
					'type' => $row['setype'],
					'label' => $label
				);
			}
			if (!empty($otherRelations)) {
				$relations['other_records'] = $otherRelations;
				$warnings[] = 'La tarea está relacionada con ' . count($otherRelations) . ' registro(s) adicional(es)';
			}
		}
		DatabaseUtils::closeResult($result);
		
		// 2. Verificar reportes de avance (BLOQUEAN eliminación)
		$result = $this->adb->pquery(
			'SELECT COUNT(*) as report_count FROM vtiger_activity_report WHERE activityid = ? AND deleted = 0',
			array($activityId)
		);
		$reportCount = 0;
		if ($this->adb->num_rows($result) > 0) {
			$reportCount = (int)$this->adb->query_result($result, 0, 'report_count');
		}
		DatabaseUtils::closeResult($result);
		
		if ($reportCount > 0) {
			$relations['activity_reports'] = $reportCount;
			$warnings[] = 'La tarea tiene ' . $reportCount . ' reporte(s) de avance y no puede ser eliminada';
		}
		
		// 3. Verificar relación con proveedor ejecutor (NO impide eliminación, solo informativo)
		$supplierName = null;
		$result = $this->adb->pquery(
			'SELECT srel.proveedoresid, prov.alias
			 FROM vtiger_supplieractivityrel srel
			 INNER JOIN vtiger_proveedores prov ON prov.proveedoresid = srel.proveedoresid
			 WHERE srel.activityid = ?',
			array($activityId)
		);
		if ($this->adb->num_rows($result) > 0) {
			$row = $this->adb->fetchByAssoc($result, -1, false);
			$supplierName = $row['alias'];
		}
		DatabaseUtils::closeResult($result);
		
		// Determinar si puede ser eliminada/desvinculada
		$hasReports = $reportCount > 0;
		$hasOtherRecords = !empty($relations['other_records']);
		$canDelete = !$hasOtherRecords && !$hasReports;
		$canUnlink = !$hasReports;
		
		return array(
			'canDelete' => $canDelete,
			'canUnlink' => $canUnlink,
			'hasReports' => $hasReports,
			'hasOtherRecords' => $hasOtherRecords,
			'supplierName' => $supplierName,
			'relations' => $relations,
			'warnings' => $warnings
		);
	}
	
	public function deleteTaskFromWork($activityId, $workId) {
		if (empty($activityId) || empty($workId)) {
			return array(
				'success' => false,
				'message' => 'Parámetros inválidos'
			);
		}
		
		$relationCheck = $this->checkTaskRelations($activityId, $workId);
		
		if ($relationCheck['hasReports']) {
			return array(
				'success' => false,
				'message' => 'No se puede eliminar una tarea que tiene reportes de avance',
				'blocked' => true,
				'reason' => 'reports'
			);
		}
		
		$this->adb->pquery(
			'DELETE FROM vtiger_seactivityrel WHERE crmid=? AND activityid=?',
			array($workId, $activityId)
		);
		
		$message = 'Relación con el trabajo eliminada';
		$logicallyDeleted = false;
		
		if ($relationCheck['canDelete']) {
			$this->adb->pquery(
				'UPDATE vtiger_crmentity SET deleted=1 WHERE crmid=?',
				array($activityId)
			);
			$message = 'Tarea eliminada completamente del sistema';
			$logicallyDeleted = true;
		}
		
		return array(
			'success' => true,
			'message' => $message,
			'logicallyDeleted' => $logicallyDeleted,
			'hasOtherRecords' => $relationCheck['hasOtherRecords'],
			'relations' => $relationCheck['relations'],
			'warnings' => $relationCheck['warnings']
		);
	}
}
