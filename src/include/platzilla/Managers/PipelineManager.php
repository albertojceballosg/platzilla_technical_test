<?php
	require_once ('include/platzilla/Objects/Pipeline.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class PipelineManager {
		/** @var PipelineManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param Pipeline $pipeline
		 */
		public function deletePipeline ($pipeline) {
			if ((empty ($pipeline)) || (!($pipeline instanceof Pipeline))) {
				return;
			}
			$this->adb->pquery ('DELETE FROM vtiger_pipelines WHERE modulename=? AND fieldname=?', array ($pipeline->getModuleName (), $pipeline->getFieldName ()));
		}

		/**
		 * @param string $moduleName
		 * @param string $fieldName
		 *
		 * @return null|Pipeline
		 */
		public function fetchPipeline ($moduleName, $fieldName) {
			if ((empty ($moduleName)) || (empty ($fieldName))) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_pipelines WHERE modulename=? AND fieldname=?', array ($moduleName, $fieldName));
			if ($this->adb->num_rows ($result) > 0) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$pipeline = Pipeline::getInstance ()
					->setId (intval ($row ['pipelineid']))
					->setFieldName ($row ['fieldname'])
					->setModuleName ($row ['modulename'])
					->setValues (!empty ($row ['values']) ? json_decode ($row ['values'], true) : null);
			} else {
				$pipeline = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $pipeline;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return Pipeline[]|null
		 */
		public function fetchPipelines ($moduleName) {
			if (empty ($moduleName)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_pipelines WHERE modulename=?', array ($moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				$pipelines = array ();
				while ($row = $this->adb->fetchByAssoc ($row, -1, false)) {
					$pipelines [] = Pipeline::getInstance ()
						->setId (intval ($row ['pipelineid']))
						->setFieldName ($row ['fieldname'])
						->setModuleName ($row ['modulename'])
						->setValues (!empty ($row ['values']) ? json_decode ($row ['values'], true) : null);
				}
			} else {
				$pipelines = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $pipelines;
		}

		/**
		 * @param Pipeline $pipeline
		 *
		 * @return Pipeline
		 * @throws PipelineException
		 */
		public function savePipeline ($pipeline) {
			if ((empty ($pipeline)) || (!($pipeline instanceof Pipeline))) {
				return null;
			}
			$pipeline->validate ();

			$this->adb->startTransaction ();
			$fieldName  = $pipeline->getFieldName ();
			$moduleName = $pipeline->getModuleName ();
			$result     = $this->adb->pquery ('SELECT * FROM vtiger_pipelines WHERE modulename=? AND fieldname=?', array ($moduleName, $fieldName));
			if ($this->adb->num_rows ($result) == 0) {
				$this->adb->pquery ('INSERT INTO vtiger_pipelines (modulename, fieldname, `values`) VALUES (?, ?, ?)', array ($moduleName, $fieldName, json_encode ($pipeline->getValues ())));
				$pipelineId = $this->adb->getLastInsertID ();
			} else {
				$row        = $this->adb->fetchByAssoc ($result, -1, false);
				$pipelineId = intval ($row ['pipelineid']);
				$this->adb->pquery ('UPDATE vtiger_pipelines SET `values`=? WHERE modulename=? AND fieldname=?', array (json_encode ($pipeline->getValues ()), $moduleName, $fieldName));
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			$this->adb->completeTransaction ();
			$pipeline->setId ($pipelineId);
			return $pipeline;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return PipelineManager
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCES === null) {
				self::$INSTANCES = array ();
			}
			if (!isset (self::$INSTANCES [ $adb->dbName ])) {
				self::$INSTANCES [ $adb->dbName ] = new self ($adb);
			}
			return self::$INSTANCES [ $adb->dbName ];
		}

	}
