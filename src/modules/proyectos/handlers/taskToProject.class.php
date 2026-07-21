<?php
	require_once ('data/CRMEntity.php');
	require_once ('data/CrmEntityUtils.php');
	require_once ('Smarty_setup.php');
	require_once ('include/fields/DateTimeField.php');
	require_once ('include/QueryGenerator/QueryGenerator.php');
	require_once ('include/platzilla/Data/EntityHistoryManager.php');
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/platzilla/Managers/UserManager.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/DataViewUtils.php');
	require_once ('include/utils/NumberHelper.class.php');
	require_once ('include/database/PearDatabase_Fix.php');
	require_once ('modules/Calendar/Activity.php');
	require_once ('modules/etapas_proyecto/etapas_proyecto.php');
	require_once ('modules/proyectos/Objects/ProjectWorks.php');
	require_once ('modules/proyectos/handlers/ProjectEstimatedProgressManager.class.php');
	require_once ('modules/orden_de_trabajo/handlers/taskToWork.class.php');
	class taskToProject {
		
		const JOB_RELATED_MODULE = 'orden_de_trabajo';
		
		/** @var PearDatabase */
		protected $masterAdb;
		
		/** @var PearDatabase */
		protected $adb;
		
		/**
		 * taskToProject constructor.
		 * @param PearDatabase $adb
		 */
		public function __construct ($adb) {
			$this->masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			$this->adb       = $adb;
		}
		
		/**
		 * @param integer $crmId
		 */
		private function delRelatedWorks ($crmId) {
			if (empty ($crmId)) {
				return;
			}
			$this->adb->pquery ('DELETE FROM vtiger_project_works WHERE crmid=?', array ($crmId));
			DatabaseUtils::closeResult ($result);
		}
		
		/**
		 * @param $currentUser
		 *
		 * @return stdClass[]|null
		 * @throws Exception
		 */
		public function getProjectStages ($currentUser) {
			if (empty ($currentUser)) {
				return null;
			}
			
			$selectClause   = 'SELECT vtiger_etapas_proyecto.etapas_proyectoid, vtiger_etapas_proyecto.titulo';
			$queryGenerator = new QueryGenerator ('etapas_proyecto', $currentUser);
			$sql            = str_replace ('SELECT ',$selectClause, $queryGenerator->getQuery ());
			$result         = $this->adb->query ($sql);
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$projectStage        = new stdClass ();
					$projectStage->id    = $row ['etapas_proyectoid'];
					$projectStage->stage = $row ['titulo'];
					$projectStages []    = (object) $projectStage;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($projectStages)) ? $projectStages : null;
		}
		
		/**
		 * @param ProjectWorks[] $jobsRelated
		 *
		 * @return array
		 */
		private function getRelatedStage ($jobsRelated) {
			if (empty ($jobsRelated)) {
				return array();
			}
			$relatedStage = array ();
			foreach ($jobsRelated as $jobRelated) {
				if (!in_array ($jobRelated->getStageId (), $relatedStage)) {
					$relatedStage [] = $jobRelated->getStageId ();
				}
			}
			return $relatedStage;
		}
		
		/**
		 * @param ProjectWorks $projectWork
		 *
		 * @throws Exception
		 * @throws WebServiceException
		 */
		private function updateJobRecord ($projectWork) {
			$jobEntity       = CRMEntity::getInstance (self::JOB_RELATED_MODULE);
			$jobEntity->id   = $projectWork->getCrmIdJob ();
			$jobEntity->mode = 'edit';
			$jobEntity->retrieve_entity_info ($projectWork->getCrmIdJob (), self::JOB_RELATED_MODULE);
			
			// Obtener datos del proyecto
			$projectEntity = CRMEntity::getInstance ('proyectos');
			$projectEntity->retrieve_entity_info ($projectWork->getCrmId (), 'proyectos');
			
			// Actualizar fechas del trabajo
			$jobEntity->column_fields ['fecha_de_inicio']       = $projectWork->getStartDate ();
			$jobEntity->column_fields ['fecha_prevista']        = $projectWork->getStartDate ();
			$jobEntity->column_fields ['estimated_due_date']    = $projectWork->getEstimatedDueDate ();
			$jobEntity->column_fields ['overall_progress_perc'] = $projectWork->getPercentageCompletion ();
			
			// Asignar fecha de emisión si está vacía (trabajo nuevo desde proyecto)
			if (empty($jobEntity->column_fields ['fecha_de_emision'])) {
				$jobEntity->column_fields ['fecha_de_emision'] = date('Y-m-d');
			}
			
			// Prellenar campos desde el proyecto (solo si están vacíos)
			if (empty($jobEntity->column_fields ['asociar_a'])) {
				$this->setFieldIfExists ($jobEntity, 'asociar_a', 'Proyecto');
				$this->setFieldIfExists ($jobEntity, 'proyecto', $projectWork->getCrmId ());
			}
			
			// Cliente del proyecto
			$this->setFieldIfExists ($jobEntity, 'cliente', 
				isset($projectEntity->column_fields['cliente']) ? $projectEntity->column_fields['cliente'] : null);
			
			// Tipo de actividad
			$this->setFieldIfExists ($jobEntity, 'tipo_dactividad', 'Trabajo de proyecto');
			
			// Importancia del trabajo
			$this->setFieldIfExists ($jobEntity, 'importance_work', 'Alta');
			
			// Prioridad del trabajo
			$this->setFieldIfExists ($jobEntity, 'work_priority', 'Según planificación');
			
			// Dirección, Ciudad, Código Postal y País desde el proyecto
			$this->setFieldIfExists ($jobEntity, 'direccion', 
				isset($projectEntity->column_fields['direccion']) ? $projectEntity->column_fields['direccion'] : null);
			$this->setFieldIfExists ($jobEntity, 'ciudad', 
				isset($projectEntity->column_fields['ciudad']) ? $projectEntity->column_fields['ciudad'] : null);
			$this->setFieldIfExists ($jobEntity, 'codigo_postal', 
				isset($projectEntity->column_fields['codigo_postal']) ? $projectEntity->column_fields['codigo_postal'] : null);
			$this->setFieldIfExists ($jobEntity, 'pai', 
				isset($projectEntity->column_fields['pai']) ? $projectEntity->column_fields['pai'] : null);
			
			// Estado del trabajo
			$this->setFieldIfExists ($jobEntity, 'estado_de_la_orden', 'Creado');
			
			$jobEntity->save (self::JOB_RELATED_MODULE);
			$this->adb->pquery ('INSERT INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule) VALUES (?, ?, ?, ?)',
				array ($jobEntity->id, self::JOB_RELATED_MODULE, $projectWork->getCrmId (), 'proyectos')
			);
			// Actualizar la tarea asociada al trabajo con las fechas correctas
			$this->updateJobTask ($projectWork->getCrmIdJob (), $projectWork->getStartDate (), $projectWork->getEstimatedDueDate ());
			
			// NOTA: work_situation NO se recalcula aquí porque retrieve_entity_info ya cargó el valor
			// correcto desde BD, y save() lo persiste. Llamar al SP sobreescribiría ese valor
			// con una lógica simplificada (Sin Tareas/Completado/En Progreso/Pendiente) que es
			// incompatible con la lógica real de calculateWorkSituation() en taskToWork.
			
			// Actualizar progreso esperado del proyecto (desactivado temporalmente por error 1442)
			// try {
			// 	if (class_exists('ProjectEstimatedProgressManager')) {
			// 		$progressManager = ProjectEstimatedProgressManager::getInstance($this->adb);
			// 		$progressManager->onWorkSave($projectWork->getCrmIdJob());
			// 	}
			// } catch (Exception $e) {
			// 	// Log error pero no interrumpir el proceso
			// 	error_log("Error updating project estimated progress in updateJobRecord: " . $e->getMessage());
			// }
			
			unset ($jobEntity);
			unset ($projectEntity);
		}
		
		/**
		 * @param integer $jobCrmId
		 * @param string $startDate
		 * @param string $dueDate
		 */
		private function updateJobTask ($jobCrmId, $startDate, $dueDate) {
			if (empty ($jobCrmId) || empty ($startDate) || empty ($dueDate)) {
				return;
			}
			
			global $current_user;
			$startDateDb = $this->convertProjectDateToDbFormat($startDate, $current_user);
			$dueDateDb   = $this->convertProjectDateToDbFormat($dueDate, $current_user);
			
			if (empty($startDateDb) || empty($dueDateDb)) {
				return;
			}
			
			// Buscar la tarea asociada al trabajo
			$result = $this->adb->pquery (
				'SELECT activityid FROM vtiger_seactivityrel WHERE crmid=?',
				array ($jobCrmId)
			);
			
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					// UPDATE directo: evita CRMEntity::save() completo que genera importance='' en vtiger_activity
					$this->adb->pquery(
						'UPDATE vtiger_activity SET date_start=?, due_date=? WHERE activityid=?',
						array($startDateDb, $dueDateDb, $row['activityid'])
					);
				}
			}
			DatabaseUtils::closeResult ($result);
		}
		
		/**
		 * Convierte una fecha de display (formato usuario) al formato DB (Y-m-d).
		 *
		 * @param string $dateValue
		 * @param Users $currentUser
		 *
		 * @return string
		 */
		private function convertProjectDateToDbFormat ($dateValue, $currentUser) {
			if (empty ($dateValue) || $dateValue === '0000-00-00') {
				return '';
			}
			if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateValue)) {
				return $dateValue;
			}
			return DateTimeField::convertToDBFormat($dateValue, $currentUser);
		}
		
		/**
		 * Asigna un valor a un campo de la entidad solo si el campo existe en la estructura
		 * Evita errores si el campo no existe en la base de datos
		 * 
		 * @param CRMEntity $entity
		 * @param string $fieldName
		 * @param mixed $value
		 */
		private function setFieldIfExists ($entity, $fieldName, $value) {
			try {
				// Solo asignar si el valor no es null
				if ($value !== null) {
					$entity->column_fields[$fieldName] = $value;
				}
			} catch (Exception $e) {
				// Si el campo no existe, simplemente no hacer nada
				// No generar error para mantener compatibilidad
			}
		}
		
		/**
		 * @param NumberHelper $numberingFormat
		 * @param float $totalProgress
		 * @param ProjectWorks[] $projectWorks
		 */
		private function updateSummaryRow ($numberingFormat, $totalProgress, &$projectWorks) {
			if (!$totalProgress) {
				return;
			}
			foreach ($projectWorks as $projectWork) {
				if (!$projectWork instanceof ProjectWorks) {
					continue;
				}
				if (!empty ($projectWork->getSummaryRow ())) {
					$summaryRow                      = $projectWork->getSummaryRow ();
					$summaryRow ['project_progress'] = $totalProgress;
					$summaryRow ['job_contribution_factor'] = $numberingFormat->setNumberFormat ($summaryRow ['job_contribution_factor']);
					$summaryRow ['project_progress']		= $numberingFormat->setNumberFormat ($summaryRow ['project_progress']);
					$projectWork->setSummaryRow (json_encode ($summaryRow));
					$projectWork->setSummaryStr ($summaryRow);
					break;
				}
			}
		}
		
		/**
		 * @param integer $crmId
		 *
		 * @return ProjectWorks[]|null
		 * @throws Exception
		 */
		public function fetchRelatedWork ($crmId, $currentUser, $view) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_project_works WHERE crmid=?', array ($crmId));
			$numRows = $this->adb->num_rows($result);
			if ($numRows > 0) {
				$projectsJobs    = array ();
				$summaryProgress = 0;
				$numberingHelper = NumberHelper::getInstance ($this->adb, $currentUser);
				$stages          = $this->getProjectStages ($currentUser);
				$jobEntity       = CRMEntity::getInstance (self::JOB_RELATED_MODULE);
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					// Verificar que el trabajo exista y no esté eliminado
					$jobCrmId = intval($row['crmid_job']);
					$checkQuery = $this->adb->pquery('SELECT deleted FROM vtiger_crmentity WHERE crmid = ?', array($jobCrmId));
					if ($this->adb->num_rows($checkQuery) == 0) {
						continue; // El trabajo no existe, saltar
					}
					$checkRow = $this->adb->fetchByAssoc($checkQuery);
					if ($checkRow['deleted'] == 1) {
						continue; // El trabajo fue eliminado, saltar
					}
					
					$jobEntity->id   = $row['crmid_job'];
					$jobEntity->mode = 'edit';
					$jobEntity->retrieve_entity_info ($row['crmid_job'], self::JOB_RELATED_MODULE);
					$projectProgress    =  ((floatval ($row['job_contribution_factor']) * floatval ($jobEntity->column_fields ['overall_progress_perc'])) / 100);
					$summaryProgress   += $projectProgress;
					$contributionFactor = $numberingHelper->setNumberFormat ($row['job_contribution_factor']);
					$projectProgress    = $numberingHelper->setNumberFormat ($projectProgress, 'overall_progress_perc');
					$overallProgress	= $numberingHelper->setNumberFormat ($jobEntity->column_fields ['overall_progress_perc'], 'overall_progress_perc');
					$workEstimatedCostRaw = isset($jobEntity->column_fields ['work_estimated_cost']) ? floatval($jobEntity->column_fields ['work_estimated_cost']) : 0;
					$workEstimatedCost	= $numberingHelper->setNumberFormat ($workEstimatedCostRaw, 'work_estimated_cost');
					$costWorkPerformedRaw = isset($jobEntity->column_fields ['cost_work_performed']) ? floatval($jobEntity->column_fields ['cost_work_performed']) : 0;
					$costWorkPerformed	= $numberingHelper->setNumberFormat ($costWorkPerformedRaw, 'cost_work_performed');
				
				// Obtener fechas estimadas directamente de vtiger_orden_de_trabajo
				$startDateDb = isset($jobEntity->column_fields['fecha_prevista']) ? $jobEntity->column_fields['fecha_prevista'] : '';
				$dueDateDb = isset($jobEntity->column_fields['fecha_estim_fin']) ? $jobEntity->column_fields['fecha_estim_fin'] : '';
				
				// Obtener situación del trabajo
				$workSituation = isset($jobEntity->column_fields['work_situation']) ? $jobEntity->column_fields['work_situation'] : '';
				
				// Convertir fechas al formato del usuario
				$startDate = '';
				$estimatedDueDate = '';
				if (!empty($startDateDb) && $startDateDb !== '0000-00-00') {
					$startDate = DateTimeField::convertToUserFormat($startDateDb, $currentUser);
				}
				if (!empty($dueDateDb) && $dueDateDb !== '0000-00-00') {
					$estimatedDueDate = DateTimeField::convertToUserFormat($dueDateDb, $currentUser);
				}
					
					$projectsJobs [] = ProjectWorks::getInstance ()
					->setCrmId (intval ($row['crmid']))
					->setCrmIdJob (intval ($row['crmid_job']))
					->setEstimatedDueDate ($estimatedDueDate)
					->setId ($row ['projectworksid'])
					->setJobContributionFactor ($contributionFactor)
					->setJobName ($row['job_name'])
					->setPercentageCompletion ($overallProgress)
					->setProjectProgress ($projectProgress)
					->setWorkEstimatedCost ($workEstimatedCost)
					->setWorkEstimatedCostRaw ($workEstimatedCostRaw)
					->setCostWorkPerformed ($costWorkPerformed)
					->setCostWorkPerformedRaw ($costWorkPerformedRaw)
					->setResponsibleJob (intval ($jobEntity->column_fields ['assigned_user_id']))
					->setResponsibleJobName (getUserFullName ($jobEntity->column_fields ['assigned_user_id']))
					->setStageId (intval ($row['stageid']))
					->setStageName ($row['stageid'], $stages)
					->setStartDate ($startDate)
					->setWorkSituation ($workSituation)
					->setSummaryRow ($row['summaryrow']);
				}
				$this->updateSummaryRow ($numberingHelper, $summaryProgress, $projectsJobs);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($projectsJobs)) ? $projectsJobs : null;
		}
		
		/**
		 * @param int $projectId
		 *
		 * @return float
		 */
		private function getTotalWorkEstimatedCostForProject ($projectId) {
			if (empty($projectId)) {
				return 0;
			}
			$total = 0;
			$result = $this->adb->pquery(
				'SELECT COALESCE(SUM(ot.work_estimated_cost), 0) AS total
				   FROM vtiger_project_works pw
				   INNER JOIN vtiger_orden_de_trabajo ot ON ot.orden_de_trabajoid = pw.crmid_job
				   INNER JOIN vtiger_crmentity ce ON ce.crmid = ot.orden_de_trabajoid AND ce.deleted = 0
				  WHERE pw.crmid = ?',
				array($projectId)
			);
			if ($this->adb->num_rows($result) > 0) {
				$row = $this->adb->fetchByAssoc($result);
				$total = floatval($row['total']);
			}
			DatabaseUtils::closeResult($result);
			return $total;
		}
		
		/**
		 * @param int $projectId
		 *
		 * @return float
		 */
		public function getTotalCostWorkPerformedForProject ($projectId) {
			if (empty($projectId)) {
				return 0;
			}
			$total = 0;
			$result = $this->adb->pquery(
				'SELECT COALESCE(SUM(ot.cost_work_performed), 0) AS total
				   FROM vtiger_project_works pw
				   INNER JOIN vtiger_orden_de_trabajo ot ON ot.orden_de_trabajoid = pw.crmid_job
				   INNER JOIN vtiger_crmentity ce ON ce.crmid = ot.orden_de_trabajoid AND ce.deleted = 0
				  WHERE pw.crmid = ?',
				array($projectId)
			);
			if ($this->adb->num_rows($result) > 0) {
				$row = $this->adb->fetchByAssoc($result);
				$total = floatval($row['total']);
			}
			DatabaseUtils::closeResult($result);
			return $total;
		}
		
		/**
		 * Actualiza vtiger_proyectos.costo_total_estimad con la regla:
		 * max(total_costos_trabajos, valor_usuario)
		 *
		 * @param int $projectId
		 */
		private function updateProjectEstimatedCost ($projectId) {
			if (empty($projectId)) {
				return;
			}
			$totalCosts = $this->getTotalWorkEstimatedCostForProject($projectId);
			$projectResult = $this->adb->pquery(
				'SELECT costo_total_estimad FROM vtiger_proyectos WHERE proyectosid = ?',
				array($projectId)
			);
			$userProposed = 0;
			if ($this->adb->num_rows($projectResult) > 0) {
				$row = $this->adb->fetchByAssoc($projectResult);
				$userProposed = floatval($row['costo_total_estimad']);
			}
			DatabaseUtils::closeResult($projectResult);
			$newValue = ($userProposed > $totalCosts) ? $userProposed : $totalCosts;
			$this->adb->pquery(
				'UPDATE vtiger_proyectos SET costo_total_estimad = ? WHERE proyectosid = ?',
				array($newValue, $projectId)
			);
		}
		
		public function recalculateProjectEstimatedCost ($projectId) {
			$this->updateProjectEstimatedCost($projectId);
			$this->updateProjectSituation($projectId);
		}
		
		private function getTotalWorkEstimatedEffortForProject ($projectId) {
			if (empty($projectId)) {
				return 0;
			}
			$total = 0;
			$result = $this->adb->pquery(
				'SELECT COALESCE(SUM(ot.numero_unidades_planificadas), 0) AS total
				   FROM vtiger_project_works pw
				   INNER JOIN vtiger_orden_de_trabajo ot ON ot.orden_de_trabajoid = pw.crmid_job
				   INNER JOIN vtiger_crmentity ce ON ce.crmid = ot.orden_de_trabajoid AND ce.deleted = 0
				  WHERE pw.crmid = ?',
				array($projectId)
			);
			if ($this->adb->num_rows($result) > 0) {
				$row = $this->adb->fetchByAssoc($result);
				$total = floatval($row['total']);
			}
			DatabaseUtils::closeResult($result);
			return $total;
		}
		
		private function updateProjectEstimatedEffort ($projectId) {
			if (empty($projectId)) {
				return;
			}
			$totalEfforts = $this->getTotalWorkEstimatedEffortForProject($projectId);
			$projectResult = $this->adb->pquery(
				'SELECT esfuerzo_total_estimad FROM vtiger_proyectos WHERE proyectosid = ?',
				array($projectId)
			);
			$userProposed = 0;
			if ($this->adb->num_rows($projectResult) > 0) {
				$row = $this->adb->fetchByAssoc($projectResult);
				$userProposed = floatval($row['esfuerzo_total_estimad']);
			}
			DatabaseUtils::closeResult($projectResult);
			$newValue = ($userProposed > $totalEfforts) ? $userProposed : $totalEfforts;
			$this->adb->pquery(
				'UPDATE vtiger_proyectos SET esfuerzo_total_estimad = ? WHERE proyectosid = ?',
				array($newValue, $projectId)
			);
		}
		
		public function recalculateProjectEstimatedEffort ($projectId) {
			$this->updateProjectEstimatedEffort($projectId);
		}
		
		private function getTotalWorkPerformedEffortForProject ($projectId) {
			if (empty($projectId)) {
				return 0;
			}
			$total = 0;
			$result = $this->adb->pquery(
				'SELECT COALESCE(SUM(ot.unidades_consumidas), 0) AS total
				   FROM vtiger_project_works pw
				   INNER JOIN vtiger_orden_de_trabajo ot ON ot.orden_de_trabajoid = pw.crmid_job
				   INNER JOIN vtiger_crmentity ce ON ce.crmid = ot.orden_de_trabajoid AND ce.deleted = 0
				  WHERE pw.crmid = ?',
				array($projectId)
			);
			if ($this->adb->num_rows($result) > 0) {
				$row = $this->adb->fetchByAssoc($result);
				$total = floatval($row['total']);
			}
			DatabaseUtils::closeResult($result);
			return $total;
		}
		
		private function updateProjectPerformedEffort ($projectId) {
			if (empty($projectId)) {
				return;
			}
			$totalEfforts = $this->getTotalWorkPerformedEffortForProject($projectId);
			$this->adb->pquery(
				'UPDATE vtiger_proyectos SET esfuerzo_ejecutad = ? WHERE proyectosid = ?',
				array($totalEfforts, $projectId)
			);
		}
		
		public function recalculateProjectPerformedEffort ($projectId) {
			$this->updateProjectPerformedEffort($projectId);
		}
		
		private function getTotalWorkPerformedCostForProject ($projectId) {
			if (empty($projectId)) {
				return 0;
			}
			$total = 0;
			$sql = 'SELECT COALESCE(SUM(ot.cost_work_performed), 0) AS total
				   FROM vtiger_project_works pw
				   INNER JOIN vtiger_orden_de_trabajo ot ON ot.orden_de_trabajoid = pw.crmid_job
				   INNER JOIN vtiger_crmentity ce ON ce.crmid = ot.orden_de_trabajoid AND ce.deleted = 0
				  WHERE pw.crmid = ?';
			$result = $this->adb->pquery($sql, array($projectId));
			if ($this->adb->num_rows($result) > 0) {
				$row = $this->adb->fetchByAssoc($result);
				$total = floatval($row['total']);
			}
			DatabaseUtils::closeResult($result);
			return $total;
		}
		
		private function updateProjectPerformedCost ($projectId) {
			if (empty($projectId)) {
				return;
			}
			$totalCosts = $this->getTotalWorkPerformedCostForProject($projectId);
			$this->adb->pquery(
				'UPDATE vtiger_proyectos SET costo_general_del_proyect = ? WHERE proyectosid = ?',
				array($totalCosts, $projectId)
			);
		}
		
		public function recalculateProjectPerformedCost ($projectId) {
			$this->updateProjectPerformedCost($projectId);
			$this->updateProjectSituation($projectId);
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
			$modStrings = return_module_language ('es_es','proyectos');
			list ($prefix, $crm, $suffix) = explode ('_', $this->adb->dbName);
			unset ($prefix, $crm);
			$relatedJobs  = null;
			$relatedStage = array();
			if (!empty ($crmId)) {
				$relatedJobs  = $this->fetchRelatedWork ($crmId, $currentUser,$view);
				$relatedStage = $this->getRelatedStage ($relatedJobs);
				if(empty ($relatedJobs) && !empty ($view)) {
					return null;
				}
			}
			$numberingHelper = NumberHelper::getInstance ($this->adb, $currentUser);
			$totalWorkEstimatedCostRaw = 0;
			if (!empty($relatedJobs)) {
				foreach ($relatedJobs as $job) {
					if ($job instanceof ProjectWorks) {
						$totalWorkEstimatedCostRaw += floatval($job->getWorkEstimatedCostRaw());
					}
				}
			}
			$totalWorkEstimatedCost = $numberingHelper->setNumberFormat($totalWorkEstimatedCostRaw, 'work_estimated_cost');
			$projectUserProposedCost = 0;
			if (!empty($crmId)) {
				$projectResult = $this->adb->pquery(
					'SELECT costo_total_estimad FROM vtiger_proyectos WHERE proyectosid = ?',
					array($crmId)
				);
				if ($this->adb->num_rows($projectResult) > 0) {
					$row = $this->adb->fetchByAssoc($projectResult);
					$projectUserProposedCost = floatval($row['costo_total_estimad']);
				}
				DatabaseUtils::closeResult($projectResult);
			}
			$isTotalCostLowerThanUserProposed = ($projectUserProposedCost > $totalWorkEstimatedCostRaw);
		
			// Calcular total de costos ejecutados
			$totalCostWorkPerformedRaw = $this->getTotalCostWorkPerformedForProject($crmId);
			$totalCostWorkPerformed = $numberingHelper->setNumberFormat($totalCostWorkPerformedRaw, 'cost_work_performed');
		
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('AVAILABLE_SYSTEM_USERS', UserManager::getInstance ($this->adb, $suffix)->fetchUsers ());
			$smarty->assign ('CURRENT_USER_ID', $currentUser->id);
			$smarty->assign ('CURRENT_USER_NAME', $currentUser->first_name . ' ' . $currentUser->last_name);
			$smarty->assign ('NUMBERING_FORMAT', $currentUser->numbering_format);
			$smarty->assign ('PROJECT_STAGES', $this->getProjectStages ($currentUser));
			$smarty->assign ('RELATED_STAGES', $relatedStage);
			$smarty->assign ('RELATED_JOBS', $relatedJobs);
			$smarty->assign ('TOTAL_WORK_ESTIMATED_COST', $totalWorkEstimatedCost);
			$smarty->assign ('TOTAL_COST_WORK_PERFORMED', $totalCostWorkPerformed);
			$smarty->assign ('IS_TOTAL_COST_LOWER_THAN_USER', $isTotalCostLowerThanUserProposed);
			$smarty->assign ('PROJECT_USER_PROPOSED_COST_RAW', $projectUserProposedCost);
			$smarty->assign ('USER_DATE_FORMAT', $currentUser->date_format ? $currentUser->date_format : 'yyyy-mm-dd');
			$smarty->assign ('VIEW', $view);
			return $smarty->fetch ("modules/proyectos/job_project/jobProjectEditView.tpl");
		}
		
		/**
		 * @param integer $crmId
		 * @param Users $userId
		 * @param string $mode
		 *
		 * @throws Exception
		 */
		public function saveJobInProject ($crmId, $user, $mode) {
			if (empty ($user) || empty ($crmId)) {
				throw new Exception ('Uoops! algo salio mal, intenta de nuevo');
			} else if (empty ($_REQUEST['projec_job'])) {
				throw new Exception ('El proyecto no tiene trabajos');
			}
			$numberingHelper = NumberHelper::getInstance ($this->adb, $user);
			$jobs        = $_REQUEST['projec_job'];
			$totalJobs   = count ($jobs['stage']);
			$projectWork = ProjectWorks::getInstance ();
			
			// Guardar el valor del summaryRow ANTES de convertirlo, para evitar aplicar setSaveNumberFormat dos veces
			$projectProgressForSave = null;
			if (isset($jobs['summaryRow']['project_progress'])) {
				$projectProgressForSave = $numberingHelper->setSaveNumberFormat($jobs['summaryRow']['project_progress']);
			}
			$workEstimatedCostTotalForSave = null;
			if (isset($jobs['summaryRow']['work_estimated_cost'])) {
				$workEstimatedCostTotalForSave = $numberingHelper->setSaveNumberFormat($jobs['summaryRow']['work_estimated_cost']);
			}
			
			// Obtener trabajos existentes ANTES de eliminarlos para detectar cambios
			$oldJobsData = array();
			if ($mode == 'edit') {
				$oldJobsResult = $this->adb->pquery(
					'SELECT pw.*, COALESCE(ot.titulo, ot.asunto) as job_name_current, ot.fecha_prevista, ot.fecha_estim_fin, ot.overall_progress_perc 
					 FROM vtiger_project_works pw 
					 LEFT JOIN vtiger_orden_de_trabajo ot ON ot.orden_de_trabajoid = pw.crmid_job 
					 WHERE pw.crmid = ?',
					array($crmId)
				);
				while ($oldRow = $this->adb->fetchByAssoc($oldJobsResult, -1, false)) {
					$oldJobName = !empty($oldRow['job_name_current']) ? $oldRow['job_name_current'] : $oldRow['job_name'];
					$oldJobsData[$oldRow['crmid_job']] = array(
						'job_name' => $oldJobName,
						'start_date' => $oldRow['fecha_prevista'],
						'due_date' => $oldRow['fecha_estim_fin'],
						'job_contribution_factor' => $oldRow['job_contribution_factor'],
						'percentage_completion' => $oldRow['overall_progress_perc'],
						'responsible_job' => $oldRow['responsible_job'],
						'stageid' => $oldRow['stageid']
					);
				}
				$this->delRelatedWorks ($crmId);
			}
			
			// Array para acumular cambios detectados
			$changesDetected = array();
			
			for ($k = 0; $k < $totalJobs; $k++) {
				if ($k == 0) {
					$jobs['summaryRow']['job_contribution_factor'] = $numberingHelper->setSaveNumberFormat ($jobs['summaryRow']['job_contribution_factor']);
					$jobs['summaryRow']['project_progress']        = $numberingHelper->setSaveNumberFormat ($jobs['summaryRow']['project_progress']);
					$projectWork->setSummaryStr ($jobs['summaryRow']);
				} else {
					$projectWork->setSummaryStr (null);
				}
				
				$jobCrmId = intval($jobs['crmid_job'][$k]);
				$jobName = $jobs['job_name'][$k];
				
				// Obtener datos del trabajo desde la BD - IGNORAR valores del request excepto Factor de avance y Etapa
				// El usuario solo debe poder editar: Etapa, Nombre del trabajo, Factor de avance
				// Los demás valores vienen directamente desde el trabajo (orden_de_trabajo)
				// El usuario asignado está en vtiger_crmentity.smownerid, no en vtiger_orden_de_trabajo
				$jobDataResult = $this->adb->pquery(
					'SELECT ot.fecha_prevista, ot.fecha_estim_fin, ce.smownerid AS assigned_user_id, ot.overall_progress_perc 
					 FROM vtiger_orden_de_trabajo ot
					 INNER JOIN vtiger_crmentity ce ON ce.crmid = ot.orden_de_trabajoid
					 WHERE ot.orden_de_trabajoid = ?',
					array($jobCrmId)
				);
				
				$startDateDb = date('Y-m-d'); // Default
				$dueDateDb = date('Y-m-d', strtotime('+2 days')); // Default
				$newResponsibleJob = 0;
				$newPercentageCompletion = 0;
				
				if ($this->adb->num_rows($jobDataResult) > 0) {
					$jobData = $this->adb->fetchByAssoc($jobDataResult, 0, false);
					$startDateDb = !empty($jobData['fecha_prevista']) ? $jobData['fecha_prevista'] : $startDateDb;
					$dueDateDb = !empty($jobData['fecha_estim_fin']) ? $jobData['fecha_estim_fin'] : $dueDateDb;
					$newResponsibleJob = intval($jobData['assigned_user_id']);
					$newPercentageCompletion = floatval($jobData['overall_progress_perc']);
				}
				DatabaseUtils::closeResult($jobDataResult);
				
				// El Factor de avance es el ÚNICO valor numérico que viene del formulario (editable por usuario)
				$newContributionFactor = $numberingHelper->setSaveNumberFormat($jobs['job_contribution_factor'][$k]);
				if ($newContributionFactor === '' || $newContributionFactor === null) {
					$newContributionFactor = 0;
				}
				
				// La etapa sí se toma del request (editable por usuario)
				$newStageId = intval($jobs['stage'][$k]);
				
				// Detectar cambios si estamos en modo edición y el trabajo existía antes
				if ($mode == 'edit' && isset($oldJobsData[$jobCrmId])) {
					$oldData = $oldJobsData[$jobCrmId];
					
					// Comparar fechas de inicio
					if ($oldData['start_date'] != $startDateDb && !empty($startDateDb)) {
						$changesDetected[] = array(
							'field' => "Trabajos del proyecto, Fecha inicio - {$jobName}",
							'oldvalue' => $oldData['start_date'] ?: '(vacío)',
							'newvalue' => $startDateDb
						);
					}
					// Comparar fechas de fin
					if ($oldData['due_date'] != $dueDateDb && !empty($dueDateDb)) {
						$changesDetected[] = array(
							'field' => "Trabajos del proyecto, Fecha fin - {$jobName}",
							'oldvalue' => $oldData['due_date'] ?: '(vacío)',
							'newvalue' => $dueDateDb
						);
					}
					// Comparar factor de contribución
					if (floatval($oldData['job_contribution_factor']) != floatval($newContributionFactor)) {
						$changesDetected[] = array(
							'field' => "Trabajos del proyecto, Factor - {$jobName}",
							'oldvalue' => $oldData['job_contribution_factor'] . '%',
							'newvalue' => $newContributionFactor . '%'
						);
					}
				}
				
				// Calcular el avance del proyecto: Factor de avance * Avance trabajo / 100
				// Esto asegura que el valor siempre sea consistente con la fórmula
				$newProjectProgress = ($newContributionFactor * $newPercentageCompletion) / 100;
				$projectWork->setCrmId (intval ($crmId))
					->setCrmIdJob ($jobCrmId)
					->setJobContributionFactor ($newContributionFactor)
					->setJobName ($jobName)
					->setPercentageCompletion ($newPercentageCompletion)
					->setProjectProgress ($newProjectProgress)
					->setResponsibleJob ($newResponsibleJob)
					->setStageId ($newStageId)
					->setStartDate ($startDateDb)
					->setEstimatedDueDate ($dueDateDb);
				$this->saveProjectWork ($projectWork);
			}
			
			// Registrar cambios en el histórico del proyecto si hay cambios detectados
			if (!empty($changesDetected)) {
				$this->registerProjectWorkChanges($crmId, $changesDetected, $user->id);
			}
			
			// Actualizar el campo porcentaje_de_avance_genera del proyecto con el valor del summaryRow
			// Usar el valor guardado previamente para evitar aplicar setSaveNumberFormat dos veces
			if ($projectProgressForSave !== null) {
				$this->adb->pquery(
					'UPDATE vtiger_proyectos SET porcentaje_de_avance_genera = ? WHERE proyectosid = ?',
					array($projectProgressForSave, $crmId)
				);
			}
			// Actualizar el costo estimado total del proyecto con la regla max(total trabajos, valor usuario)
			if ($workEstimatedCostTotalForSave !== null) {
				$projectResult = $this->adb->pquery(
					'SELECT costo_total_estimad FROM vtiger_proyectos WHERE proyectosid = ?',
					array($crmId)
				);
				$userProposed = 0;
				if ($this->adb->num_rows($projectResult) > 0) {
					$row = $this->adb->fetchByAssoc($projectResult);
					$userProposed = floatval($row['costo_total_estimad']);
				}
				DatabaseUtils::closeResult($projectResult);
				$newValue = ($userProposed > $workEstimatedCostTotalForSave) ? $userProposed : $workEstimatedCostTotalForSave;
				$this->adb->pquery(
					'UPDATE vtiger_proyectos SET costo_total_estimad = ? WHERE proyectosid = ?',
					array($newValue, $crmId)
				);
			} else {
				$this->updateProjectEstimatedCost($crmId);
			}
			// Actualizar esfuerzo estimado total del proyecto con la regla max(total trabajos, valor usuario)
			$this->updateProjectEstimatedEffort($crmId);
			// Actualizar esfuerzo ejecutado del proyecto (suma de trabajos)
			$this->updateProjectPerformedEffort($crmId);
			// Actualizar costo ejecutado del proyecto (suma de trabajos)
			$this->updateProjectPerformedCost($crmId);
		}
		
		/**
		 * Registra cambios en trabajos del proyecto en el histórico
		 * @param integer $projectId
		 * @param array $changes
		 * @param integer $userId
		 */
		private function registerProjectWorkChanges($projectId, $changes, $userId) {
			try {
				// Obtener tabid del módulo proyectos
				$module = ModuleManager::getInstance($this->adb)->fetchModule('proyectos', true);
				if (empty($module)) {
					return;
				}
				$moduleId = $module->getId();
				
				// Buscar el campo task_project para asociar el histórico de trabajos
				$fieldResult = $this->adb->pquery(
					"SELECT fieldid, uitype FROM vtiger_field WHERE tabid = ? AND fieldname = 'task_project' AND presence IN (0,2)",
					array($moduleId)
				);
				$fieldId = 0;
				if ($this->adb->num_rows($fieldResult) > 0) {
					$fieldId = $this->adb->query_result($fieldResult, 0, 'fieldid');
				}
				
				// Registrar cada cambio
				foreach ($changes as $change) {
					// Detectar si es formato nuevo (array) o antiguo (string)
					if (is_array($change)) {
						$changeField = $change['field'];
						// Extraer solo el subcampo (después de la coma) para evitar repetir "Trabajos del proyecto"
						$subField = $change['field'];
						if (strpos($change['field'], ', ') !== false) {
							$parts = explode(', ', $change['field'], 2);
							$subField = $parts[1]; // Solo la parte después de "Trabajos del proyecto, "
						}
						$oldValue = $subField . ': ' . $change['oldvalue'];
						$newValue = $change['newvalue'];
					} else {
						// Formato antiguo (string) - mantener compatibilidad
						$changeField = '(Cambio de trabajo)';
						$oldValue = '';
						$newValue = $change;
					}
					
					$result = $this->adb->pquery("select MAX(crmentityid)+1 as id1 from vtiger_crmentityutils");
					$vcrmentityid = $this->adb->query_result($result, 0, 'id1');
					$vdate = date("Y-m-d H:i:s");
					$vmodifiedon = 1;
					try {
						$this->adb->pquery(
							'INSERT INTO vtiger_crmentityutils (crmentityid, module, field, oldvalue, newvalue, modifiedby, modifiedon, registryid, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
							array(
								$vcrmentityid,
								$moduleId,
								$fieldId,
								$oldValue,
								$newValue,
								$userId,
								$vmodifiedon,
								$projectId,
								$vdate
							)
						);
						$this->adb->pquery("UPDATE vtiger_crmentityutils_seq SET id = ?", array($vcrmentityid));
					} catch (Exception $e) {
						// Ignorar error de duplicado para no interrumpir el flujo
						if (strpos($e->getMessage(), 'Duplicate entry') === false) {
							throw $e;
						}
					}
				}
			} catch (Exception $e) {
			}
		}
		
		/**
		 * @param ProjectWorks $projectWork
		 *
		 * @throws Exception
		 * @throws WebServiceException
		 */
		public function saveProjectWork ($projectWork) {
			$projectWork->validate ();
			
			global $current_user;
			$startDateDb    = $this->convertProjectDateToDbFormat($projectWork->getStartDate (), $current_user);
			$estimatedDueDb = $this->convertProjectDateToDbFormat($projectWork->getEstimatedDueDate (), $current_user);
			
			$this->adb->pquery (
				'INSERT INTO vtiger_project_works (crmid, stageid, crmid_job, job_name, start_date, estimated_due_date, responsible_job, job_contribution_factor, percentage_completion, project_progress, summaryRow) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
				array ($projectWork->getCrmId (), $projectWork->getStageId (), $projectWork->getCrmIdJob (), $projectWork->getJobName (), $startDateDb, $estimatedDueDb, $projectWork->getResponsibleJob (), $projectWork->getJobContributionFactor (), $projectWork->getPercentageCompletion (), $projectWork->getProjectProgress (), $projectWork->getSummaryStr ())
			);
			$projectJobId = $this->adb->getLastInsertID ();
			$this->updateJobRecord ($projectWork);
			
			// Actualizar progreso esperado del proyecto (desactivado temporalmente por error 1442)
			// try {
			// 	if (class_exists('ProjectEstimatedProgressManager')) {
			// 		$progressManager = ProjectEstimatedProgressManager::getInstance($this->adb);
			// 		$progressManager->onProjectWorkChange($projectWork->getCrmId());
			// 	}
			// } catch (Exception $e) {
			// 	// Log error pero no interrumpir el proceso
			// 	error_log("Error updating project estimated progress: " . $e->getMessage());
			// }
			
			return $projectJobId;
			
		}
		
		/**
		 * @param integer $worksId
		 * @param integer $crmIdJob
		 * @param string $dueDate
		 *
		 * @throws Exception
		 */
		public function updateDueDateJob ($worksId, $crmIdJob, $dueDate) {
			if (
				empty ($worksId) ||
				empty ($crmIdJob) ||
				empty ($dueDate)
			) {
				throw new Exception ('Algo salió mal, por favor inténtelo mas tarde');
			}
			$this->adb->pquery (
				'UPDATE vtiger_project_works SET estimated_due_date=? WHERE projectworksid=? AND crmid_job=?',
				array ($dueDate, $worksId, $crmIdJob)
			);
		}
		
		/**
		 * Actualiza las fechas estimadas de inicio y fin del proyecto.
		 * Solo actualiza los campos que tengan un valor no vacío.
		 *
		 * @param integer $projectId
		 * @param string|null $startDate Fecha de inicio en formato BD (Y-m-d)
		 * @param string|null $endDate Fecha de fin en formato BD (Y-m-d)
		 * @return void
		 */
		private function updateEstimatedDatesToProject ($projectId, $startDate, $endDate) {
			if (empty ($projectId) || (empty ($startDate) && empty ($endDate))) {
				return;
			}
			
			// Obtener valores actuales para detectar cambios
			$oldResult = $this->adb->pquery(
				'SELECT est_start_date, est_end_date FROM vtiger_proyectos WHERE proyectosid = ?',
				array($projectId)
			);
			$oldStartDate = null;
			$oldEndDate = null;
			if ($this->adb->num_rows($oldResult) > 0) {
				$oldRow = $this->adb->fetchByAssoc($oldResult);
				$oldStartDate = $oldRow['est_start_date'];
				$oldEndDate = $oldRow['est_end_date'];
			}
			
			$fields = array ();
			$params = array ();
			$changes = array();
			
			if (!empty ($startDate)) {
				$fields[] = 'est_start_date=?';
				$params[] = $startDate;
				// Detectar cambio en fecha de inicio
				if ($oldStartDate != $startDate && (!empty($oldStartDate) && $oldStartDate != '0000-00-00' || empty($oldStartDate) || $oldStartDate == '0000-00-00')) {
					$changes[] = array(
						'field' => 'Trabajos del proyecto, Fecha inicio estimada del proyecto',
						'oldvalue' => (!empty($oldStartDate) && $oldStartDate != '0000-00-00') ? $oldStartDate : '(vacío)',
						'newvalue' => $startDate
					);
				}
			}
			if (!empty ($endDate)) {
				$fields[] = 'est_end_date=?';
				$params[] = $endDate;
				// Detectar cambio en fecha de fin
				if ($oldEndDate != $endDate && (!empty($oldEndDate) && $oldEndDate != '0000-00-00' || empty($oldEndDate) || $oldEndDate == '0000-00-00')) {
					$changes[] = array(
						'field' => 'Trabajos del proyecto, Fecha fin estimada del proyecto',
						'oldvalue' => (!empty($oldEndDate) && $oldEndDate != '0000-00-00') ? $oldEndDate : '(vacío)',
						'newvalue' => $endDate
					);
				}
			}
			if (empty ($fields)) {
				return;
			}
			$params[] = $projectId;
			$this->adb->pquery ('UPDATE vtiger_proyectos SET ' . implode (', ', $fields) . ' WHERE proyectosid = ?', $params);
			
			// Registrar cambios en el histórico si hubo cambios
			if (!empty($changes)) {
				global $current_user;
				$userId = !empty($current_user->id) ? $current_user->id : 1;
				$this->registerProjectWorkChanges($projectId, $changes, $userId);
			}
		}
		
		/**
		 * Recalcula las fechas estimadas de inicio y fin de un proyecto
		 * a partir de las fechas estimadas de sus trabajos relacionados.
		 *
		 * Ignora:
		 *  - trabajos eliminados (vtiger_crmentity.deleted = 1)
		 *  - fechas vacías o '0000-00-00'
		 *
		 * @param integer $projectId
		 * @return void
		 */
		public function recalculateProjectEstimatedDatesFromDb ($projectId) {
			if (empty ($projectId)) {
				return;
			}
			$minStartDateDb = null;
			$maxDueDateDb   = null;
			
			$sql = $this->adb->pquery (
				'SELECT
						odt.fecha_prevista,
						odt.fecha_estim_fin
				   FROM
						vtiger_orden_de_trabajo odt
				   INNER JOIN vtiger_project_works pw ON pw.crmid_job = odt.orden_de_trabajoid
				   INNER JOIN vtiger_crmentity crm ON crm.crmid = odt.orden_de_trabajoid AND crm.deleted = 0
				   WHERE
						pw.crmid = ?'
				,
				array ($projectId)
			);
			
			if ($this->adb->num_rows ($sql) > 0) {
				while ($row = $this->adb->fetchByAssoc ($sql, -1, false)) {
					$startDateDb = $row['fecha_prevista'];
					$dueDateDb   = $row['fecha_estim_fin'];
					
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
			
			$this->updateEstimatedDatesToProject ($projectId, $minStartDateDb, $maxDueDateDb);
		}
		
		/**
		 * Obtiene los IDs de proyectos relacionados con un trabajo dado.
		 *
		 * @param integer $workId
		 * @return array Lista de IDs de proyectos
		 */
		public function getRelatedProjectIds ($workId) {
			if (empty ($workId)) {
				return array ();
			}
			$projectIds = array ();
			$result = $this->adb->pquery (
				'SELECT DISTINCT pw.crmid
				   FROM vtiger_project_works pw
				   INNER JOIN vtiger_crmentity ce ON ce.crmid = pw.crmid AND ce.deleted = 0
				  WHERE pw.crmid_job = ?'
				,
				array ($workId)
			);
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$projectIds[] = (int)$row['crmid'];
				}
			}
			DatabaseUtils::closeResult ($result);
			return $projectIds;
		}
		
		/**
		 * Recalcular el porcentaje de avance del proyecto basándose en los trabajos relacionados
		 * @param integer $projectId
		 * @throws Exception
		 */
		public function recalculateProjectProgress ($projectId) {
			if (empty($projectId)) {
				return;
			}
			
			// Obtener el porcentaje de avance actual para detectar cambios
			$oldProgressResult = $this->adb->pquery(
				'SELECT porcentaje_de_avance_genera FROM vtiger_proyectos WHERE proyectosid = ?',
				array($projectId)
			);
			$oldProgress = 0;
			if ($this->adb->num_rows($oldProgressResult) > 0) {
				$oldRow = $this->adb->fetchByAssoc($oldProgressResult);
				$oldProgress = floatval($oldRow['porcentaje_de_avance_genera']);
			}
			
			// Consultar los trabajos del proyecto y calcular el porcentaje total
			// Ignorar trabajos eliminados (vtiger_crmentity.deleted = 0)
			$result = $this->adb->pquery(
				'SELECT pw.job_contribution_factor, ot.overall_progress_perc 
				 FROM vtiger_project_works pw
				 INNER JOIN vtiger_orden_de_trabajo ot ON ot.orden_de_trabajoid = pw.crmid_job
				 INNER JOIN vtiger_crmentity ce ON ce.crmid = ot.orden_de_trabajoid AND ce.deleted = 0
				 WHERE pw.crmid = ?',
				array($projectId)
			);
			
			$totalProgress = 0;
			while ($row = $this->adb->fetchByAssoc($result)) {
				$factor = floatval($row['job_contribution_factor']);
				$progress = isset($row['overall_progress_perc']) ? floatval($row['overall_progress_perc']) : 0;
				$contribution = ($factor * $progress) / 100;
				$totalProgress += $contribution;
			}
			
			DatabaseUtils::closeResult($result);
			
			// Actualizar el campo porcentaje_de_avance_genera del proyecto
			$this->adb->pquery(
				'UPDATE vtiger_proyectos SET porcentaje_de_avance_genera = ? WHERE proyectosid = ?',
				array($totalProgress, $projectId)
			);
			
			// Registrar cambio en el histórico si hubo cambio significativo (más de 0.01%)
			if (abs($oldProgress - $totalProgress) > 0.01) {
				global $current_user;
				$userId = !empty($current_user->id) ? $current_user->id : 1;
				$oldProgressFormatted = number_format($oldProgress, 2, '.', '');
				$newProgressFormatted = number_format($totalProgress, 2, '.', '');
				$changes = array(array(
					'field' => 'Trabajos del proyecto, Porcentaje de avance del proyecto',
					'oldvalue' => $oldProgressFormatted . '%',
					'newvalue' => $newProgressFormatted . '%'
				));
				$this->registerProjectWorkChanges($projectId, $changes, $userId);
			}
			
			// Actualizar el progreso esperado del proyecto
			$this->updateProjectEstimatedProgress($projectId);
			
			// Actualizar la situación del proyecto después de recalcular el avance
			$this->updateProjectSituation($projectId);
		}
		
		/**
		 * Actualizar el progreso esperado del proyecto basado en el progreso esperado de sus trabajos
		 * 
		 * @param integer $projectId ID del proyecto
		 * @return void
		 */
		private function updateProjectEstimatedProgress($projectId) {
			if (empty($projectId)) {
				return;
			}
			
			// Calcular el progreso esperado del proyecto basado en los trabajos
			$result = $this->adb->pquery(
				'SELECT pw.job_contribution_factor, ot.expected_work_progress 
				 FROM vtiger_project_works pw
				 INNER JOIN vtiger_orden_de_trabajo ot ON ot.orden_de_trabajoid = pw.crmid_job
				 INNER JOIN vtiger_crmentity ce ON ce.crmid = ot.orden_de_trabajoid AND ce.deleted = 0
				 WHERE pw.crmid = ?',
				array($projectId)
			);
			
			$totalEstimatedProgress = 0;
			while ($row = $this->adb->fetchByAssoc($result)) {
				$factor = floatval($row['job_contribution_factor']);
				$expectedProgress = isset($row['expected_work_progress']) ? floatval($row['expected_work_progress']) : 0;
				$contribution = ($factor * $expectedProgress) / 100;
				$totalEstimatedProgress += $contribution;
			}
			
			DatabaseUtils::closeResult($result);
			
			// Actualizar el campo estimated_project_progress del proyecto
			$this->adb->pquery(
				'UPDATE vtiger_proyectos SET estimated_project_progress = ? WHERE proyectosid = ?',
				array($totalEstimatedProgress, $projectId)
			);
		}
		
		/**
		 * Actualizar work_situation usando procedimiento almacenado simple
		 * Reemplaza la funcionalidad del trigger trg_after_update_orden_trabajo_work_situation
		 * 
		 * @param integer $jobCrmId
		 * @return boolean
		 */
		private function updateWorkSituation($jobCrmId) {
			try {
				// FIX: Limpiar resultados pendientes antes de llamar al procedure
				pearDatabase_FlushResults($this->adb);
				
				// Llamar al procedimiento simple SIN retornar resultados para evitar "commands out of sync"
				$result = $this->adb->pquery('CALL sp_update_work_situation_simple(?)', array($jobCrmId));
				
				// FIX: Consumir cualquier resultado residual del procedure
				pearDatabase_FlushResults($this->adb);
				
				// No necesitamos consumir resultados porque el procedimiento no retorna nada
				if ($result) {
					return true;
				}
			} catch (Exception $e) {
				// Log error pero no interrumpir el proceso
			}
			
			return false;
		}
		
		/**
		 * Actualizar work_situation para múltiples trabajos
		 * Útil para actualizaciones masivas
		 * 
		 * @param array $jobIds
		 * @return array
		 */
		public function updateMultipleWorkSituations($jobIds) {
			$results = array();
			
			foreach ($jobIds as $jobId) {
				$results[$jobId] = $this->updateWorkSituation($jobId);
			}
			
			return $results;
		}
		
		/**
		 * Actualizar work_situation para todos los trabajos de un proyecto
		 * 
		 * @param integer $projectId
		 * @return array
		 */
		public function updateProjectWorkSituations($projectId) {
			$jobIds = array();
			
			// Obtener todos los trabajos del proyecto
			$result = $this->adb->pquery(
				'SELECT crmid_job FROM vtiger_project_works WHERE crmid = ?',
				array($projectId)
			);
			
			if ($result && $this->adb->num_rows($result) > 0) {
				while ($row = $this->adb->fetchByAssoc($result)) {
					$jobIds[] = $row['crmid_job'];
				}
			}
			
			return $this->updateMultipleWorkSituations($jobIds);
		}
		
		/**
		 * Calcular la situación del proyecto basándose en progreso esperado, avance general, costo estimado y costo ejecutado
		 * 
		 * Estados posibles:
		 * - Óptima: Progreso >= esperado*0.95, costo <= estimado
		 * - En control: Progreso dentro del 95-105% del esperado, costo <= estimado
		 * - Alerta de eficiencia: Progreso >= esperado*1.05, costo > estimado
		 * - Retraso operativo: Progreso < esperado, costo <= estimado
		 * - Crítica: Progreso < esperado, costo > estimado
		 * 
		 * @param integer $crmId ID del proyecto
		 * @return string|null
		 */
		private function calculateProjectSituation($crmId) {
			if (empty($crmId)) {
				return null;
			}
			
			// Obtener los valores actuales del proyecto
			$query = 'SELECT 
							esfuerzo_total_estimad,
							estimated_project_progress,
							costo_total_estimad,
							porcentaje_de_avance_genera,
							costo_general_del_proyect
					  FROM vtiger_proyectos
					  WHERE proyectosid = ?';
			
			$result = $this->adb->pquery($query, array($crmId));
			
			if (!$result || $this->adb->num_rows($result) == 0) {
				return null;
			}
			
			$row = $this->adb->fetchByAssoc($result, -1, false);
			DatabaseUtils::closeResult($result);
			
			// Convertir valores a float
			$progresoEsperado = floatval($row['estimated_project_progress']);
			$avanceGeneral = floatval($row['porcentaje_de_avance_genera']);
			$costoEstimado = floatval($row['costo_total_estimad']);
			$costoEjecutado = floatval($row['costo_general_del_proyect']);
			
			// Evaluar condiciones en orden de prioridad según la tabla
			
			// 1. Óptima: (progreso >= esperado*0.95 Y avance >= esperado) Y (costo <= estimado)
			if (($avanceGeneral >= ($progresoEsperado * 0.95) && $avanceGeneral >= $progresoEsperado) && 
				($costoEstimado >= $costoEjecutado)) {
				return 'Óptima';
			}
			
			// 2. En control: (progreso >= esperado*0.95 Y avance < esperado*1.05) Y (costo <= estimado)
			if (($avanceGeneral >= ($progresoEsperado * 0.95) && $avanceGeneral < ($progresoEsperado * 1.05)) && 
				($costoEstimado >= $costoEjecutado)) {
				return 'En control';
			}
			
			// 3. Alerta de eficiencia: (progreso >= esperado*1.05 Y avance >= esperado) Y (costo > estimado)
			if (($avanceGeneral >= ($progresoEsperado * 1.05) && $avanceGeneral >= $progresoEsperado) && 
				($costoEstimado < $costoEjecutado)) {
				return 'Alerta de eficiencia';
			}
			
			// 4. Retraso operativo: (progreso > esperado Y avance < esperado) Y (costo <= estimado)
			if (($progresoEsperado > $avanceGeneral) && 
				($costoEstimado >= $costoEjecutado)) {
				return 'Retraso operativo';
			}
			
			// 5. Crítica: (progreso > esperado Y avance < esperado) Y (costo > estimado)
			if (($progresoEsperado > $avanceGeneral) && 
				($costoEstimado < $costoEjecutado)) {
				return 'Crítica';
			}
			
			// Por defecto, si no cumple ninguna condición específica
			return 'En control';
		}
		
		/**
		 * Actualizar el campo project_situation del proyecto
		 * 
		 * @param integer $crmId ID del proyecto
		 * @return void
		 */
		private function updateProjectSituation($crmId) {
			if (empty($crmId)) {
				return;
			}
			
			// Obtener el valor actual de project_situation
			$currentQuery = 'SELECT project_situation FROM vtiger_proyectos WHERE proyectosid = ?';
			$currentResult = $this->adb->pquery($currentQuery, array($crmId));
			
			if (!$currentResult || $this->adb->num_rows($currentResult) == 0) {
				return;
			}
			
			$currentRow = $this->adb->fetchByAssoc($currentResult, -1, false);
			$currentSituation = $currentRow['project_situation'];
			DatabaseUtils::closeResult($currentResult);
			
			// Calcular la nueva situación
			$newSituation = $this->calculateProjectSituation($crmId);
			
			if ($newSituation === null) {
				return;
			}
			
			// Solo actualizar si el valor cambió
			if ($currentSituation === $newSituation) {
				return;
			}
			
			// Actualizar el campo en la base de datos
			$this->adb->pquery(
				'UPDATE vtiger_proyectos SET project_situation=? WHERE proyectosid = ?',
				array($newSituation, $crmId)
			);
		}
		
		/**
		 * Recalcular la situación del proyecto (método público)
		 * 
		 * @param integer $projectId
		 * @return void
		 */
		public function recalculateProjectSituation($projectId) {
			$this->updateProjectSituation($projectId);
		}
		
		/**
		 * Obtiene la fecha más antigua entre todas las tareas de todos los trabajos
		 * de un proyecto. Se usa como fecha base para el desplazamiento relativo
		 * al duplicar proyectos.
		 *
		 * @param integer $projectId
		 * @return DateTime|null
		 */
		private function getProjectOldestTaskDate ($projectId) {
			
			$result = $this->adb->pquery (
				"SELECT MIN(act.date_start) AS min_start, MIN(act.due_date) AS min_due
				 FROM vtiger_project_works pw
				 INNER JOIN vtiger_orden_de_trabajo ot ON ot.orden_de_trabajoid = pw.crmid_job
				 INNER JOIN vtiger_crmentity ce ON ce.crmid = ot.orden_de_trabajoid AND ce.deleted = 0
				 INNER JOIN vtiger_seactivityrel sar ON sar.crmid = ot.orden_de_trabajoid
				 INNER JOIN vtiger_activity act ON act.activityid = sar.activityid
				 INNER JOIN vtiger_crmentity ce2 ON ce2.crmid = act.activityid AND ce2.deleted = 0
				 WHERE pw.crmid = ? AND act.activitytype <> ? AND act.date_start <> '0000-00-00' AND act.due_date <> '0000-00-00'",
				array ($projectId, 'Job')
			);
			
			$oldestDate = null;
			if ($result && $this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				if (!empty ($row['min_start']) && $row['min_start'] !== '0000-00-00') {
					$oldestDate = new DateTime ($row['min_start']);
				}
				if (!empty ($row['min_due']) && $row['min_due'] !== '0000-00-00') {
					$d = new DateTime ($row['min_due']);
					if ($oldestDate === null || $d < $oldestDate) {
						$oldestDate = $d;
					}
				}
			}
			DatabaseUtils::closeResult ($result);
			
			if ($oldestDate !== null) {
			} else {
			}
			return $oldestDate;
		}
		
		/**
		 * Duplica los trabajos asociados a un proyecto original en un nuevo proyecto.
		 * Actualiza el payload $_REQUEST['projec_job'] para que apunte a los nuevos trabajos.
		 *
		 * @param integer $originalProjectId
		 * @param integer $newProjectId
		 * @param Users $currentUser
		 *
		 * @return array Mapa oldJobId => newJobId
		 * @throws Exception
		 */
		public function duplicateProjectJobs ($originalProjectId, $newProjectId, $currentUser) {
			if (empty ($originalProjectId) || empty ($newProjectId) || empty ($currentUser)) {
				return array ();
			}
			
			// Fecha base global: la fecha más antigua de todas las tareas de todos los trabajos del proyecto
			$globalBaseDate = $this->getProjectOldestTaskDate ($originalProjectId);
			
			$result = $this->adb->pquery (
				'SELECT pw.crmid_job 
				 FROM vtiger_project_works pw 
				 INNER JOIN vtiger_orden_de_trabajo ot ON ot.orden_de_trabajoid = pw.crmid_job 
				 INNER JOIN vtiger_crmentity ce ON ce.crmid = ot.orden_de_trabajoid 
				 WHERE pw.crmid = ? AND ce.deleted = 0 
				 ORDER BY pw.projectworksid ASC',
				array ($originalProjectId)
			);
			
			$jobMapping = array ();
			while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
				$originalJobId = intval ($row['crmid_job']);
				if (isset ($jobMapping[$originalJobId])) {
					continue;
				}
				$newJobId = $this->duplicateWork ($originalJobId, $newProjectId, $currentUser, $globalBaseDate);
				if (!empty ($newJobId)) {
					$jobMapping[$originalJobId] = $newJobId;
				}
			}
			DatabaseUtils::closeResult ($result);
			
			// Recalcular fechas estimadas de los trabajos copiados a partir de sus tareas
			if (!empty ($jobMapping)) {
				$taskToWork = new taskToWork ($this->adb);
				foreach ($jobMapping as $newJobId) {
					try {
						$taskToWork->recalculateWorkEstimatedDatesFromDb ($newJobId);
					} catch (Exception $e) {
					}
				}
			}
			
			// Reemplazar los IDs de trabajos originales por los nuevos en el payload
			if (!empty ($jobMapping) && !empty ($_REQUEST['projec_job']['crmid_job']) && is_array ($_REQUEST['projec_job']['crmid_job'])) {
				foreach ($_REQUEST['projec_job']['crmid_job'] as $key => $jobId) {
					$jobId = intval ($jobId);
					if (isset ($jobMapping[$jobId])) {
						$_REQUEST['projec_job']['crmid_job'][$key] = $jobMapping[$jobId];
					}
				}
			}
			
			return $jobMapping;
		}
		
		/**
		 * Duplica un trabajo individual usando el mismo algoritmo de duplicación de trabajos.
		 *
		 * @param integer $originalJobId
		 * @param integer $newProjectId
		 * @param Users $currentUser
		 *
		 * @return integer|null Nuevo orden_de_trabajoid o null si falla
		 * @throws Exception
		 * @throws WebServiceException
		 */
		private function duplicateWork ($originalJobId, $newProjectId, $currentUser, $globalBaseDate = null) {
			if (empty ($originalJobId) || empty ($newProjectId) || empty ($currentUser)) {
				return null;
			}
			
			// Preservar valores del request para restaurarlos al final
			$savedProjectTask = isset ($_REQUEST['projec_task']) ? $_REQUEST['projec_task'] : null;
			
			try {
				// Cargar trabajo original
				$originalJob = CRMEntity::getInstance (self::JOB_RELATED_MODULE);
				$originalJob->retrieve_entity_info ($originalJobId, self::JOB_RELATED_MODULE);
				
				// Preparar nuevo trabajo
				$newJob = CRMEntity::getInstance (self::JOB_RELATED_MODULE);
				$newJob->column_fields = $originalJob->column_fields;
				$newJob->id = '';
				$newJob->mode = '';
				
				// Limpiar campos de numeración para que se generen nuevos
				$newJob->column_fields['cod_orden_de_tra'] = '';
				if (isset ($newJob->column_fields['case_number'])) {
					$newJob->column_fields['case_number'] = '';
				}
				
				// Aplicar reglas de negocio: progreso 0%, estado Creado, responsable actual
				$newJob->column_fields['overall_progress_perc'] = 0;
				$newJob->column_fields['estado_de_la_orden'] = 'Creado';
				$newJob->column_fields['assigned_user_id'] = $currentUser->id;
				
				// Limpiar proyecto/asociar_a para evitar que el save_module de orden_de_trabajo
				// cree una relación temporal con el proyecto original. 
				// saveProjectWork/updateJobRecord los establecerán correctamente al nuevo proyecto.
				$newJob->column_fields['proyecto'] = '';
				$newJob->column_fields['asociar_a'] = '';
				
				// Evitar que saveTaskInWork cree una tarea por defecto; 
				// duplicateWorkTasks se encargará de duplicar las tareas reales.
				$_REQUEST['projec_task'] = array ('types' => array ());
				
				// Guardar nuevo trabajo sin heredar isDuplicate/record del proyecto;
				// de lo contrario el save de orden_de_trabajo (y sus tareas) intentaría
				// duplicar relaciones usando el ID del proyecto original.
				$savedIsDuplicate = isset ($_REQUEST['isDuplicate']) ? $_REQUEST['isDuplicate'] : null;
				$savedRecord = isset ($_REQUEST['record']) ? $_REQUEST['record'] : null;
				unset ($_REQUEST['isDuplicate']);
				unset ($_REQUEST['record']);
				
				// Guardar nuevo trabajo
				$newJob->save (self::JOB_RELATED_MODULE);
				$newJobId = $newJob->id;
				
				// Restaurar valores del proyecto para el resto del save
				if ($savedIsDuplicate !== null) {
					$_REQUEST['isDuplicate'] = $savedIsDuplicate;
				}
				if ($savedRecord !== null) {
					$_REQUEST['record'] = $savedRecord;
				}
				
				if (empty ($newJobId)) {
					return null;
				}
				
				// Duplicar relaciones uitype=10 excepto el campo proyecto (se asigna al nuevo proyecto luego)
				$this->duplicateJobUitype10Relations ($originalJobId, $newJobId);
				
				// Duplicar tareas reales del trabajo original
				$this->duplicateWorkTasks ($originalJobId, $newJobId, $currentUser, $globalBaseDate);
				
				return $newJobId;
			} catch (Exception $e) {
				return null;
			} finally {
				// Restaurar el payload de tareas original
				if ($savedProjectTask !== null) {
					$_REQUEST['projec_task'] = $savedProjectTask;
				} else {
					unset ($_REQUEST['projec_task']);
				}
			}
		}
		
		/**
		 * Duplica las tareas reales de un trabajo original a su copia,
		 * aplicando el mismo desplazamiento de fechas que el flujo de duplicación de trabajos.
		 *
		 * @param integer $originalJobId
		 * @param integer $newJobId
		 * @param Users $currentUser
		 */
		private function duplicateWorkTasks ($originalJobId, $newJobId, $currentUser, $globalBaseDate = null) {
			
			if (empty ($originalJobId) || empty ($newJobId) || empty ($currentUser)) {
				return;
			}
			
			// Obtener tareas reales del trabajo original (excluir la actividad tipo Job)
			$result = $this->adb->pquery (
				"SELECT act.activityid, act.subject, act.date_start, act.due_date, act.time_start, act.time_end, 
				        act.status, act.eventstatus, act.priority, act.location, act.activitytype, 
				        act.duration_hours, act.duration_minutes, act.recurringtype, crm.smownerid AS assigned_user_id
				 FROM vtiger_activity act
				 INNER JOIN vtiger_seactivityrel sar ON sar.activityid = act.activityid
				 INNER JOIN vtiger_crmentity crm ON crm.crmid = act.activityid
				 WHERE sar.crmid = ? AND crm.deleted = 0 AND act.activitytype <> ?",
				array ($originalJobId, 'Job')
			);
			
			$taskCount = $this->adb->num_rows ($result);
			if ($taskCount == 0) {
				DatabaseUtils::closeResult ($result);
				return;
			}
			
			$tasks = array ();
			$oldestDate = null;
			while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
				$tasks[] = $row;
				if ($globalBaseDate === null) {
					if (!empty ($row['date_start']) && $row['date_start'] !== '0000-00-00') {
						$d = new DateTime ($row['date_start']);
						if ($oldestDate === null || $d < $oldestDate) {
							$oldestDate = $d;
						}
					}
					if (!empty ($row['due_date']) && $row['due_date'] !== '0000-00-00') {
						$d = new DateTime ($row['due_date']);
						if ($oldestDate === null || $d < $oldestDate) {
							$oldestDate = $d;
						}
					}
				}
			}
			DatabaseUtils::closeResult ($result);
			
			$today = new DateTime ();
			if ($globalBaseDate !== null) {
				$oldestDate = $globalBaseDate;
			}
			if ($oldestDate === null) {
				$oldestDate = $today;
			}
			
			// Preservar valores del request para restaurar después de guardar cada tarea
			$savedIsDuplicate = isset ($_REQUEST['isDuplicate']) ? $_REQUEST['isDuplicate'] : null;
			$savedRecord = isset ($_REQUEST['record']) ? $_REQUEST['record'] : null;
			
			foreach ($tasks as $row) {
				try {
					
					$activity = CRMEntity::getInstance ('Calendar');
					$activity->retrieve_entity_info ($row['activityid'], 'Calendar');
					$activity->id = '';
					$activity->mode = 'create';
					
					// Calcular nuevas fechas manteniendo el desplazamiento relativo a la fecha más antigua
					$newStartDate = null;
					$newDueDate = null;
					if (!empty ($row['date_start']) && $row['date_start'] !== '0000-00-00') {
						$originalStartDate = new DateTime ($row['date_start']);
						$diff = $oldestDate->diff ($originalStartDate);
						$newStartDate = clone $today;
						$newStartDate->add (new DateInterval ('P' . $diff->days . 'D'));
					}
					if (!empty ($row['due_date']) && $row['due_date'] !== '0000-00-00') {
						$originalDueDate = new DateTime ($row['due_date']);
						$diff = $oldestDate->diff ($originalDueDate);
						$newDueDate = clone $today;
						$newDueDate->add (new DateInterval ('P' . $diff->days . 'D'));
					}
					
					// La fecha estimada de finalización no puede ser anterior a la de inicio
					if ($newStartDate && $newDueDate && $newDueDate->format ('U') < $newStartDate->format ('U')) {
						$newDueDate = clone $newStartDate;
						$newDueDate->add (new DateInterval ('P5D'));
					}
					
					$activity->column_fields['date_start'] = $newStartDate ? $newStartDate->format ('Y-m-d') : $row['date_start'];
					$activity->column_fields['due_date'] = $newDueDate ? $newDueDate->format ('Y-m-d') : $row['due_date'];
					$activity->column_fields['status'] = 'Planned';
					$activity->column_fields['eventstatus'] = 'Planned';
					$activity->column_fields['progress'] = '0.00';
					$activity->column_fields['assigned_user_id'] = $currentUser->id;
					// Conservar valores estimados del original; el progreso y lo ejecutado quedan en cero
					$activity->column_fields['estimated_time'] = isset ($activity->column_fields['estimated_time']) ? $activity->column_fields['estimated_time'] : 0;
					$activity->column_fields['estimated_time_unit'] = isset ($activity->column_fields['estimated_time_unit']) ? $activity->column_fields['estimated_time_unit'] : 'Hora';
					$activity->column_fields['estimated_cost'] = isset ($activity->column_fields['estimated_cost']) ? $activity->column_fields['estimated_cost'] : 0;
					
					// Evitar que el save de Calendar herede isDuplicate/record del proyecto
					unset ($_REQUEST['isDuplicate']);
					unset ($_REQUEST['record']);
					
					$activity->save ('Calendar');
					$newActivityId = $activity->id;
					
					if (!empty ($newActivityId)) {
						// Relacionar con el nuevo trabajo
						$this->adb->pquery ('DELETE FROM vtiger_seactivityrel WHERE crmid=? AND activityid=?', array ($newJobId, $newActivityId));
						$this->adb->pquery ('INSERT INTO vtiger_seactivityrel (crmid, activityid) VALUES (?, ?)', array ($newJobId, $newActivityId));
						$this->adb->pquery ('UPDATE vtiger_activity SET related_id = ? WHERE activityid = ?', array ($newJobId, $newActivityId));
					}
				} catch (Exception $e) {
				}
			}
			
			// Restaurar valores del request
			if ($savedIsDuplicate !== null) {
				$_REQUEST['isDuplicate'] = $savedIsDuplicate;
			}
			if ($savedRecord !== null) {
				$_REQUEST['record'] = $savedRecord;
			}
			
		}
		
		/**
		 * Duplica las relaciones uitype=10 de un trabajo original a su copia,
		 * excluyendo el campo proyecto para evitar duplicar la relación con el proyecto anterior.
		 *
		 * @param integer $originalJobId
		 * @param integer $newJobId
		 */
		private function duplicateJobUitype10Relations ($originalJobId, $newJobId) {
			if (empty ($originalJobId) || empty ($newJobId)) {
				return;
			}
			
			$tabId = getTabid (self::JOB_RELATED_MODULE);
			$result = $this->adb->pquery (
				"SELECT f.fieldid, f.fieldname, f.columnname, rel.relmodule 
				 FROM vtiger_field f 
				 LEFT JOIN vtiger_fieldmodulerel rel ON f.fieldid = rel.fieldid 
				 WHERE f.tabid = ? AND f.uitype = 10 AND f.presence != 1 AND f.fieldname != 'proyecto'",
				array ($tabId)
			);
			
			while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
				$fieldName = $row['fieldname'];
				$relatedModule = $row['relmodule'];
				
				$originalEntity = CRMEntity::getInstance (self::JOB_RELATED_MODULE);
				$originalEntity->retrieve_entity_info ($originalJobId, self::JOB_RELATED_MODULE);
				
				$relatedRecordId = isset ($originalEntity->column_fields[$fieldName]) ? $originalEntity->column_fields[$fieldName] : '';
				if (empty ($relatedRecordId)) {
					continue;
				}
				
				$newJobEntity = CRMEntity::getInstance (self::JOB_RELATED_MODULE);
				$newJobEntity->save_related_module (
					self::JOB_RELATED_MODULE,
					$newJobId,
					$relatedModule,
					$relatedRecordId
				);
			}
			DatabaseUtils::closeResult ($result);
		}
		
		/**
		 * @param PearDatabase $adb
		 * @return taskToProject
		 */
		public static function getInstance (PearDatabase $adb) {
			return new self ($adb);
		}
	}
