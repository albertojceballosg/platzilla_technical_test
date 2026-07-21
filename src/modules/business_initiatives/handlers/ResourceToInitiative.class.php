<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('modules/business_initiatives/Objects/ResourcesForExecution.php');
	
	class ResourceToInitiative {
		
		/** @var PearDatabase */
		protected $adb;
		
		/** @var string  */
		protected $errorMessage = '';
		
		protected $fieldName = 'resource_initiative';
		
		/** @var PearDatabase */
		protected $masterAdb;
		
		public function __construct($adb) {
			$this->masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			$this->adb       = $adb;
		}
		
		/**
		 * @param ResourcesForExecution[] $resources
		 *
		 * @throws Exception
		 */
		private function checkForUpdates (&$resources) {
			$tableName = $this->getTableName ();
			foreach ($resources as $resource) {
				if (!$resource instanceof ResourcesForExecution) {
					continue;
				}
				$retrieveField   = ResourceInterface::MODULES_FACTOR_FIELD[ $resource->getTypeResource () ];
				$entityFieldName = $this->fetchEntityFieldName ($resource->getTypeResource ());
				$entity        = CRMEntity::getInstance ($resource->getTypeResource ());
				$entity->retrieve_entity_info ($resource->getIdResource (), $resource->getTypeResource ());
				$factor        = floatval ($entity->column_fields [ $retrieveField ]);
				$fieldResource = (!empty ($entityFieldName)) ? $entity->column_fields [ $entityFieldName ] : $resource->getResourceDescription ();
				if (
					$factor != $resource->getResourceProgress () ||
					$fieldResource != $resource->getResourceDescription()
				) {
					$resource->setResourceDescription ($entity->column_fields [ $entityFieldName ]);
					$resource->setResourceProgress ($factor);
					$resource->setTotalContribution ((($factor * $resource->getContributionFactor ()) / 100));
					$this->updateResourceInitiative ($tableName, $resource);
				}
			}
		}
		
		/**
		 * @param string $tableName
		 * @param integer $recordId
		 *
		 * @throws Exception
		 */
		private function delRecordById ($tableName, $recordId) {
			if (empty ($tableName)) {
				$tableName = $this->getTableName ();
			}
			if (!$recordId) {
				return;
			}
			$this->adb->query ("DELETE  FROM {$tableName} WHERE recurse_initiativeid={$recordId}");
		}
		
		/**
		 * @param string $moduleName
		 *
		 * @return string|null
		 * @throws Exception
		 */
		private function fetchEntityFieldName ($moduleName) {
			$result = $this->adb->pquery ('SELECT fieldname FROM vtiger_entityname WHERE modulename=?', array ($moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				$row              = $this->adb->fetchByAssoc ($result, -1, false);
				$entityIdentifier = $row ['fieldname'];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($entityIdentifier)) ? $entityIdentifier : null;
		}
		
		/**
		 * @return string
		 *
		 * @throws Exception
		 */
		private function getTableName () {
			$field = FieldManager::getInstance ($this->adb)->fetchFieldByName ('business_initiatives', $this->fieldName, true);
			if (!$field instanceof Field) {
				throw new Exception('Tabla de recurso no definida');
			}
			return "vtiger_{$this->fieldName}_ft{$field->getId ()}";
		}
		
		/**
		 * @param string $tableName
		 * @param ResourcesForExecution $resource
		 */
		private function updateResourceInitiative ($tableName, $resource) {
			if (!$resource instanceof ResourcesForExecution) {
				return;
			}
			$this->adb->pquery (
				"UPDATE {$tableName} SET field_resource=?, resource_progress=?, total_contribution=? WHERE crmid_resource=? AND recurse_initiativeid=?",
				array($resource->getResourceDescription(), $resource->getResourceProgress (), $resource->getTotalContribution (), $resource->getIdResource (), $resource->getId ())
			);
		}
		
		/**
		 * @param integer $userId
		 * @param integer $crmId
		 * @param string $mode
		 *
		 * @throws Exception
		 */
		public function buildResourceInitiatives ($userId, $crmId, $mode) {
			if (empty ($userId) || empty($crmId)) {
				throw new Exception ('Uoops! algo salio mal, intenta de nuevo');
			} else if (!isset($_REQUEST['resource_initiatives']) || empty ($_REQUEST['resource_initiatives'])) {
				if  ($mode == 'edit') {
					$this->delRecordById ('', intval ($crmId));
				}
				throw new Exception ('No hay recursos para la ejecución de la iniciativa');
			} else if (!count ($_REQUEST['resource_initiatives']['total_contribution'])) {
				throw new Exception ('No hay recursos para la ejecución de la iniciativa');
			}
			$totalResource    = count ($_REQUEST['resource_initiatives']['total_contribution']);
			$requestResources = $_REQUEST['resource_initiatives'];
			$resources        = array ();
			
			for ($k = 0 ; $k < $totalResource; $k++) {
				$resources [] = ResourcesForExecution::getInstance ()
					->setContributionFactor (floatval ($requestResources['contribution_factor'][$k]))
					->setId (intval ($crmId))
					->setIdResource (intval ($requestResources['crmid_resource'][$k]))
					->setResourceDescription ($requestResources['field_resource'][$k])
					->setResourceProgress (floatval ($requestResources['resource_progress'][$k]))
					->setSummaryContribution (floatval ($requestResources['summary_contribution']))
					->setSummaryFactor (floatval ($requestResources['summary_factor']))
					->setTotalContribution (floatval ($requestResources['total_contribution'][$k]))
					->setTypeResource ($requestResources['type_resource'][$k]);
			}
			if (count ($resources)) {
				$this->saveResourceInitiative ($resources, intval ($crmId));
			}
		}
		
		/**
		 * @param integer $crmId
		 *
		 * @return ResourcesForExecution[]| null
		 * @throws Exception
		 */
		public function fetchResourceInitiatives ($crmId) {
			$tableName = $this->getTableName ();
			$result    = $this->adb->pquery ("SELECT * FROM {$tableName} WHERE recurse_initiativeid=?", array($crmId));
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$resources [] = ResourcesForExecution::getInstance ()
						->setContributionFactor (floatval ($row ['contribution_factor']))
						->setId (intval ($row ['recurse_initiativeid']))
						->setIdResource ($row ['crmid_resource'])
						->setResourceDescription ($row ['field_resource'])
						->setResourceProgress (floatval ($row ['resource_progress']))
						->setSummaryContribution (floatval ($row ['summary_contribution']))
						->setSummaryFactor (floatval ($row ['summary_factor']))
						->setTotalContribution (floatval ($row ['total_contribution']))
						->setTypeResource ($row['type_resource']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($resources)) ? $resources : null;
		}
		
		/**
		 * @param $crmId
		 * @param null $view
		 * @param $currentUser
		 * @param null $appFieldParameters
		 * @return string
		 * @throws Exception
		 * @throws SmartyException
		 */
		public function run ($crmId, $view = null, $currentUser, $appFieldParameters = null) {
			if (!empty ($crmId)) {
				$resources = $this->fetchResourceInitiatives ($crmId);
				if (!empty ($resources)) {
					$this->checkForUpdates ($resources);
				}
			} else {
				$resources = null;
			}
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('RESOURCE_INITIATIVE', $resources);
			$smarty->assign ('TYPE_RESOURCE', ResourceInterface::RESOURCE_MODULES);
			$smarty->assign ('VIEW', $view);
			return $smarty->fetch ("modules/business_initiatives/ResourceInitiatve.tpl");
		}
		
		/**
		 * @param ResourcesForExecution[] $resources
		 * @param integer $crmid
		 *
		 * @throws Exception
		 */
		public function saveResourceInitiative ($resources, $crmid) {
			$tablaName = $this->getTableName ();
			$this->delRecordById ($tablaName, $crmid);
			foreach ($resources as $resource) {
				$resource->validate ();
				$this->adb->pquery (
					"INSERT INTO {$tablaName} (recurse_initiativeid, type_resource, crmid_resource, field_resource, contribution_factor, resource_progress, total_contribution, summary_contribution, summary_factor) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
					array ($resource->getId (), $resource->getTypeResource (), $resource->getIdResource (), $resource->getResourceDescription (), $resource->getContributionFactor (), $resource->getResourceProgress (), $resource->getTotalContribution (), $resource->getSummaryContribution (), $resource->getSummaryFactor ())
				);
			}
			
		}
		
		
		/**
		 * @param PearDatabase $adb
		 * @return resourceToInitiative
		 */
		public static function getInstance (PearDatabase $adb) {
			return new self ($adb);
		}
	}
