<?php
	require_once ('data/CRMEntity.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/TableFieldUtils.php');
	require_once ('modules/model_action_plan/Objects/ModuleColumnFields.php');
	require_once ('modules/model_action_plan/Objects/ModelActionPlanInterface.php');
	require_once ('modules/business_initiatives/handlers/ResourceToInitiative.class.php');
	require_once ('modules/proyectos/handlers/taskToProject.class.php');
	
	class ModelActionPlanHelper implements ModelActionPlanInterface {
		
		/** @var PearDatabase */
		private $adb;
		
		/** @var string */
		private $createDate;
		
		/** @var PearDatabase */
		private $masterAdb;
		
		/** @var string */
		private $platform;
		
		/**
		 * ModelActionPlanHelper constructor.
		 * @param PearDatabase $adb
		 * @param string $platform
		 */
		public function __construct (PearDatabase $adb, $platform) {
			$this->adb        = $adb;
			$this->masterAdb  = AdbManager::getInstance ()->getMasterAdb ();
			$this->platform   = $platform;
			$this->createDate = date('Y-m-d h:i:s');
		}
		
		/**
		 * @param ProjectWorks[] $projectWorks
		 * @param integer $crmId
		 * @param integer[] $workIds
		 *
		 * @throws Exception
		 * @throws WebServiceException
		 */
		private function createProjectWork ($projectWorks, $crmId, $workIds) {
			if (!is_array ($projectWorks) || empty ($crmId) || empty ($workIds)) {
				return;
			}
			$today = date('Y-m-d');
			foreach ($projectWorks as $projectWork) {
				if (!$projectWork instanceof  ProjectWorks) {
					continue;
				}
				$summaryRow                      = json_decode ($projectWork->getSummaryStr (), true);
				$summaryRow ['project_progress'] = null;
				
				$projectWork->setCrmId ($crmId);
				$crmIdJob = $workIds[ $projectWork->getJobName () ];
				$projectWork->setCrmIdJob ($crmIdJob);
				$projectWork->setJobName ($workIds [$crmIdJob]);
				$projectWork->setResponsibleJob (1);
				$stageId = $this->getProjectStage ($projectWork->getStageId ());
				$projectWork->setStageId ($stageId);
				$projectWork->setStartDate ($today);
				$projectWork->setEstimatedDueDate (null);
				$projectWork->setProjectProgress (0.00);
				$projectWork->setPercentageCompletion (0.00);
				$projectWork->setSummaryStr ($summaryRow);
				taskToProject::getInstance ($this->adb)->saveProjectWork ($projectWork);
			}
		}
		
		/**
		 * @param ResourcesForExecution[] $resourcesTable
		 *
		 * @throws Exception
		 * @throws WebServiceException
		 */
		private function createResource (&$resourcesTable) {
			if (empty ($resourcesTable)) {
				return;
			}
			foreach ($resourcesTable as $resourceTable) {
				if (!$resourceTable instanceof ResourcesForExecution) {
					continue;
				} else if (
					!empty ($resourceTable->getResource ()) &&
					($resourceTable->getResource ()['record_module'] == taskToProject::JOB_RELATED_MODULE)
				) {
					$tasksId        = $this->createTasksResource ($resourceTable->getResource ()[self::WORK_TABLE_FIELD]);
					$moduleData     = ModuleColumnFields::getInstance ();
					$resourceEntity = CRMEntity::getInstance ($resourceTable->getTypeResource ());
					$moduleData->setWorkOrder ($resourceTable->getResource (), $resourceEntity);
					$resourceEntity->mode                = 'create';
					$resourceEntity->id                  = null;
					$resourceEntity->column_fields       = $moduleData->getWorkOrder ();
					$resourceEntity->save ($resourceTable->getTypeResource ());
					$this->setRelatedTasksToResource ($resourceEntity->id, $tasksId);
					$resourceTable->setIdResource ($resourceEntity->id);
					unset ($resourceEntity);
					unset ($moduleData);
				} else if (
					!empty ($resourceTable->getResource ()) &&
					($resourceTable->getResource ()['record_module'] == self::PROJECT_MODULE)
				) {
					$workIds        = $this->createWorkResource ($resourceTable->getResource ()[self::PROJECT_TABLE_FIELD]);
					$projectJobs    = $resourceTable->getResource () [self::PROJECT_TABLE_FIELD]['projects_jobs'];
					$moduleData     = ModuleColumnFields::getInstance ();
					$resourceEntity = CRMEntity::getInstance ($resourceTable->getTypeResource ());
					$moduleData->setProjects ($resourceTable->getResource (), $resourceEntity);
					$resourceEntity->mode          = 'create';
					$resourceEntity->id            = null;
					$resourceEntity->column_fields = $moduleData->getProjects ();
					$resourceEntity->save ($resourceTable->getTypeResource ());
					$resourceTable->setIdResource ($resourceEntity->id);
					$this->createProjectWork ($projectJobs, $resourceEntity->id, $workIds);
					unset ($resourceEntity);
					unset ($moduleData);
				} else if ($resourceTable->getTypeResource () == self::CAMPAIGN_MODULE)  {
					$tasksId = $this->createTasksResource ($resourceTable->getResource ()[self::CAMPAIGN_MODULE_TASK]);
					unset ($resourceTable->getResource ()[self::CAMPAIGN_MODULE_TASK]);
					$moduleData     = ModuleColumnFields::getInstance ();
					$resourceEntity = CRMEntity::getInstance ($resourceTable->getTypeResource ());
					$moduleData->setCampaignMarketing ($resourceTable->getResource (), $resourceEntity);
					$resourceEntity->mode          = 'create';
					$resourceEntity->id            = null;
					$resourceEntity->column_fields = $moduleData->getCampaignMarketing ();
					$resourceEntity->save ($resourceTable->getTypeResource ());
					$this->setRelatedTasksToResource ($resourceEntity->id, $tasksId);
					$resourceTable->setIdResource ($resourceEntity->id);
					unset ($resourceEntity);
					unset ($moduleData);
				}
			}
		}
		
		/**
		 * @param $tasks
		 *
		 * @return array|null
		 * @throws Exception
		 * @throws WebServiceException
		 */
		private function createTasksResource ($tasks) {
			if (empty($tasks)) {
				return null;
			}
			$today      = date ('Y-m-d');
			foreach ($tasks as $task) {
				if (empty ($task)) {
					continue;
				}
				unset ($task['record_id']);
				unset ($task['record_module']);
				$task ['eventstatus']      = ($task['eventstatus'] != 'Planned') ? 'Planned' : $task['eventstatus'];
				$task ['show_in_matrix']   = ($task['show_in_matrix'] != 'YES') ? 'YES' : $task['show_in_matrix'];
				$task ['assigned_user_id'] = 1;
				$task ['activitytype']     = 'Activity';
				$task ['createdtime']      = $this->createDate;
				$task ['modifiedtime']     = $this->createDate;
				$task ['date_start']       = $today;
				$task ['due_date']         = $today;
				$activity  = CRMEntity::getInstance ('Calendar');
				$activity->mode          = 'create';
				$activity->id            = null;
				$activity->column_fields = $task;
				$activity->save ('Calendar');
				$tasksId[] = $activity->id;
				unset ($activity);
			}
			return (isset($tasksId)) ? $tasksId : null;
		}
		
		/**
		 * @param array $works
		 *
		 * @return array|null
		 * @throws Exception
		 * @throws WebServiceException
		 */
		private function createWorkResource ($works) {
			if (empty($works)) {
				return null;
			}
			foreach ($works ['records'] as $work) {
				if ($work ['record_module'] != taskToProject::JOB_RELATED_MODULE) {
					continue;
				}
				$actualCode = $work ['cod_orden_de_tra'];
				$moduleData = ModuleColumnFields::getInstance ();
				$taskWork   = $work [ self::WORK_TABLE_FIELD ];
				$activity   = CRMEntity::getInstance (taskToProject::JOB_RELATED_MODULE);
				$moduleData->setWorkOrder ($work, $activity);
				$activity->mode          = 'create';
				$activity->id            = null;
				$activity->column_fields = $moduleData->getWorkOrder ();
				$activity->save (taskToProject::JOB_RELATED_MODULE);
				if (!empty ($taskWork)) {
					$tasksId  = $this->createTasksResource ($taskWork);
					$this->setRelatedTasksToResource ($activity->id, $tasksId);
				}
				
				$workId [$actualCode] = $activity->id;
				$activity->retrieve_entity_info ($activity->id, taskToProject::JOB_RELATED_MODULE);
				$workId [$activity->id] = $activity->column_fields ['cod_orden_de_tra'];
				unset ($taskWork);
				unset ($activity);
				unset ($moduleData);
			}
			return (isset ($workId)) ? $workId : null;
		}
		
		/**
		 * @param null|array $planDestination
		 *
		 * @return null|array
		 */
		private function fetchActionPlans ($planDestination) {
			if (empty ($planDestination)) {
				return null;
			}
			$entity = CRMEntity::getInstance (ModelActionPlanInterface::ACTION_PLAN_MODULE);
			foreach ($planDestination ['destination_action_planid'] as $actionPlanId) {
				if (empty ($actionPlanId)) {
					continue;
				}
				$entity->retrieve_entity_info ($actionPlanId, ModelActionPlanInterface::ACTION_PLAN_MODULE);
				if (!empty ($entity->column_fields['informative_video'])) {
					$entity->column_fields ['video_type'] = $this->getVideoType ($entity->column_fields['informative_video']);
				}
				$actionPlans [] = $entity->column_fields;
			}
			return (isset ($actionPlans)) ? $actionPlans : null;
		}
		
		/**
		 * @param ResourcesForExecution[] $resourcesTable
		 *
		 * @throws Exception
		 */
		private function fetchResource (&$resourcesTable) {
			if (empty ($resourcesTable)) {
				return;
			}
			foreach ($resourcesTable as $resourceTable) {
				if (!$resourceTable instanceof ResourcesForExecution) {
					continue;
				}
				$resourceEntity  = CRMEntity::getInstance ($resourceTable->getTypeResource ());
				$resourceEntity->retrieve_entity_info ($resourceTable->getIdResource (), $resourceTable->getTypeResource ());
				if (in_array (self::PROJECT_TABLE_FIELD, array_keys ($resourceEntity->column_fields))) {
					$resourceEntity->column_fields[ self::PROJECT_TABLE_FIELD ] = $this->fetchWorksResource ($resourceEntity->column_fields['record_id']);
				} elseif (in_array (self::WORK_TABLE_FIELD, array_keys ($resourceEntity->column_fields))) {
					$resourceEntity->column_fields[ self::WORK_TABLE_FIELD ] = $this->fetchTasksResource ($resourceEntity->column_fields['record_id']);
				} elseif ($resourceTable->getTypeResource () == self::CAMPAIGN_MODULE) {
					$resourceEntity->column_fields [self::CAMPAIGN_MODULE_TASK] = $this->fetchTasksResource ($resourceEntity->column_fields['record_id']);
				}
				$resourceTable->setResource ($resourceEntity->column_fields);
			}
			unset($resourceEntity);
		}
		
		/**
		 * @param integer $crmId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		private function fetchTasksResource ($crmId) {
			if (empty ($crmId)) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT activityid FROM vtiger_seactivityrel WHERE crmid=?', array ($crmId));
			if ($this->adb->num_rows ($result) > 0) {
				$activity  = CRMEntity::getInstance ('Calendar');
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$activity->retrieve_entity_info ($row ['activityid'], 'Calendar');
					$tasks [] = $activity->column_fields;
				}
				unset ($activity);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($tasks)) ? $tasks : null;
		}
		
		/**
		 * @param integer $crmId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		private function fetchWorksResource ($crmId) {
			if (empty($crmId)) {
				return null;
			}
			$projectsJobs = taskToProject::getInstance ($this->adb)->fetchRelatedWork ($crmId, null, null);
			if (!empty ($projectsJobs)) {
				$activity       = CRMEntity::getInstance (taskToProject::JOB_RELATED_MODULE);
				$activity->mode = 'edit';
				foreach ($projectsJobs as $projectsJob) {
					if (!$projectsJob instanceof ProjectWorks) {
						continue;
					}
					$activity->retrieve_entity_info ($projectsJob->getCrmIdJob (), taskToProject::JOB_RELATED_MODULE);
					$activity->column_fields[ self::WORK_TABLE_FIELD ] = $this->fetchTasksResource ($activity->column_fields['record_id']);
					$jobs ['records'][] = $activity->column_fields;
				}
				$jobs ['projects_jobs'] = $projectsJobs;
			}
			return (isset ($jobs)) ? $jobs : null;
		}
		
		/**
		 * @param integer $stageId
		 * @return integer|null
		 *
		 * @throws Exception
		 * @throws WebServiceException
		 */
		private function getProjectStage ($stageId) {
			if (empty ($stageId)) {
				return null;
			}
			$result = $this->masterAdb->pquery ('SELECT titulo, descripcion FROM vtiger_etapas_proyecto WHERE etapas_proyectoid=?', array ($stageId));
			if ($this->masterAdb->num_rows ($result) > 0) {
				$row         = $this->masterAdb->fetchByAssoc ($result, -1, false);
				$title       = $row ['titulo'];
				$description = $row ['descripcion'];
				DatabaseUtils::closeResult ($result);
				$result = null;
				$result = $this->adb->pquery ('SELECT etapas_proyectoid FROM vtiger_etapas_proyecto WHERE titulo=? AND descripcion=?  LIMIT 1', array ($title, $description));
				if ($this->adb->num_rows ($result)) {
					$row = $this->adb->fetchByAssoc ($result, -1, false);
					$stageProjectId = $row ['etapas_proyectoid'];
				} else {
					$moduleData = ModuleColumnFields::getInstance ();
					$entity = CRMEntity::getInstance (self::PROJECT_STEPS_MODULE);
					$moduleData->setStageProjects (
						array ('descripcion' => $description, 'titulo' => $title),
						$entity
					);
					$entity->id   = null;
					$entity->mode = 'create';
					$entity->column_fields = $moduleData->getStageProjects ();
					$entity->save (self::PROJECT_STEPS_MODULE);
					$stageProjectId = $entity->id;
					unset ($entity);
					unset ($moduleData);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return isset($stageProjectId) ? $stageProjectId : null;
		}
		
		/**
		 * @param string $urlVideo
		 *
		 * @return null|string
		 */
		private function getVideoType ($urlVideo) {
			if (strpos ($urlVideo,'vimeo.com') !== false) {
				return 'VIMEO';
			} else if (strpos ($urlVideo,'youtube.com') !== false) {
				return 'YOUTUBE';
			} else {
				return null;
			}
		}
		
		/**
		 * @param ResourcesForExecution[] $resourcesTable
		 * @param integer $crmId
		 *
		 * @throws Exception
		 */
		private function saveResourceInitiative ($resourcesTable, $crmId) {
			if (empty ($resourcesTable)) {
				return;
			}
			foreach ($resourcesTable as $resourceTable) {
				if (!$resourceTable instanceof ResourcesForExecution) {
					continue;
				}
				$resourceTable->setId ($crmId);
				ModuleColumnFields::getInstance ()->updateResourceInitiative ($resourceTable, $crmId);
				$resourceTable->setResourceProgress (0);
				$resourceTable->setTotalContribution (0);
			}
			ResourceToInitiative::getInstance ($this->adb)->saveResourceInitiative ($resourcesTable, $crmId);
		}
		
		/**
		 * @param integer $crmId
		 * @param array $tasksId
		 */
		private function setRelatedTasksToResource ($crmId, $tasksId) {
			if (!empty ($crmId) && !empty ($tasksId)) {
				foreach ($tasksId as $taskId) {
					$this->adb->pquery ('INSERT IGNORE INTO vtiger_seactivityrel (crmid, activityid) VALUES (?, ?)', array($crmId, $taskId));
				}
			}
		}
		
		/**
		 * @param array $destinationData
		 * @param integer $planId
		 * @param integer $actionPlanId
		 */
		private function updateDestinationTableField (&$destinationData, $planId, $actionPlanId) {
			$destinationTableFields = array_keys ($destinationData['plan_destination']);
			$totalRows              = count ($destinationData['plan_destination'][ self::ID_ACTION_PLAN ]);
			$rowIndex               = 0;
			for ($k = 0; $k < $totalRows; $k++) {
				if ($destinationData['plan_destination'][ self::ID_ACTION_PLAN ][$k] == $planId) {
					$destinationData['plan_destination'][ self::ID_ACTION_PLAN ][$k]    = $actionPlanId;
					$destinationData['plan_destination'][ self::CRMID_ACTION_PLAN ][$k] = null;
					$rowIndex = $k;
					continue;
				} else {
					foreach ($destinationTableFields as $fieldName) {
						unset ($destinationData['plan_destination'][ $fieldName ][$k]);
					}
				}
			}
			if ($rowIndex != 0) {
				foreach ($destinationTableFields as $fieldName) {
					$destinationData['plan_destination'][ $fieldName ][0] = $destinationData['plan_destination'][ $fieldName ][$rowIndex];
				}
				foreach ($destinationTableFields as $fieldName) {
					unset ($destinationData['plan_destination'][ $fieldName ][$rowIndex]);
				}
			}
		}
		
		/**
		 * @param integer $crmId
		 *
		 * @return boolean
		 */
		private function validateRecord ($crmId) {
			$result = $this->adb->pquery (
				'SELECT setype FROM vtiger_crmentity WHERE crmid=? AND deleted=?',
				array ($crmId, 0)
			);
			DatabaseUtils::closeResult ($result);
			return ($this->adb->num_rows ($result) > 0);
		}
		
		/**
		 * @param integer $idDestination
		 * @param integer $idPlan
		 * @param array $initiatives
		 *
		 * @return array
		 * @throws Exception
		 */
		public function copyModelActionPlan ($idDestination, $idPlan, $initiatives) {
			$entity        = CRMEntity::getInstance (ModelActionPlanInterface::INITIATIVES_MODULE);
			$resourceClass = ResourceToInitiative::getInstance ($this->masterAdb);
			$index         = 0;
			foreach ($initiatives as $initiativeId) {
				if (empty ($initiativeId)) {
					continue;
				}
				$entity->retrieve_entity_info ($initiativeId, ModelActionPlanInterface::INITIATIVES_MODULE);
				$sourceIndex = "{$initiativeId}-{$index}";
				$sourceInitiative [$sourceIndex]['record'] = $entity->column_fields;
				$resourcesTable                             = $resourceClass->fetchResourceInitiatives ($entity->column_fields['record_id']);
				$this->fetchResource ($resourcesTable);
				$sourceInitiative [$sourceIndex]['resource_table'] = $resourcesTable;
				$index += 1;
				unset ($resourcesTable);
			}
			$entity  = CRMEntity::getInstance (ModelActionPlanInterface::ACTION_PLAN_MODULE);
			$entity->retrieve_entity_info ($idPlan, ModelActionPlanInterface::ACTION_PLAN_MODULE);
			
			$modelPlan ['plan']        = $entity->column_fields;
			$modelPlan ['initiatives'] = (isset($sourceInitiative)) ? $sourceInitiative : null;
			$entity                    = CRMEntity::getInstance (ModelActionPlanInterface::DESTINATION_MODULE);
			$entity->retrieve_entity_info ($idDestination, ModelActionPlanInterface::DESTINATION_MODULE);
			$modelPlan ['destination'] = $entity->column_fields;
			return $modelPlan;
		}
		
		/**
		 * @param array $destinationData
		 * @param integer $planId
		 * @param integer $actionPlanId
		 *
		 * @return null|integer
		 * @throws Exception
		 * @throws WebServiceException
		 */
		public function createDestination ($destinationData, $planId, $actionPlanId) {
			$this->updateDestinationTableField ($destinationData, $planId, $actionPlanId);
			$moduleData = ModuleColumnFields::getInstance ();
			$entity     = CRMEntity::getInstance (ModelActionPlanInterface::DESTINATION_MODULE);
			$moduleData->setBusinessDestination ($destinationData, $entity);
			$entity->id            = null;
			$entity->mode          = 'create';
			$entity->column_fields = $moduleData->getBusinessDestination ();
			$entity->save (ModelActionPlanInterface::DESTINATION_MODULE);
			if (
				isset ($destinationData['plan_destination']['summaryrow']) &&
				!empty ($destinationData['plan_destination']['summaryrow'])
			) {
				$summaryRow = json_decode ($destinationData['plan_destination']['summaryrow'][0],true);
				$destinationData['plan_destination']['summaryRow'] = $summaryRow;
			}
			$arguments = array (
				'module'      => ModelActionPlanInterface::DESTINATION_MODULE,
				'recordId'    => $entity->id,
				'requestData' => $destinationData,
			);
			$destinationId = $entity->id;
			TableFieldUtils::getInstance ($this->adb)->saveTableFields ($arguments);
			unset ($entity);
			unset ($moduleData);
			$entity  = CRMEntity::getInstance (ModelActionPlanInterface::ACTION_PLAN_MODULE);
			$entity->id   = $actionPlanId;
			$entity->mode = 'edit';
			$entity->retrieve_entity_info ($actionPlanId, ModelActionPlanInterface::ACTION_PLAN_MODULE);
			$entity->column_fields['business_destination'] = $destinationId;
			$entity->save (ModelActionPlanInterface::ACTION_PLAN_MODULE);
			return isset ($destinationId) ? $destinationId : null;
		}
		
		/**
		 * @param array $source
		 *
		 * @return null|integer
		 * @throws Exception
		 * @throws WebServiceException
		 */
		public function createModelActionPlan ($source) {
			$initiativesId = array ();
			$actionPlanId  = null;
			$today         = date ('Y-m-d');
			if (!empty ($source['initiatives'])) {
				foreach ($source['initiatives'] as $initiativeId => $initiative) {
					$this->createResource ($initiative ['resource_table']);
					if (!empty ($initiative ['record'])) {
						$moduleData  = ModuleColumnFields::getInstance ();
						$entity      = CRMEntity::getInstance (self::INITIATIVES_MODULE);
						$moduleData->setBusinessInitiatives ($initiative ['record'], $entity);
						$entity->id            = null;
						$entity->mode          = 'create';
						$entity->column_fields = $moduleData->getBusinessInitiatives ();
						$entity->save (self::INITIATIVES_MODULE);
						$this->saveResourceInitiative ($initiative['resource_table'], $entity->id);
						$initiativesId [] = $entity->id;
						unset ($entity);
						unset ($moduleData);
					}
				}
			}
			if (!empty ($source['plan']['plan_directives'])) {
				$totalRow = count ($source['plan']['plan_directives']['action_plantfid']);
				for ($k = 0;$k< $totalRow; $k++) {
					$source['plan']['plan_directives']['action_plantfid'][$k] = null;
				}
			}
			if (!empty ($source['plan']['plan_initiatives'])) {
				$totalRow = count ($source['plan']['plan_initiatives']['action_plantfid']);
				for ($k = 0;$k< $totalRow; $k++) {
					$source ['plan']['plan_initiatives']['action_plantfid'][$k]      = null;
					$source ['plan']['plan_initiatives']['plan_initiativeid'][$k]    = $initiativesId[$k];
					$source ['plan']['plan_initiatives']['init_date'][$k]            = $today;
					$source ['plan']['plan_initiatives']['progress_initiative_'][$k] = '0.00';
					$source ['plan']['plan_initiatives']['progress_plan'][$k]        = '0.00';
				}
			}
			$source ['plan']['record_id']           = null;
			$source ['plan']['init_date']           = $today;
			$source ['plan']['"overall_progress']   = 0;
			$source ['plan']['estimated_end_date']  = '';
			$entity  = CRMEntity::getInstance (ModelActionPlanInterface::ACTION_PLAN_MODULE);
			$entity->id            = null;
			$entity->mode          = 'create';
			$entity->column_fields = $source['plan'];
			$entity->save (ModelActionPlanInterface::ACTION_PLAN_MODULE);
			if (
				isset ($source['plan']['plan_initiatives']['summaryrow']) &&
				!empty ($source['plan']['plan_initiatives']['summaryrow'])
			) {
				$source['plan']['plan_initiatives']['summaryrow']['progress_plan'][0] = '0.00';;
				$summaryRow                   = json_decode ($source['plan']['plan_initiatives']['summaryrow'][0],true);
				$summaryRow ['progress_plan'] = '0.00';
				$source['plan']['plan_initiatives']['summaryRow'] = $summaryRow;
			}
			
			$arguments = array (
				'module'      => ModelActionPlanInterface::ACTION_PLAN_MODULE,
				'recordId'    => $entity->id,
				'requestData' => $source['plan'],
			);
			$actionPlanId = $entity->id;
			TableFieldUtils::getInstance ($this->adb)->saveTableFields ($arguments);
			return $actionPlanId;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public function fetchModelByDestinationId ($id) {
			if (empty ($id)) {
				throw new Exception('Uops! Aún no se ha seleccionado el destino');
			} else if (!$this->validateRecord ($id)) {
				return null;
			}
			$entity = CRMEntity::getInstance (ModelActionPlanInterface::DESTINATION_MODULE);
			$entity->retrieve_entity_info ($id, ModelActionPlanInterface::DESTINATION_MODULE);
			$model ['destination'] = $entity->column_fields;
			$model['plans']        = $this->fetchActionPlans ($entity->column_fields [ModelActionPlanInterface::PLAN_DESTINATION]);
			return $model;
		}
		
		/**
		 * @param string $email
		 *
		 * @return null|string
		 * @throws Exception
		 */
		public function getInstanceCode ($email) {
			$result = $this->masterAdb->pquery (
				'SELECT code, name FROM vtiger_instances WHERE administrator=? AND status=?',
				array ($email, ModelActionPlanInterface::INSTANCE_STATUS)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row  = $this->adb->fetchByAssoc ($result, -1, false);
				$code[] = $row['code'];
				$code[] = $row['name'];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($code)) ? $code : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $platform
		 *
		 * @return ModelActionPlanHelper
		 */
		public static function getInstance (PearDatabase $adb, $platform) {
			return new self ($adb, $platform);
		}
		
		/**
		 * @param integer $actionPlanId
		 * @param integer $destinationId
		 * @param string $code
		 *
		 * @throws Exception
		 */
		public function saveActionPlanExported ($destinationId, $planId, $idDestination, $actionPlanId, $code) {
			if (empty ($actionPlanId) || empty ($idDestination) || empty ($code)) {
				throw new Exception('El plan ha sido instalado! pero fue imposible guardar datos de exportación del plan, code '. $code . ' iddestino '. $idDestination. ' plan '.$actionPlanId);
			}
			$this->adb->pquery (
				"INSERT INTO vtiger_exported_action_plan (source_destinationid, source_planid, business_destinationid, action_planid, code) VALUES (?, ?, ?, ?, ?)",
				array ($destinationId, $planId, $idDestination, $actionPlanId, $code)
			);
		}
        
        /**
         * @param integer $diagnosticId
         * @param integer $idDestination
         * @param integer $actionPlanId
         * @return void
         */
        public function updateDiagnosticReport ($diagnosticId, $idDestination, $actionPlanId) {
            if (empty ($diagnosticId) || empty ($actionPlanId)|| empty ($idDestination)) {
                return;
            }
            $this->adb->pquery('UPDATE vtiger_diagnostic_report SET destination=?, action_plan=? WHERE diagnostic_reportid=?', array ($idDestination, $actionPlanId, $diagnosticId));
        }
		
	}
