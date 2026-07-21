<?php
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/platzilla/Objects/BackgroundTaskActionHandler.php');
	require_once ('include/utils/DemoDataManager.class.php');

	class CreateStockInstances extends BackgroundTaskActionHandler {
		/** @var string */
		private $platform;

		/** @var string */
		private $usersServerAddress;

		/**
		 * CreateInstanceFromRequests constructor.
		 *
		 * @param PearDatabase $adb
		 * @param Logger|null $logger
		 * @param string|null $platform
		 */
		public function __construct (PearDatabase $adb, Logger $logger = null, $platform = null) {
			global $platPrincipal, $dbconfig;
			require ('config.inc.php');
			$this->platform           = $platPrincipal;
			$this->usersServerAddress = $dbconfig ['db_serverForNewUsers'];
			parent::__construct ($adb, $logger);
		}

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

			$this->logger->emit ('INFO', 'Obteniendo cantidad solicitada');
			$requestedParameterNames = array ('quantity');
			$dummy                   = $this->getParameterValues ($parameters, $requestedParameterNames, true);
			$requestedInstances                = intval ($dummy ['quantity']);

			$this->logger->emit ('INFO', 'Obteniendo cantidad existente');
			$result = $this->adb->query ('SELECT COUNT(*) AS total FROM vtiger_instancestock');
			if ($this->adb->num_rows ($result) > 0) {
				$row            = $this->adb->fetchByAssoc ($result, -1, false);
				$existingInstances = intval ($row ['total']);
			} else {
				$existingInstances = null;
				$e = new Exception ('Imposible obtener el total de instancias existentes');
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}

			$this->logger->emit ('INFO', "Existen {$existingInstances} instancias. Se requiere tener un total de {$requestedInstances}");
			if ($existingInstances >= $requestedInstances) {
				return null;
			}

			$totalInstances = ($requestedInstances - $existingInstances);
			$this->logger->emit ('INFO', "Se crearán {$totalInstances} instancias");

			$pm = PlatformManager::getInstance ($this->adb, $this->usersServerAddress);
			$platform = $pm->fetchPlatform (false, true);
			//$pm->validateModules ($platform);
			//$pm->validateModuleRelationships ($platform);
			for ($i = 0; $i < $totalInstances; $i++) {
				$instance = $pm->createFastEmptyInstance ($platform);
                $this->logger->emit ('INFO', "Se ha creado la instancia {$instance}");
				//DemoDataManager::create ($instance->getCode (), PlatformUtils::getCrmEntity ($this->adb, 'Users', 1));
				//$this->logger->emit ('INFO', "Se ha creado la instancia {$instance->getCode ()}");
			}
			return null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param Logger|null $logger
		 * @param string|null $platform
		 *
		 * @return CreateRequestedInstances
		 */
		public static function getInstance (PearDatabase $adb, Logger $logger = null, $platform = null) {
			return new self ($adb, $logger);
		}

	}
