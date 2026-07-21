<?php
	require_once ('include/platzilla/Objects/BackgroundTaskActionHandler.php');

	class SendNotificationAction extends BackgroundTaskActionHandler {

		/**
		 * @param BackgroundTaskAction $action
		 *
		 * @return null
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
			foreach ($parameters as $parameter) {
				if ($parameter->getName () == 'notificationid') {
					list ($notificationName, $view) = explode ('@', $parameter->getValueFormula ());
				} else if ($parameter->getName () == 'entityid') {
					$entityId = $parameter->getValue ();
				}
			}
			
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
			// set active notifications
			$this->adb->pquery (
				'UPDATE
						vtiger_notifications_filters nf
					  INNER JOIN vtiger_notifications n ON nf.notificationid = n.notificationid
					  SET
					  	nf.recordid=?
					  WHERE
					  	n.name=? AND
					  	n.view=? AND
					  	nf.modulefilter=?',
				array ($entityId, $notificationName, $view, $moduleName)
			);
			$this->logger->emit ('INFO', 'Notificación lista para activar');
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
