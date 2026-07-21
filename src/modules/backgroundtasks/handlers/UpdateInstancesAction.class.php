<?php
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/platzilla/Objects/BackgroundTaskActionHandler.php');

	class UpdateInstancesAction extends BackgroundTaskActionHandler {

		/**
		 * @param BackgroundTaskAction $action
		 *
		 * NOTA: CodeSniffer y Mess Detector se quejan de un parámetro que no se usa, pero se requiere usar la misma firma del método
		 *
		 * @codingStandardsIgnoreStart
		 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
		 * @return null
		 */
		public function run ($action) {
			$this->adb->exceptionInsteadOfDying = true;
			$result                             = $this->adb->query ('SELECT * FROM vtiger_instances ORDER BY instanceid');
			if ($this->adb->num_rows ($result) > 0) {
				$pm = PlatformManager::getInstance ($this->adb);
				$this->logger->emit ('INFO', 'Obteniendo la información de la plataforma madre');
				$platform      = $pm->fetchPlatform (false, true);
				$configuration = BackgroundTaskConfigurationManager::getInstance ($this->adb)->fetchConfiguration ();
				$pm->validateModules ($platform);
				$pm->validateModuleRelationships ($platform);
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$dummy = $this->adb->pquery ('SHOW DATABASES LIKE ?', array ("pg_crm_{$row ['code']}"));
					$hasDatabase = ($this->adb->num_rows ($dummy) > 0);
					DatabaseUtils::closeResult ($dummy);
					if (!$hasDatabase) {
						continue;
					}

					$this->logger->emit ('INFO', "Actualizando instancia {$row ['code']}");
					$targetAdb                          = AdbManager::getInstance ()->getTargetInstanceAdb ($row ['code']);
					$targetAdb->exceptionInsteadOfDying = true;
					try {
						$pm->updateInstance ($row ['code'], $platform, $configuration);
						$this->logger->emit ('INFO', "La instancia {$row ['code']} ha sido actualizada");
					} catch (Exception $e) {
						$this->logger->emit ('ERROR', "Se ha presentado un error: {$e->getMessage ()}\n{$e->getTraceAsString ()}");
					}
					DatabaseUtils::fullyDisconnect ($targetAdb);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param Logger $logger
		 * @param string $platform
		 *
		 * @return UpdateInstancesAction
		 */
		public static function getInstance (PearDatabase $adb, Logger $logger = null, $platform = null) {
			return new self ($adb, $logger, $platform);
		}

	}
