<?php
	require_once ('include/platzilla/Managers/ApplicationManager.php');
	require_once ('include/platzilla/Objects/BackgroundTaskActionHandler.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/DemoDataManager.class.php');
	require_once ('modules/emailmanager/emailmanager.php');

	class CreateRequestedInstances extends BackgroundTaskActionHandler {
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
		 * @return string
		 */
		private function generateRandomPassword () {
			$alphabet    = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
			$password    = array ();
			$alphaLength = strlen ($alphabet) - 1;
			for ($i = 0; $i < 15; $i++) {
				$n           = rand (0, $alphaLength);
				$password [] = $alphabet[ $n ];
			}
			return implode ($password);
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

			$this->logger->emit ('INFO', 'Obteniendo solicitudes');
			$result = $this->adb->query ('SELECT * FROM vtiger_instancerequests ORDER BY requestid');
			if (empty ($result)) {
				$this->logger->emit ('INFO', 'No hay solicitudes pendientes por procesar');
				DatabaseUtils::closeResult ($result);
				$result = null;
				return null;
			}

			$applications = ApplicationManager::getInstance ($this->adb)->fetchApplicationHeaders ();
			if (empty ($applications)) {
				throw new Exception ('No hay aplicaciones registradas');
			}

			$applicationCodes = array ();
			foreach ($applications as $application) {
				if ($application->getStatus () == Application::STATUS_ACTIVE) {
					$applicationCodes [] = $application->getCode ();
				}
			}

			while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
				$dummy            = explode ('@', $row ['email']);
				$emailAddress     = $row ['email'];
				$firstName        = $dummy [0];
				$lastName         = $dummy [1];
				$company          = trim ($emailAddress);
				$plainPassword    = $this->generateRandomPassword ();
				$token            = sha1 ($emailAddress);
				$platzillaRootUri = PlatzillaUtils::getPlatzillaRootUri ();

				$deleteRequest = false;
				$this->logger->emit ('INFO', "Procesando solicitud de {$emailAddress}");
				try {
					$pm = PlatformManager::getInstance ($this->adb, $this->usersServerAddress);
					if ($pm->userHasInstance ($emailAddress)) {
						$deleteRequest = true;
						throw new Exception ('Ya tiene una instancia asignada');
					}

					$_SESSION ['plat']          = $this->platform;
					$_SESSION ['platInstancia'] = '';
					$administrator = User::getInstance ()
						->setEmail ($emailAddress)
						->setFirstName ($firstName)
						->setAdministrator (true)
						->setLastName ($lastName)
						->setPlainPassword ($plainPassword)
						->setUserName ($emailAddress);
					$instance      = $pm->createInstance ($this->platform, $company, $administrator, null, $applicationCodes);
					try {
						$_SESSION ['plat']          = $instance->getCode ();
						$_SESSION ['platInstancia'] = $instance->getCode ();
						PlatformUtils::createPermissionFiles ($instance->getCode (), 1, $instance->getCode ());
						DemoDataManager::create ($instance->getCode ());
					} catch (Exception $ie) {
						$this->logger->emit ('ERROR', "Se ha presentado un error al copiar la data de prueba {$ie->getMessage ()}");
					}

					$status = emailmanager::getInstance ($this->adb, $this->platform)->addSender (
						'Platzilla',
						'no_reply@platzilla.com'
					)->send (
						$emailAddress,
						'es',
						'Bienvenida a Platzilla',
						array (
							'NOMBRE_DE_USUARIO' => $emailAddress,
							'URL'               => "{$platzillaRootUri}/?token={$token}{$plainPassword}",
						)
					);

					$deleteRequest = true;
					if ($status != emailmanager::STATUS_SENT) {
						$pm->deleteInstance ($instance->getCode ());
						throw new Exception ("Se ha presentado un error al enviar la notificación a {$emailAddress}: código {$status}");
					}
					$this->logger->emit ('INFO', "Se ha creado la instancia {$instance->getCode ()}");
				} catch (Exception $e) {
					$this->logger->emit ('ERROR', $e->getMessage ());
				}
				if ($deleteRequest) {
					$this->adb->pquery ('DELETE FROM vtiger_instancerequests WHERE email=?', array ($emailAddress));
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
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
