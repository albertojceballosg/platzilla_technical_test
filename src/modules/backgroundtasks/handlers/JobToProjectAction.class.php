<?php
	require_once ('include/platzilla/Objects/BackgroundTaskActionHandler.php');
	require_once ('modules/proyectos/handlers/taskToProject.class.php');
	class JobToProjectAction extends BackgroundTaskActionHandler {
		
		protected $handlerTabs = array ('orden_de_trabajo', 'proyectos');
		
		/**
		 * @param integer $entityId
		 * @param string $moduleName
		 *
		 * @return CRMEntity|stdClass
		 * @throws Exception
		 */
		private function getCrmEntity ($moduleName, $entityId) {
			$this->logger->emit ('INFO', "Localizando el archivo {$moduleName}.php");
			$moduleFilePath = "modules/{$moduleName}/{$moduleName}.php";
			if (!file_exists (__DIR__ . "/../../../{$moduleFilePath}")) {
				throw new Exception ("No se encuentra el archivo {$moduleFilePath}");
			}
		
			$this->logger->emit ('INFO', "Creando objeto del tipo {$moduleName}");
			$entity = PlatformUtils::getCrmEntity ($this->adb, $moduleName, $entityId);
			return $entity;
		}
		
		private function getProjectStage () {
			$result = $this->adb->query (
				'SELECT
       					etapas_proyectoid
					FROM
					     vtiger_etapas_proyecto
					ORDER BY etapas_proyectoid ASC
					LIMIT 1'
			);
			
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$stageId = $row ['etapas_proyectoid'];
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return (isset ($stageId)) ? $stageId : null;
		}
		/**
		 * @param $entityId
		 * @return mixed|null
		 * @throws Exception
		 */
		private function validateEntity ($entityId) {
			if (empty($entityId) || !is_numeric ($entityId)) {
				throw new Exception ('No se han suministrado los parámetros de la acción');
			}
			
			$result = $this->adb->pquery ('SELECT setype FROM vtiger_crmentity WHERE crmid=?', array ($entityId));
			
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$moduleName = $row ['setype'];
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return (isset ($moduleName) && in_array ($moduleName, $this->handlerTabs)) ? $moduleName : null;
		}
		
		/**
		 * @inheritDoc
		 */
		public function run ($action)  {
			if (empty ($action)) {
				throw new Exception ('No se ha suministrado la acción');
			}
			
			$parameters = $action->getParameters ();
			if (empty ($parameters)) {
				throw new Exception ('No se han suministrado los parámetros de la acción');
			}
			
			$this->logger->emit ('INFO', 'Obteniendo parámetros');
			$requestedParameterNames = array ('jobId', 'projectId');
			$parameterValues         = $this->getParameterValues ($parameters, $requestedParameterNames, true);
			$jobId                   = $parameterValues ['jobId'];
			$projectId               = $parameterValues ['projectId'];
			
			$this->logger->emit ('INFO', 'Validando registros: Proyecto: '. $projectId. '  Trabajo: '. $jobId );
			$projectTab = $this->validateEntity ($projectId);
			if (empty ($projectTab)) {
				throw new Exception ('El parámetro: ' . $projectId . ' No corresponde a un registro de proyectos');
			}
			$jobTab = $this->validateEntity ($jobId);
			if (empty ($jobTab)) {
				throw new Exception ('El parámetro: ' . $jobId . ' No corresponde a un registro de Trabajos');
			}
			$entity = $this->getCrmEntity ($jobTab, $jobId);
			
			$startDate = date($entity->column_fields ['fecha_de_inicio']);
			$endDate   = date ('Y-m-d', strtotime ($startDate . '+ 1 days'));
			$projectStage = $this->getProjectStage ();
			$taskToProjectClass = taskToProject::getInstance ($this->adb);
			$projectWork        = ProjectWorks::getInstance ()
				->setCrmId (intval ($projectId))
				->setCrmIdJob (intval ($jobId))
				->setJobContributionFactor (0.00)
				->setJobName ($entity->column_fields ['titulo'])
				->setPercentageCompletion (0.00)
				->setProjectProgress (0.00)
				->setResponsibleJob (intval ($entity->column_fields ['assigned_user_id']))
				->setStageId ($projectStage)
				->setStartDate ($entity->column_fields ['fecha_de_inicio'])
				->setEstimatedDueDate ($endDate)
				->setSummaryStr (null);
			$this->logger->emit ('INFO', 'Registrando el trabajo en la tabla de trabajos del proyecto.' );
			$projectJobId = $taskToProjectClass->saveProjectWork ($projectWork);
			return $projectJobId;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param Logger|null $logger
		 * @param $platform
		 * @return JobToProjectAction|null
		 */
		public static function getInstance (PearDatabase $adb, Logger $logger = null, $platform = null) {
			return new self ($adb, $logger, $platform);
		}
	}