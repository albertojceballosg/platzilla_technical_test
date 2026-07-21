<?php
	require_once ('include/platzilla/Exceptions/PlatformException.php');
	require_once ('include/platzilla/Managers/ApplicationManager.php');
	require_once ('include/platzilla/Managers/BackgroundTaskConfigurationManager.php');
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/platzilla/Managers/ModuleRelationshipManager.php');
	require_once ('include/platzilla/Managers/PlatformBillingPlanManager.php');
	require_once ('include/platzilla/Managers/PlatformFreeBillingPlanLimitManager.php');
	require_once ('include/platzilla/Managers/PlatformInstanceManager.php');
	require_once ('include/platzilla/Managers/PlatformSubscriptionManager.php');
	require_once ('include/platzilla/Managers/SystemAlertsManager.php');
	require_once ('include/platzilla/Managers/UserManager.php');
	require_once ('include/platzilla/Objects/Platform.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksRunner.class.php');

	/**
	 * Class PlatformManager
	 *
	 * En esta clase se desarrollan los métodos base para la creación y asignación de instancias y manejo de aplicaciones y módulos asociados a una instancia.
	 */
	class PlatformManager {
		/** @var PearDatabase */
		private $adb;

		/** @var string */
		private $httpHostName;

		private static $PLATFORM_ONLY_MODULE_NAMES = array ('store', 'instances', 'ConfigEditor', 'Integration');

		/**
		 * PlatformManager constructor.
		 *
		 * @param PearDatabase $adb
		 * @param null $httpHostName
		 */
		public function __construct (PearDatabase $adb, $httpHostName = null) {
			$this->adb          = $adb;
			$this->httpHostName = $httpHostName;
		}

		/**
		 * Obtiene las aplicaciones con sus atributos, instaladas en la instancia.
		 *
		 * @param boolean $headersOnly True Obtiene atributos simples de las aplicaciones, false obtiene el objeto completo
		 * @param boolean $forAnInstance Bandera se usa para determinar si la búsqueda que se está haciendo es para usar esa información
		 * en la creación/actualizacion de una instancia ($forAnInstance = true) o para cosas dentro de la misma plataforma ($forAnInstance = false)
		 *
		 * @return Application[]|null
		 */
		private function fetchAvailableApplications ($headersOnly = false, $forAnInstance = false) {
			$excludedModuleNames = $forAnInstance ? self::$PLATFORM_ONLY_MODULE_NAMES : null;
			return ApplicationManager::getInstance ($this->adb)->fetchApplications ($headersOnly, $excludedModuleNames);
		}
		
		/**
		 * @return Systemalerts[] |null
		 * @throws Exception
		 */
		private  function FetchAvailableAlerts () {
			return SystemAlertsManager::getInstance ($this->adb)->fetchSystemAlerts ();
		}
		
		/**
		 * Obtiene los módulos disponibles
		 *
		 * @param boolean $headersOnly True, obtiene atributos simples del objeto, false obtiene el objeto completo
		 * @param boolean $forAnInstance Bandera se usa para determinar si la búsqueda que se está haciendo es para usar esa información
		 * en la creación/actualizacion de una instancia ($forAnInstance = true) o para cosas dentro de la misma plataforma ($forAnInstance = false)
		 *
		 * @return Module[]
		 *
		 * @throws PlatformException
		 */
		private function fetchAvailableModules ($headersOnly, $forAnInstance) {
			$excludedModuleNames = $forAnInstance ? self::$PLATFORM_ONLY_MODULE_NAMES : null;
			$availableModules    = ModuleManager::getInstance ($this->adb)->fetchModules ($headersOnly, $excludedModuleNames, $forAnInstance);
			if (empty ($availableModules)) {
				throw new PlatformException (PlatformException::ERROR_PLATFORM_EMPTY_MODULES);
			}
			return $availableModules;
		}

		/**
		 * Obtiene las relaciones disponibles entre módulos
		 *
		 * @return ModuleRelationship[]|null
		 */
		private function fetchAvailableRelationships () {
			return ModuleRelationshipManager::getInstance ($this->adb)->fetchRelationships ();
		}
		
		/**
		 * Obtiene el prefijo y la secuencia con que se nombrará la nueva instancia.
		 *
		 * @return string
		 *
		 * @throws PlatformException
		 */
		private function getNextPlatformInstanceCode () {
			$this->adb->startTransaction ();
			$result = $this->adb->pquery ('SELECT * FROM vtiger_variables_instancias WHERE varname IN (?, ?)', array ('prefixinstances', 'codeseq'), true);
			if ($this->adb->num_rows ($result) == 2) {
				$prefix   = null;
				$sequence = null;
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					if ($row ['varname'] == 'prefixinstances') {
						$prefix = $row ['varvalue'];
					} else {
						$sequence = $row ['varvalue'];
					}
				}
				$this->adb->pquery ('UPDATE vtiger_variables_instancias SET varvalue=varvalue+1 WHERE varname=?', array ('codeseq'), true);
				$this->adb->completeTransaction ();
				$instanceCode = "{$prefix}{$sequence}";
			} else {
				$e            = new PlatformException (PlatformException::ERROR_PLATFORM_EMPTY_CONFIGURATION);
				$instanceCode = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
			return $instanceCode;
		}

		/**
		 * Verifica sí una aplicación puede darse de alta (suscribirse) en una instancia
		 *
		 * @param PlatformBillingPlan $billingPlan
		 * @param ApplicationSubscription[] $applicationSubscriptions
		 * @param string $applicationCode
		 *
		 * @return ApplicationSubscription|null
		 *
		 * @throws PlatformSubscriptionException
		 */
		private function checkIfApplicationCanBeSubscribed ($billingPlan, $applicationSubscriptions, $applicationCode) {
			$selectedApplicationSubscription = null;
			$subscribedApplications          = 0;
			foreach ($applicationSubscriptions as $applicationSubscription) {
				$applicationSubscriptionStatus = $applicationSubscription->getStatus ();
				if ($applicationSubscriptionStatus == ApplicationSubscription::STATUS_SUBSCRIBED) {
					$subscribedApplications++;
				}
				if ($applicationSubscription->getApplicationCode () == $applicationCode) {
					$selectedApplicationSubscription = $applicationSubscription;
				}
			}

			if (empty ($selectedApplicationSubscription)) {
				throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_APPLICATION_NOT_INSTALLED);
			} else if ($selectedApplicationSubscription->getStatus () == ApplicationSubscription::STATUS_INACTIVE) {
				throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_APPLICATION_INACTIVE);
			} else if ($selectedApplicationSubscription->getStatus () == ApplicationSubscription::STATUS_SUBSCRIBED) {
				throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_APPLICATION_SUBSCRIBED);
			}

			$totalApplications = $billingPlan->getTotalApplications ();
			if (($totalApplications != -1) && ($totalApplications < ($subscribedApplications + 1))) {
				throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_SUBSCRIBED_APPLICATIONS_FULL);
			}
			return $selectedApplicationSubscription;
		}

		/**
		 * Verifica sí una aplicación puede darse de baja, en una instancia.
		 *
		 * @param ApplicationSubscriptionManager $asm
		 * @param string $instanceCode
		 * @param string $applicationCode
		 *
		 * @return ApplicationSubscription|null
		 *
		 * @throws PlatformSubscriptionException
		 */
		private function checkIfApplicationCanBeUninstalled ($asm, $instanceCode, $applicationCode) {
			$selectedApplicationSubscription = $asm->fetchSubscription ($instanceCode, $applicationCode);
			if (empty ($selectedApplicationSubscription)) {
				throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_APPLICATION_NOT_INSTALLED);
			} else if ($selectedApplicationSubscription->getStatus () == ApplicationSubscription::STATUS_INACTIVE) {
				throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_APPLICATION_INACTIVE);
			}
			return $selectedApplicationSubscription;
		}

		/**
		 * Verifica sí la aplicación puede darse de baja
		 *
		 * @param ApplicationSubscriptionManager $asm
		 * @param string $instanceCode
		 * @param string $applicationCode
		 *
		 * @return ApplicationSubscription|null
		 *
		 * @throws PlatformSubscriptionException
		 */
		private function checkIfApplicationCanBeUnsubscribed ($asm, $instanceCode, $applicationCode) {
			$selectedApplicationSubscription = $asm->fetchSubscription ($instanceCode, $applicationCode);
			if (empty ($selectedApplicationSubscription)) {
				throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_APPLICATION_NOT_INSTALLED);
			} else if ($selectedApplicationSubscription->getStatus () == ApplicationSubscription::STATUS_INACTIVE) {
				throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_APPLICATION_INACTIVE);
			} else if ($selectedApplicationSubscription->getStatus () == ApplicationSubscription::STATUS_ACTIVE) {
				throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_APPLICATION_ACTIVE);
			}

			// Eliminar la suscripción de la aplicación que queremos cancelar
			$remainingApplicationSubscriptions = array_filter (
				$asm->fetchSubscriptions ($instanceCode),
				function (ApplicationSubscription $applicationSubscription) use ($applicationCode) {
					return $applicationSubscription->getApplicationCode () != $applicationCode;
				}
			);

			// Determinar cómo quedan los módulos
			$pfplm                        = PlatformFreeBillingPlanLimitManager::getInstance ($this->adb);
			$defaultMaxRecordsLimit       = $pfplm->fetchDefaultMaxRecordsLimit ();
			$psm                          = PlatformSubscriptionManager::getInstance ($this->adb);
			$remainingModuleSubscriptions = $psm->fetchModuleSubscriptions ($instanceCode, $remainingApplicationSubscriptions);
			$selectedModuleSubscriptions  = $psm->fetchModuleSubscriptions ($instanceCode, array ($selectedApplicationSubscription));
			$exceededModuleSubscriptions  = array_filter (
				$selectedModuleSubscriptions,
				function (ModuleSubscription $selectedModuleSubscription) use ($pfplm, $remainingModuleSubscriptions, $defaultMaxRecordsLimit) {
					if (empty ($remainingModuleSubscription)) {
						$limit = $pfplm->fetchLimit ($selectedModuleSubscription->getModuleName ());
						if ($limit === null) {
							$maxRecords = $defaultMaxRecordsLimit;
						} else {
							$maxRecords = $limit->getMaxRecords ();
						}
					} else {
						$maxRecords = $defaultMaxRecordsLimit;
						foreach ($remainingModuleSubscriptions as $remainingModuleSubscription) {
							if ($selectedModuleSubscription->getModuleName () == $remainingModuleSubscription->getModuleName ()) {
								$maxRecords = $remainingModuleSubscription->getMaxRecords ();
								break;
							}
						}
					}
					return $selectedModuleSubscription->getTotalRecords () > $maxRecords;
				}
			);
			if (!empty ($exceededModuleSubscriptions)) {
				throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_MODULE_RECORDS_LIMIT_EXCEEDED);
			}
			return $selectedApplicationSubscription;
		}

		/**
		 * @return array
		 * @throws Exception
		 */
		private function getInitialData () {
			$result = $this->adb->pquery (
				'SELECT
					crme.*
				FROM
					vtiger_crmentity crme
					INNER JOIN vtiger_settings_field sf ON sf.name=crme.setype
					INNER JOIN vtiger_settings_blocks sb ON sb.blockid=sf.blockid AND sb.label=?
				WHERE
					crme.deleted=0',
				array ('LBL_APPLICATIONS_SETTINGS')
			);
			$data   = array ();
			if ($this->adb->num_rows ($result) > 0) {
				$data = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$moduleName             = $row ['setype'];
					$entity                 = PlatformUtils::loadCrmEntity ($this->adb, $moduleName, $row ['crmid']);
					$data [ $moduleName ][] = $entity->column_fields;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $data;
		}

		/**
		 * Registra en el módulo clientes, la cuenta asociada al administrador de la instancia.
		 *
		 * @param PlatformInstance $instance
		 */
		private function registerAccount ($instance) {
			$accountId = $this->adb->getUniqueID ('vtiger_crmentity');
			$result    = $this->adb->pquery ('SELECT * FROM vtiger_modentity_num WHERE semodule=? AND active=?', array ('clientes', 1));
			if ($this->adb->num_rows ($result) == 0) {
				$prefix   = 'CLI-';
				$sequence = '001';
			} else {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$prefix   = $row ['prefix'];
				$sequence = substr ('00' . (intval ($row ['cur_id']) + 1), -3);
				$this->adb->pquery ('UPDATE vtiger_modentity_num SET cur_id=? WHERE semodule=? AND active=?', array ($sequence, 'clientes', 1));
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			$code   = "{$prefix}{$sequence}";

			$administrator = $instance->getAdministrator ();
			$instanceName  = $instance->getName ();
			$name          = !empty ($instanceName) ? $instanceName : trim ("{$administrator->getFirstName ()} {$administrator->getLastName ()}");
			$alias         = (!empty($administrator->getLastName ())) ? $administrator->getLastName () : 'Personal';
			$today         = date ('Y-m-d h:i:s');

			$this->adb->pquery (
				'INSERT INTO vtiger_crmentity (crmid, smcreatorid, setype, createdtime, modifiedtime, smownerid) VALUES (?, ?, ?, ?, ?, ?)',
				array ($accountId, 1, 'clientes', $today, $today, 1)
			);

			$this->adb->pquery (
				'INSERT INTO vtiger_clientes (clientesid, cod_clientes, alias, nombre_comercial, e_mail) VALUES (?, ?, ?, ?, ?)',
				array ($accountId, $code, $alias, $name, $administrator->getEmail ())
			);

			$this->adb->pquery ('INSERT INTO vtiger_clientescf (clientesid) VALUES (?)', array ($accountId));
			$instance->setAccountId ($accountId);
		}

		/**
		 * Registra en el módulo contactos, los datos del administrador de la instancia.
		 *
		 * @param PlatformInstance $instance
		 */
		private function registerContact ($instance) {
			$contactId = $this->adb->getUniqueID ('vtiger_crmentity');
			$result    = $this->adb->pquery ('SELECT * FROM vtiger_modentity_num WHERE semodule=? AND active=?', array ('contactos', 1));
			if ($this->adb->num_rows ($result) == 0) {
				$prefix   = 'CON-';
				$sequence = '001';
			} else {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$prefix   = $row ['prefix'];
				$sequence = substr ('00' . (intval ($row ['cur_id']) + 1), -3);
				$this->adb->pquery ('UPDATE vtiger_modentity_num SET cur_id=? WHERE semodule=? AND active=?', array ($sequence, 'contactos', 1));
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			$code   = "{$prefix}{$sequence}";

			$administrator = $instance->getAdministrator ();
            $today         = date ('Y-m-d h:i:s');
			$this->adb->pquery (
				'INSERT INTO vtiger_crmentity (crmid, smcreatorid, setype, createdtime, modifiedtime, smownerid) VALUES (?, ?, ?, ?, ?, ?)',
				array ($contactId, 1, 'contactos', $today, $today, 1)
			);
			$this->adb->pquery (
				'INSERT INTO vtiger_contactos (contactosid, cod_contactos, nombre, apellidos, email, clientes) VALUES (?, ?, ?, ?, ?, ?)',
				array ($contactId, $code, $administrator->getFirstName (), $administrator->getLastName (), $administrator->getEmail (), $instance->getAccountId ())
			);
			$this->adb->pquery ('INSERT INTO vtiger_contactoscf (contactosid) VALUES (?)', array ($contactId));
		}

		/**
		 * Registra la instancia creada
		 *
		 * @param PlatformInstance $instance
		 *
		 * @throws PlatformException
		 */
		private function registerInstance ($instance) {
			$billingPlan = PlatformBillingPlanManager::getInstance ($this->adb)->fetchFreePlan ();
			if (empty ($billingPlan)) {
				throw new PlatformException (PlatformException::ERROR_PLATFORM_INVALID_BILLING_PLAN);
			}

			$administrator = $instance->getAdministrator ();
			$name          = $instance->getName ();
			$name          = !empty ($name) ? $name : trim ("{$administrator->getFirstName ()} {$administrator->getLastName ()}");
			$this->adb->pquery (
				'INSERT INTO vtiger_instances (code, name, administrator, accountid, activeusers, status, verificationcode, registrationdate, billingplanid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
				array ($instance->getCode (), $name, $administrator->getEmail (), $instance->getAccountId (), $instance->getTotalRegisteredUsers (), PlatformInstanceInterface::STATUS_UNVERIFIED, $instance->getVerificationCode (), date ('Y-m-d H:i:s'), $billingPlan->getId ())
			);
			$instanceId = $this->adb->getLastInsertID ();

			$this->adb->pquery ('INSERT INTO vtiger_instanceusers (instancecode, username) VALUES (?, ?)', array ($instance->getCode (), $administrator->getEmail ()));

			$users = $instance->getUsers ();
			if (!empty ($users)) {
				foreach ($users as $user) {
					$this->adb->pquery ('INSERT INTO vtiger_instanceusers (instancecode, username) VALUES (?, ?)', array ($instance->getCode (), $user->getEmail ()));
				}
			}
			$instance->setId ($instanceId);
		}

		/**
		 * Registra una aplicación en una instancia para una suscripcion de un usuario
		 *
		 * @param PlatformInstance $instance
		 * @param string[] $applicationCodes
		 */
		private function registerInstanceApplications ($instance, $applicationCodes) {
			$applicationSubscriptions = array ();
			foreach ($applicationCodes as $applicationCode) {
				$applicationSubscriptions [] = ApplicationSubscription::getInstance ()
					->setApplicationCode ($applicationCode)
					->setInstanceCode ($instance->getCode ())
					->setRegistrationDate (date_create ())
					->setStatus (ApplicationSubscription::STATUS_ACTIVE);
			}
			$subscription = PlatformSubscription::getInstance ()
				->setAccountId ($instance->getAccountId ())
				->setApplicationSubscriptions ($applicationSubscriptions)
				->setBillingPlan ($instance->getBillingPlan ())
				->setInstanceCode ($instance->getCode ())
				->setRegistrationDate (date_create ())
				->setTotalActiveUsers ($instance->getTotalRegisteredUsers ())
				->setTotalDiskSpace (0);
			PlatformSubscriptionManager::getInstance ($this->adb)->saveSubscription ($subscription);
		}

		/**
		 * Cancelar el registro de aplicaciones en una instancia
		 *
		 * @param string $instanceCode
		 */
		private function unregisterInstanceApplications ($instanceCode) {
			PlatformSubscriptionManager::getInstance ($this->adb)->deleteSubscriptions ($instanceCode);
		}

		/**
		 * Valida el nombre del host de la instancia
		 *
		 * @throws PlatformException
		 */
		private function validateHttpHostName () {
			if (empty ($this->httpHostName)) {
				throw new PlatformException (PlatformException::ERROR_PLATFORM_EMPTY_HTTP_DATABASE_HOST_NAME);
			}
		}

		/**
		 * Ordenar módulos por tipo de entidad
		 *
		 * @param Module $moduleA
		 * @param Module $moduleB
		 *
		 * @return integer
		 */
		private static function sortModulesByEntityType ($moduleA, $moduleB) {
			if ((!$moduleA->getIsEntityType ()) && ($moduleB->getIsEntityType ())) {
				return -1;
			} else if (($moduleA->getIsEntityType ()) && (!$moduleB->getIsEntityType ())) {
				return 1;
			} else {
				return 0;
			}
		}

		/**
		 * Ordenar módulos por Id.
		 *
		 * @param Module $moduleA
		 * @param Module $moduleB
		 *
		 * @return integer
		 */
		private static function sortModulesById ($moduleA, $moduleB) {
			if ($moduleA->getId () < $moduleB->getId ()) {
				return -1;
			} else if ($moduleA->getId () > $moduleB->getId ()) {
				return 1;
			} else {
				return 0;
			}
		}

		/**
		 * Ordenar módulos por prioridad.
		 *
		 * @param Module $moduleA
		 * @param Module $moduleB
		 *
		 * @return integer
		 */
		private static function sortModulesByPriority ($moduleA, $moduleB) {
			if ($moduleA->getName () == 'emailmanager') {
				return -1;
			} else if ($moduleB->getName () == 'emailmanager') {
				return 1;
			} else if ($moduleA->getName () == 'backgroundtasks') {
				return -1;
			} else if ($moduleB->getName () == 'backgroundtasks') {
				return 1;
			} else if ($moduleA->getName () == 'graficosgenerales') {
				return 1;
			} else if ($moduleB->getName () == 'graficosgenerales') {
				return -1;
			} else {
				return 0;
			}
		}

		/**
		 * Ordenar módulos por tipo.
		 *
		 * @param Module $moduleA
		 * @param Module $moduleB
		 *
		 * @return integer
		 */
		private static function sortModulesByType ($moduleA, $moduleB) {
			if (($moduleA->getType () == ModuleInterface::TYPE_TOOL) && ($moduleB->getType () != ModuleInterface::TYPE_TOOL)) {
				return -1;
			} else if (($moduleB->getType () == ModuleInterface::TYPE_TOOL) && ($moduleA->getType () != ModuleInterface::TYPE_TOOL)) {
				return 1;
			} else if (($moduleA->getType () == ModuleInterface::TYPE_ADMIN) && ($moduleB->getType () != ModuleInterface::TYPE_ADMIN)) {
				return -1;
			} else if (($moduleB->getType () == ModuleInterface::TYPE_ADMIN) && ($moduleA->getType () != ModuleInterface::TYPE_ADMIN)) {
				return 1;
			} else {
				return 0;
			}
		}

		/**
		 * Ordenadar Modulos
		 *
		 * @param Module $moduleA
		 * @param Module $moduleB
		 *
		 * @return integer
		 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
		 * NOTA: PHP Mess Detector reporta este método como no usado. Falso. Se usa para ordenar los módulos en el método fetchPlatform. Se deshabilita el warning
		 */
		private static function sortModules ($moduleA, $moduleB) {
			$result = self::sortModulesByPriority ($moduleA, $moduleB);
			if ($result !== 0) {
				return $result;
			}

			$result = self::sortModulesByType ($moduleA, $moduleB);
			if ($result !== 0) {
				return $result;
			}

			$result = self::sortModulesByEntityType ($moduleA, $moduleB);
			if ($result !== 0) {
				return $result;
			}

			return self::sortModulesById ($moduleA, $moduleB);
		}

		/**
		 * @return PlatformInstance
		 * @throws PlatformException
		 */
		private function fetchStockInstance () {
			$instance = null;
			$result   = $this->adb->query ('SELECT * FROM vtiger_instancestock ORDER BY code LIMIT 1');
			if ($this->adb->num_rows ($result) > 0) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$instance = PlatformInstance::getInstance ()->setCode ($row ['code']);
			} else {
				$platform = $this->fetchPlatform (false, true);
				$this->validateModules ($platform);
				$this->validateModuleRelationships ($platform);
				$instance = $this->createEmptyInstance ($platform);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
			return $instance;
		}

		/**
		 * @param string $platformName
		 * @param string $company
		 * @param User $administrator
		 *
		 * @return PlatformInstance
		 *
		 * @throws Exception
		 * @throws PlatformException
		 */
		public function assignInstance ($platformName, $company, $administrator) {
			$oldExceptionInsteadOfDying         = $this->adb->exceptionInsteadOfDying;
			$this->adb->exceptionInsteadOfDying = true;

			$instance = $this->fetchStockInstance ()
				->setAdministrator ($administrator)
				->setBillingPlan (PlatformBillingPlanManager::getInstance ($this->adb)->fetchFreePlan ())
				->setName ($company);
			$code     = $instance->getCode ();

			$applications     = ApplicationManager::getInstance ($this->adb)->fetchApplicationHeaders ();
			$applicationCodes = array ();
			foreach ($applications as $application) {
				if ($application->getStatus () == Application::STATUS_ACTIVE) {
					$applicationCodes [] = $application->getCode ();
				}
			}

			try {
				PlatformInstanceManager::getInstance ()->assignInstance ($instance);
				$btr = BackgroundTasksRunner::getInstance ($this->adb, $platformName);
				$btr->runEventTriggeredTasks (
					'INSTANCE ASSIGNMENT',
					BackgroundTaskInterface::EVENT_INSTANT_BEFORE,
					$instance->convertToCrmEntity ()
				);
				$this->registerAccount ($instance);
				$this->registerContact ($instance);
				$this->registerInstance ($instance);
				$this->registerInstanceApplications ($instance, $applicationCodes);
				$btr->runEventTriggeredTasks (
					'INSTANCE ASSIGNMENT',
					BackgroundTaskInterface::EVENT_INSTANT_AFTER,
					$instance->convertToCrmEntity ()
				);
				$this->adb->pquery ('DELETE FROM vtiger_instancestock WHERE code=?', array ($code));
			} catch (Exception $ie) {
				$this->unregisterInstanceApplications ($code);
				$this->adb->pquery (
					'DELETE FROM vtiger_crmentity WHERE crmid IN (SELECT contactosid FROM vtiger_contactos WHERE clientes IN (SELECT accountid FROM vtiger_instances WHERE code=?))',
					array ($code)
				);
				$this->adb->pquery (
					'DELETE FROM vtiger_crmentity WHERE crmid IN (SELECT accountid FROM vtiger_instances WHERE code=?)',
					array ($code)
				);
				$this->adb->pquery ('DELETE FROM vtiger_crmentity WHERE crmid IN (SELECT accountid FROM vtiger_instances WHERE code=?)', array ($code));
				$this->adb->pquery ('DELETE FROM vtiger_instances WHERE code=?', array ($code));
			}
			$this->adb->exceptionInsteadOfDying = $oldExceptionInsteadOfDying;
			if (isset ($e)) {
				throw $e;
			}
			return $instance;
		}

        /**
         * @param string $platform
         *
         * @return string|null
         * @throws PlatformException
         */
        public function createFastEmptyInstance ($platform) {
            $this->validateHttpHostName ();
            $oldExceptionInsteadOfDying         = $this->adb->exceptionInsteadOfDying;
            $this->adb->exceptionInsteadOfDying = true;

            $code = $this->getNextPlatformInstanceCode ();
            try {
                $pim = PlatformInstanceManager::getInstance ();
                $pim->createFastEmptyInstance ($this->adb, $code, $this->adb->dbHostName, $this->adb->userName, $this->adb->userPassword, $this->httpHostName, $this->getInitialData ());
                $this->adb->pquery ('INSERT INTO vtiger_instancestock (code) VALUES (?)', array ($code));
            } catch (Exception $ie) {
                $this->adb->pquery ('DELETE FROM vtiger_instancestock WHERE code=?', array ($code));
                $e = $ie;
            }
            $this->adb->exceptionInsteadOfDying = $oldExceptionInsteadOfDying;
            if (isset ($e)) {
                throw $e;
            }
            return $code;
        }

		/**
		 * @param Platform $platform
		 *
		 * @return PlatformInstance
		 * @throws Exception
		 * @throws PlatformException
		 */
		public function createEmptyInstance ($platform) {
			$this->validateHttpHostName ();
			$oldExceptionInsteadOfDying         = $this->adb->exceptionInsteadOfDying;
			$this->adb->exceptionInsteadOfDying = true;

			$code                 = $this->getNextPlatformInstanceCode ();
			$platformApplications = $platform->getApplications ();
			$applications         = array ();
			foreach ($platformApplications as $platformApplication) {
				if ($platformApplication->getStatus () == Application::STATUS_ACTIVE) {
					$applications [] = $platformApplication->duplicate (null, $platformApplication->getName (), $platformApplication->getDescription ());
				}
			}

			$instance = null;
			try {
				$instance = PlatformInstance::getInstance ()
					->setApplications ($applications)
					->setBillingPlan (PlatformBillingPlanManager::getInstance ($this->adb)->fetchFreePlan ())
					->setCode ($code)
					->setModuleRelationships ($platform->getModuleRelationships ())
					->setSystemAlerts ($platform->getSystemAlerts ())
					->setName ('Stock instance')
					->setModules ($platform->getModules ());
				$pim      = PlatformInstanceManager::getInstance ();
				$pim->createEmptyInstance ($instance, $this->adb->dbHostName, $this->adb->userName, $this->adb->userPassword, $this->httpHostName, $this->getInitialData ());
				$this->adb->pquery ('INSERT INTO vtiger_instancestock (code) VALUES (?)', array ($code));
			} catch (Exception $ie) {
				$this->adb->pquery ('DELETE FROM vtiger_instancestock WHERE code=?', array ($code));
				$e = $ie;
			}
			$this->adb->exceptionInsteadOfDying = $oldExceptionInsteadOfDying;
			if (isset ($e)) {
				throw $e;
			}
			return $instance;
		}


		/**
		 * @param string $code
		 *
		 * @return null|User
		 * @throws Exception
		 */
		public function createInstanceTemporaryAdmin ($code) {
			if (empty ($code)) {
				return null;
			}

			$username  = "admin-{$code}";
			$targetAdb = AdbManager::getInstance ()->getTargetInstanceAdb ($code);
			try {
				$this->adb->pquery ('DELETE FROM vtiger_instanceusers WHERE instancecode=? AND username=?', array ($code, $username));
				$targetAdb->pquery ('DELETE vtiger_user2role FROM vtiger_user2role INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid AND vtiger_users.user_name=?', array ($username));
				$targetAdb->pquery ('DELETE FROM vtiger_users WHERE user_name=?', array ($username));

				$password       = md5 ($username);
				$temporaryAdmin = User::getInstance ()
					->setId (999999999)
					->setAdministrator (true)
					->setEmail ('no-reply@platzilla.com')
					->setFirstName ('Admin')
					->setLastName ('Platzilla')
					->setPlainPassword ($password)
					->setRoles (array (Role::getInstance ()->setId ('H2')->setName ('CEO')))
					->setStatus (User::STATUS_ACTIVE)
					->setUserName ($username)
					->setDefaultOperating ('MANAGEMENT_MODE')
					->setDefaultModuleName ('Home');
				UserManager::getInstance ($targetAdb, $code)->saveUser ($temporaryAdmin);
				$this->adb->pquery ('INSERT INTO vtiger_instanceusers (instancecode, username) VALUES (?, ?)', array ($code, $username));
				return $temporaryAdmin;
			} catch (Exception $e) {
				$this->adb->pquery ('DELETE FROM vtiger_instanceusers WHERE instancecode=? AND username=?', array ($code, $username));
				$targetAdb->pquery ('DELETE vtiger_user2role FROM vtiger_user2role INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid AND vtiger_users.user_name=?', array ($username));
				$targetAdb->pquery ('DELETE FROM vtiger_users WHERE user_name=?', array ($username));
				throw $e;
			}
		}

		/**
		 * Eliminar intancia.
		 *
		 * @param string $code
		 *
		 * @throws Exception
		 * @throws PlatformException
		 */
		public function deleteInstance ($code) {
			if (empty ($code)) {
				throw new PlatformException (PlatformException::ERROR_PLATFORM_EMPTY_INSTANCE_CODE);
			}
			$this->validateHttpHostName ();
			PaymentGatewayManager::getInstance ()->deleteInstanceCustomer ($code);
			PlatformInstanceManager::getInstance ()->deleteInstance ($code, $this->adb->dbHostName, $this->adb->userName, $this->adb->userPassword, $this->httpHostName);
			$this->unregisterInstanceApplications ($code);
			$this->adb->pquery (
				'DELETE FROM vtiger_crmentity WHERE crmid IN (SELECT contactosid FROM vtiger_contactos WHERE clientes IN (SELECT accountid FROM vtiger_instances WHERE code=?))',
				array ($code)
			);
			$this->adb->pquery (
				'DELETE FROM vtiger_crmentity WHERE crmid IN (SELECT accountid FROM vtiger_instances WHERE code=?)',
				array ($code)
			);
			$this->adb->pquery ('DELETE FROM vtiger_instancepayments WHERE instancecode=?', array ($code));
			$this->adb->pquery ('DELETE FROM vtiger_instanceusers WHERE instancecode=?', array ($code));
			$this->adb->pquery ('DELETE FROM vtiger_instances WHERE code=?', array ($code));
			$this->adb->pquery ('DELETE FROM vtiger_crmentity WHERE crmid IN (SELECT accountid FROM vtiger_instances WHERE code=?)', array ($code));
		}

		/**
		 * Elimina el usuario administrador temporal creado desde la consola administrativa
		 *
		 * @param string $token
		 *
		 * @throws Exception
		 */
		public function deleteInstanceTemporaryAdmin ($token) {
			$result = $this->adb->pquery ("SELECT * FROM vtiger_instanceusers WHERE SHA1(CONCAT(instancecode, '-', username))=?", array ($token));
			if ($this->adb->num_rows ($result) > 0) {
				$row          = $this->adb->fetchByAssoc ($result, -1, false);
				$instanceCode = $row ['instancecode'];
				$userName     = $row ['username'];
				$this->adb->pquery ('DELETE FROM vtiger_instanceusers WHERE instancecode=? AND username=?', array ($instanceCode, $userName));
				$targetAdb = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceCode);
				$targetAdb->pquery ('DELETE vtiger_user2role FROM vtiger_user2role INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid AND vtiger_users.user_name=?', array ($userName));
				$targetAdb->pquery ('DELETE FROM vtiger_users WHERE user_name=?', array ($userName));
			}
			DatabaseUtils::closeResult ($result);
		}

		/**
		 * Buscar instancia por código.
		 *
		 * @param string $code
		 * @param boolean $headersOnly
		 * @param boolean $includeStatistics
		 *
		 * @return PlatformInstance
		 * @throws PlatformException
		 */
		public function fetchInstance ($code, $headersOnly = false, $includeStatistics = false) {
			if (empty ($code)) {
				throw new PlatformException (PlatformException::ERROR_PLATFORM_EMPTY_INSTANCE_CODE);
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_instances WHERE code=?', array ($code));
			if ($this->adb->num_rows ($result) > 0) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$instance = PlatformInstanceManager::getInstance ()
					->fetchInstance ($row ['code'], $row ['administrator'], $headersOnly, $includeStatistics)
					->setAccountId (intval ($row ['accountid']))
					->setBillingPlan (PlatformBillingPlanManager::getInstance ($this->adb)->fetchPlan ($row ['billingplanid']))
					->setId (intval ($row ['instanceid']))
					->setName ($row ['name'])
					->setPattern ($row ['pattern'] == 1)
					->setProfileCode ($row ['profilecode'])
					->setRegistrationDate ($row ['registrationdate'])
					->setSource ($row ['source'])
					->setStatus ($row ['status'])
					->setVerificationCode ($row ['verificationcode']);
			} else {
				$e        = new PlatformException (PlatformException::ERROR_PLATFORM_INVALID_INSTANCE_CODE);
				$instance = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
			return $instance;
		}

		/**
		 * @param string $code
		 *
		 * @return instances
		 * @throws PlatformException
		 */
		public function fetchInstanceAsCrmEntity ($code) {
			if (empty ($code)) {
				throw new PlatformException (PlatformException::ERROR_PLATFORM_EMPTY_INSTANCE_CODE);
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_instances WHERE code=?', array ($code));
			if ($this->adb->num_rows ($result) > 0) {
				$row                   = $this->adb->fetchByAssoc ($result, -1, false);
				$row ['record_id']     = $row ['instanceid'];
				$row ['record_module'] = 'instances';
				/** @var instances $instance */
				$instance                = CRMEntity::getInstance ('instances');
				$instance->id            = $row ['instanceid'];
				$instance->column_fields = $row;
			} else {
				$e        = new PlatformException (PlatformException::ERROR_PLATFORM_INVALID_INSTANCE_CODE);
				$instance = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
			return $instance;
		}

		/**
		 * @param $shaOneEncodedCode
		 * @param boolean $headersOnly
		 * @param boolean $includeStatistics
		 *
		 * @return null|string|PlatformInstance
		 * @throws PlatformException
		 */
		public function fetchInstanceByShaOneEncodedCode ($shaOneEncodedCode, $headersOnly = false, $includeStatistics = false) {
			if (empty ($shaOneEncodedCode)) {
				throw new PlatformException (PlatformException::ERROR_PLATFORM_EMPTY_INSTANCE_CODE);
			} else if ($shaOneEncodedCode == sha1 ('')) {
				return '';
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_instances WHERE SHA1(code)=?', array ($shaOneEncodedCode));
			if ($this->adb->num_rows ($result) > 0) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$instance = PlatformInstanceManager::getInstance ()
					->fetchInstance ($row ['code'], $row ['administrator'], $headersOnly, $includeStatistics)
					->setAccountId (intval ($row ['accountid']))
					->setBillingPlan (PlatformBillingPlanManager::getInstance ($this->adb)->fetchPlan ($row ['billingplanid']))
					->setId (intval ($row ['instanceid']))
					->setName ($row ['name'])
					->setPattern ($row ['pattern'] == 1)
					->setRegistrationDate ($row ['registrationdate'])
					->setSource ($row ['source'])
					->setStatus ($row ['status'])
					->setVerificationCode ($row ['verificationcode']);
			} else {
				$e        = new PlatformException (PlatformException::ERROR_PLATFORM_INVALID_INSTANCE_CODE);
				$instance = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
			return $instance;
		}

		/**
		 * @param string $username
		 * @param boolean $headersOnly
		 * @param boolean $includeStatistics
		 *
		 * @return null|PlatformInstance
		 * @throws PlatformException
		 */
		public function fetchInstanceByUserName ($username, $headersOnly = false, $includeStatistics = false) {
			if (empty ($username)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_instanceusers WHERE username=?', array ($username));
			if ($this->adb->num_rows ($result) > 0) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$instance = $this->fetchInstance ($row ['instancecode'], $headersOnly, $includeStatistics);
			} else {
				$instance = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $instance;
		}

		/**
		 * Busca las instancias por código
		 *
		 * @param string $keyword
		 * @param string $fieldName
		 * @param integer $page
		 * @param integer $recordsPerPage
		 * @param boolean $headersOnly
		 * @param boolean $includeStatistics
		 *
		 * @return null|PlatformInstance[]
		 * @throws PlatformException
		 */
		public function fetchInstances ($keyword = null, $fieldName = null, $page = null, $recordsPerPage = null, $headersOnly = false, $includeStatistics = false) {
			$whereClauses = array ();
			$arguments    = array ();
			if ((!empty ($keyword)) && (!empty ($fieldName)) && (in_array ($fieldName, array ('code', 'name', 'administrator', 'registrationdate', 'source', 'status')))) {
				$whereClauses [] = "{$fieldName} LIKE ?";
				$arguments    [] = "%{$keyword}%";
				$arguments    [] = "%{$keyword}%";
			}
			$whereClause = !empty ($whereClauses) ? 'WHERE ' . join (' AND ', $whereClauses) : '';

			$startRecord = 0;
			$limitClause = '';
			if (is_numeric ($recordsPerPage)) {
				if ($page > 0) {
					$startRecord = (($page - 1) * $recordsPerPage);
				}
				$limit       = $recordsPerPage;
				$limitClause = "LIMIT {$startRecord}, {$limit}";
			}

			$result = $this->adb->pquery (
				"SELECT
					*
				FROM
					vtiger_instances
					CROSS JOIN (SELECT COUNT(*) AS __total_records__ FROM vtiger_instances {$whereClause}) AS total
				{$whereClause}
				ORDER BY
					instanceid
				{$limitClause}",
				$arguments
			);
			if ($this->adb->num_rows ($result) > 0) {
				$billingPlans = array ();
				$pbpm         = PlatformBillingPlanManager::getInstance ($this->adb);
				$pim          = PlatformInstanceManager::getInstance ();
				$startRecord++;
				$totalRecords = null;
				$records      = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$billingPlanId = $row ['billingplanid'];
					if (!isset ($billingPlans [ $billingPlanId ])) {
						$billingPlans [ $billingPlanId ] = $pbpm->fetchPlan ($billingPlanId);
					}
					$totalRecords   = intval ($row ['__total_records__']);
					$code           = $row ['code'];
					$databaseExists = DatabaseUtils::checkIfDatabaseExists ("pg_crm_{$row ['code']}");
					if ($databaseExists) {
						$records [] = $pim->fetchInstance ($code, $row ['administrator'], $headersOnly, $includeStatistics)
							->setAccountId (intval ($row ['accountid']))
							->setBillingPlan ($billingPlans [ $billingPlanId ])
							->setId (intval ($row ['instanceid']))
							->setName ($row ['name'])
							->setPattern ($row ['pattern'] == 1)
							->setRegistrationDate ($row ['registrationdate'])
							->setSource ($row ['source'])
							->setStatus ($row ['status'])
							->setVerificationCode ($row ['verificationcode']);
					} else {
						$records [] = PlatformInstance::getInstance ()
							->setAccountId (intval ($row ['accountid']))
							->setAdministrator (null)
							->setApplications (null)
							->setBillingPlan ($billingPlans [ $billingPlanId ])
							->setCode ($row ['code'])
							->setId (intval ($row ['instanceid']))
							->setModuleRelationships (null)
							->setModules (null)
							->setName ($row ['name'])
							->setPattern ($row ['pattern'] == 1)
							->setRegistrationDate ($row ['registrationdate'])
							->setSource ($row ['source'])
							->setStatus ($row ['status'])
							->setTotalRecords (null)
							->setUsageTime (null)
							->setUsers (null)
							->setVerificationCode ($row ['verificationcode']);
					}
				}
				$endRecord  = count ($records);
				$totalPages = ceil ($totalRecords / $recordsPerPage);
			} else {
				$totalRecords = 0;
				$records      = null;
				$endRecord    = 0;
				$totalPages   = 0;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return array (
				'startRecord'  => $startRecord,
				'endRecord'    => $endRecord,
				'totalRecords' => $totalRecords,
				'page'         => empty ($page) ? 1 : intval ($page),
				'totalPages'   => $totalPages,
				'records'      => $records,
			);
		}

		/**
		 * @param string $code
		 * @param string $token
		 *
		 * @return null|User
		 */
		public function fetchInstanceTemporaryAdmin ($code, $token) {
			if ((empty ($code)) || (empty ($token))) {
				return null;
			}

			$username = "admin-{$code}";
			$result   = $this->adb->pquery (
				'SELECT
					iu.*
				FROM
					vtiger_instanceusers iu
					INNER JOIN vtiger_instances i ON i.code=iu.instancecode
				WHERE
					iu.instancecode=? AND
					iu.username=? AND
					SHA1(?)=?',
				array ($code, $username, "{$code}-{$username}", $token)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$targetAdb      = AdbManager::getInstance ()->getTargetInstanceAdb ($code);
				$temporaryAdmin = UserManager::getInstance ($targetAdb, $code)->fetchUserByUsername ($username);
			} else {
				$temporaryAdmin = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $temporaryAdmin;
		}

		/**
		 * @param string $instanceCode
		 *
		 * @return integer
		 */
		public function fetchInstanceUsersLimit ($instanceCode) {
			if (empty ($instanceCode)) {
				return 0;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_instances WHERE code=?', array ($instanceCode));
			if ($this->adb->num_rows ($result) == 0) {
				$limit = 0;
			} else {
				$row   = $this->adb->fetchByAssoc ($result, -1, false);
				$limit = PlatformBillingPlanManager::getInstance ($this->adb)->fetchPlan ($row ['billingplanid'])->getTotalUsers ();
				if (($limit != -1) && ($row ['subscribedusers'] > 0)) {
					$limit = $row ['subscribedusers'];
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $limit;
		}

		/**
		 * Obtiene los atributos simples y objetos asociados a una instancia.
		 *
		 * @param boolean $headersOnly
		 * @param boolean $forAnInstance
		 *
		 * @return Platform
		 * @throws Exception
		 * @throws PlatformException
		 */
		public function fetchPlatform ($headersOnly = false, $forAnInstance = false) {
			$this->adb->query ('DELETE FROM vtiger_deletedelements');
			$modules = $this->fetchAvailableModules ($headersOnly, $forAnInstance);
			usort ($modules, array ('PlatformManager', 'sortModules'));
			$platform = Platform::getInstance ()
				->setApplications ($this->fetchAvailableApplications (true, $forAnInstance))
				->setModuleRelationships ($this->fetchAvailableRelationships ())
				->setSystemAlerts ($this->FetchAvailableAlerts ())
				->setModules ($modules);
			return $platform;
		}

		/**
		 * Instalar aplicación en una instancia. Implica asociar la aplicación a la instancia y asignar el estatus activo.
		 *
		 * @param string $instanceCode
		 * @param string $applicationCode
		 *
		 * @throws PlatformException
		 * @throws PlatformSubscriptionException
		 */
		public function installInstanceApplication ($instanceCode, $applicationCode) {
			if ((empty ($instanceCode)) || (empty ($applicationCode))) {
				return;
			}

			$psm                                 = PlatformSubscriptionManager::getInstance ($this->adb);
			$subscription                        = $psm->fetchSubscription ($instanceCode, true);
			$applicationSubscriptions            = $subscription->getApplicationSubscriptions ();
			$hasInactiveApplicationSubscriptions = false;
			if (!empty ($applicationSubscriptions)) {
				$selectedApplicationSubscription = null;
				foreach ($applicationSubscriptions as $applicationSubscription) {
					if ($applicationSubscription->getApplicationCode () == $applicationCode) {
						$selectedApplicationSubscription = $applicationSubscription;
						break;
					}
					if ($applicationSubscription->getStatus () == ApplicationSubscription::STATUS_INACTIVE) {
						$hasInactiveApplicationSubscriptions = true;
						break;
					}
				}
			}

			if (!empty ($selectedApplicationSubscription)) {
				throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_APPLICATION_INSTALLED);
			} else if ($hasInactiveApplicationSubscriptions) {
				throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_APPLICATION_INACTIVE);
			}

			$application = ApplicationManager::getInstance ($this->adb)->fetchApplication ($applicationCode, true, self::$PLATFORM_ONLY_MODULE_NAMES);
			if (empty ($application)) {
				throw new PlatformException (PlatformException::ERROR_PLATFORM_APPLICATION_NOT_FOUND);
			}

			$application->setStatus (ApplicationInterface::STATUS_ACTIVE);
			$instance = $this->fetchInstance ($instanceCode);
			PlatformInstanceManager::getInstance ()->installInstanceApplication ($instance, $application);
			$applicationSubscriptions [] = ApplicationSubscription::getInstance ()
				->setApplicationCode ($applicationCode)
				->setInstanceCode ($instanceCode)
				->setRegistrationDate (date_create ())
				->setStatus (ApplicationSubscription::STATUS_ACTIVE);
			$subscription->setApplicationSubscriptions ($applicationSubscriptions);
			$psm->saveSubscription ($subscription);
		}

		/**
		 * Para dar de alta (contratar) una aplicación en una instancia
		 *
		 * @param string $instanceCode
		 * @param string $applicationCode
		 *
		 * @throws PlatformException
		 * @throws PlatformSubscriptionException
		 */
		public function subscribeInstanceApplication ($instanceCode, $applicationCode) {
			if ((empty ($instanceCode)) || (empty ($applicationCode))) {
				return;
			}

			$psm                      = PlatformSubscriptionManager::getInstance ($this->adb);
			$subscription             = $psm->fetchSubscription ($instanceCode, true);
			$applicationSubscriptions = $subscription->getApplicationSubscriptions ();
			if (empty ($applicationSubscriptions)) {
				throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_EMPTY_APPLICATION_SUBSCRIPTIONS);
			}

			$selectedApplicationSubscription = $this->checkIfApplicationCanBeSubscribed ($subscription->getBillingPlan (), $applicationSubscriptions, $applicationCode);
			$this->updateInstanceApplication ($instanceCode, $applicationCode, Application::STATUS_ACTIVE);
			$selectedApplicationSubscription->setStatus (ApplicationSubscription::STATUS_SUBSCRIBED)
				->setServiceStartDate (date_create ())
				->setServiceEndDate ($subscription->getServiceEndDate ());
			ApplicationSubscriptionManager::getInstance ($this->adb)->saveSubscription ($selectedApplicationSubscription);
		}

		/**
		 * Desisntalar una aplicación de una instancia
		 *
		 * @param string $instanceCode
		 * @param string $applicationCode
		 *
		 * @throws PlatformException
		 */
		public function uninstallInstanceApplication ($instanceCode, $applicationCode) {
			if ((empty ($instanceCode)) || (empty ($applicationCode))) {
				return;
			}

			$asm                             = ApplicationSubscriptionManager::getInstance ($this->adb);
			$selectedApplicationSubscription = $this->checkIfApplicationCanBeUninstalled ($asm, $instanceCode, $applicationCode);
			$application                     = ApplicationManager::getInstance ($this->adb)->fetchApplication ($applicationCode, true, self::$PLATFORM_ONLY_MODULE_NAMES);
			$instance                        = $this->fetchInstance ($instanceCode);
			PlatformInstanceManager::getInstance ()->uninstallInstanceApplication ($instance, $application);
			$asm->deleteSubscription ($selectedApplicationSubscription);
		}

		/**
		 * Para dar de baja una aplicación en una instancia
		 *
		 * @param string $instanceCode
		 * @param string $applicationCode
		 *
		 * @throws PlatformException
		 * @throws PlatformSubscriptionException
		 */
		public function unsubscribeInstanceApplication ($instanceCode, $applicationCode) {
			if ((empty ($instanceCode)) || (empty ($applicationCode))) {
				return;
			}

			$asm                             = ApplicationSubscriptionManager::getInstance ($this->adb);
			$selectedApplicationSubscription = $this->checkIfApplicationCanBeUnsubscribed ($asm, $instanceCode, $applicationCode);
			$this->updateInstanceApplication ($instanceCode, $applicationCode, Application::STATUS_ACTIVE);
			$selectedApplicationSubscription->setStatus (ApplicationSubscription::STATUS_ACTIVE)
				->setServiceStartDate (null)
				->setServiceEndDate (null);
			$asm->saveSubscription ($selectedApplicationSubscription);
		}

		/**
		 * Actualizar una instancia dado el código
		 *
		 * @param string $code
		 * @param Platform $platform
		 * @param BackgroundTaskConfiguration $configuration
		 *
		 * @return PlatformInstance
		 * @throws Exception
		 */
		public function updateInstance ($code, $platform = null, $configuration = null) {
			$oldDieOnError                      = $this->adb->dieOnError;
			$oldExceptionInsteadOfDying         = $this->adb->exceptionInsteadOfDying;
			$this->adb->exceptionInsteadOfDying = true;
			$instance                           = null;
			try {
				if (empty ($platform)) {
					$platform = $this->fetchPlatform (false, true);
				}
				if (empty ($configuration)) {
					$configuration = BackgroundTaskConfigurationManager::getInstance ($this->adb)->fetchConfiguration ();
				}
				$this->validateModules ($platform);
				$this->validateModuleRelationships ($platform);

				$instance = $this->fetchInstance ($code);
				$pim      = PlatformInstanceManager::getInstance ();
				$pim->updateInstanceConfiguration ($instance, $configuration);
				$pim->updateInstance ($instance, $platform->getApplications (), $platform->getModules (), $platform->getModuleRelationships ());
			} catch (Exception $ie) {
				$e = $ie;
			}
			$this->adb->dieOnError              = $oldDieOnError;
			$this->adb->exceptionInsteadOfDying = $oldExceptionInsteadOfDying;
			if (isset ($e)) {
				throw $e;
			}
			return $instance;
		}

		/**
		 * Actualizar las aplicaciones de una instancia
		 *
		 * @param string $instanceCode
		 * @param string $applicationCode
		 * @param string $applicationStatus
		 *
		 * @throws PlatformException
		 */
		public function updateInstanceApplication ($instanceCode, $applicationCode, $applicationStatus) {
			if ((empty ($instanceCode)) || (empty ($applicationCode))) {
				return;
			}

			$adb         = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceCode);
			$application = ApplicationManager::getInstance ($adb)->fetchApplication ($applicationCode, true, self::$PLATFORM_ONLY_MODULE_NAMES);
			if (empty ($application)) {
				throw new PlatformException (PlatformException::ERROR_PLATFORM_APPLICATION_NOT_FOUND);
			}
			$application->setStatus ($applicationStatus);
			$instance = $this->fetchInstance ($instanceCode);
			PlatformInstanceManager::getInstance ()->updateInstanceApplication ($instance, $application);
		}

		/**
		 * Actualizar la instancia patron
		 *
		 * @param string $instanceCode
		 * @param boolean $isPattern
		 */
		public function updateInstancePattern ($instanceCode, $isPattern) {
			if (empty ($instanceCode)) {
				return;
			}

			$this->adb->pquery (
				'UPDATE vtiger_instances SET pattern=? WHERE code=?',
				array ($isPattern ? 1 : 0, $instanceCode)
			);
		}

		/**
		 * Determinar si un usuario esta registrado en una instancia
		 *
		 * @param string $username
		 *
		 * @return boolean
		 */
		public function userHasInstance ($username) {
			$adb         = AdbManager::getInstance ()->getMasterAdb ();
			$result      = $adb->pquery (
				'SELECT
					1
				FROM
					vtiger_instances i
					INNER JOIN vtiger_instanceusers iu ON iu.instancecode=i.code
				WHERE
					(i.administrator=? OR iu.username=?)',
				array ($username, $username)
			);
			$hasInstance = $adb->num_rows ($result) > 0;
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $hasInstance;
		}

		/**
		 * Valida las relaciones de los  módulos
		 *
		 * @param Platform $platform
		 *
		 * @throws PlatformException
		 */
		public function validateModuleRelationships ($platform) {
			$relationships = $platform->getModuleRelationships ();
			if (empty ($relationships)) {
				return;
			}
			$mrm = ModuleRelationshipManager::getInstance ($this->adb);
			foreach ($relationships as $relationship) {
				$mrm->validateRelationship ($relationship);
			}
		}

		/**
		 * Valida los módulos de un objeto Platform
		 *
		 * @param Platform $platform
		 *
		 * @throws PlatformException
		 */
		public function validateModules ($platform) {
			$modules = $platform->getModules ();
			$mm      = ModuleManager::getInstance ($this->adb);
			foreach ($modules as $module) {
				$mm->validateModule ($module);
			}
		}

		/**
		 * Instanciación de la clase PlatformManager. Se obtiene un objeto PlatformManager con los atributos de la clase.
		 *
		 * @param PearDatabase $adb
		 * @param string|null $httpHostName
		 *
		 * @return PlatformManager
		 */
		public static function getInstance (PearDatabase $adb, $httpHostName = null) {
			return new self ($adb, $httpHostName);
		}

	}
