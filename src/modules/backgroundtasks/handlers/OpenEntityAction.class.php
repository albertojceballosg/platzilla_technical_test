<?php
	require_once ('include/platzilla/Objects/BackgroundTaskActionHandler.php');

	class OpenEntityAction extends BackgroundTaskActionHandler {

		/**
		 * @param BackgroundTaskAction $action
		 *
		 * @return integer
		 * @throws Exception
		 */
		public function run ($action) {
			if (empty ($action)) {
				throw new Exception ('No se ha suministrado la acción');
			}

			$parameters = $action->getParameters ();
			if (empty ($parameters)) {
				throw new Exception ('No se han suministrado los parámetros de la acción');
			}

			$this->logger->emit ('INFO', 'Obteniendo parámetros');
			$requestedParameterNames = array ('entityid');
			$parameterValues         = $this->getParameterValues ($parameters, $requestedParameterNames, true);
			$entityId                = $parameterValues ['entityid'];

			$result = $this->adb->pquery ('SELECT * FROM vtiger_crmentity WHERE crmid=?', array ($entityId));
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$moduleName = $row ['setype'];
			} else {
				$moduleName = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}

			if (empty ($moduleName)) {
				return null;
			}

			header ("Location: index.php?module={$moduleName}&action=DetailView&record={$entityId}");
			exit ();
		}

		/**
		 * @param PearDatabase $adb
		 * @param Logger $logger
		 * @param string $platform
		 *
		 * @return CreateEntityAction|null
		 */
		public static function getInstance (PearDatabase $adb, Logger $logger = null, $platform = null) {
			return new self ($adb, $logger, $platform);
		}

	}
