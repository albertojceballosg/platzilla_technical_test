<?php
	require_once ('include/platzilla/Exceptions/PlatformInstanceException.php');
	require_once ('include/platzilla/Objects/Application.php');
	require_once ('include/platzilla/Objects/PlatformBillingPlan.php');
	require_once ('include/platzilla/Objects/PlatformInstanceInterface.php');
	require_once ('include/platzilla/Objects/User.php');

	/**
	 * Class PlatformInstance
	 *
	 * En esta clase se define el objeto "Instancia Plataforma" el cual hace referencia a las instancias que componen la Plataforma.
	 **/
	class PlatformInstance implements PlatformInstanceInterface {
		/** @var integer */
		private $id;

		/** @var integer */
		private $accountId;

		/** @var User */
		private $administrator;

		/** @var Application[] */
		private $applications;

		/** @var PlatformBillingPlan */
		private $billingPlan;

		/** @var string */
		private $code;

		/** @var Module[] */
		private $modules;

		/** @var ModuleRelationship[] */
		private $moduleRelationships;
		
		/** @var string */
		private $name;

		/** @var boolean */
		private $pattern;

		/** @var string */
		private $profileCode;

		/** @var DateTime */
		private $registrationDate;

		/** @var string */
		private $source;

		/** @var string */
		private $status;

		/** @var integer */
		private $subscribedUsers;
		
		/** @var Systemalerts[] */
		private $SystemAlerts;

		/** @var integer */
		private $totalRecords;

		/** @var integer */
		private $usageTime;

		/** @var User[] */
		private $users;

		/** @var string */
		private $verificationCode;

		/**
		 * PlatformInstance constructor
		 */
		public function __construct () {
			$this->status           = self::STATUS_UNVERIFIED;
			$this->subscribedUsers  = 0;
			$this->verificationCode = $this->generateRandomVerificationCode ();
		}

		/**
		 * Para generar el código de validación de la instancia de manera aleatoria
		 *
		 * @return string code
		 */
		private function generateRandomVerificationCode () {
			$code    = '';
			$pattern = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$max     = (strlen ($pattern) - 1);
			for ($i = 0; $i < 6; $i++) {
				$code .= $pattern[mt_rand (0, $max)];
			}
			return $code;
		}

		/**
		 * Para validar las aplicaciones que lleva una instancia
		 *
		 * @throws ApplicationException
		 * @throws PlatformInstanceException
		 */
		private function validateApplications () {
			if (empty ($this->applications)) {
				return;
			}

			if (!is_array ($this->applications)) {
				throw new PlatformInstanceException (PlatformInstanceException::ERROR_INSTANCE_INVALID_APPLICATIONS);
			}

			foreach ($this->applications as $application) {
				if (!($application instanceof Application)) {
					throw new PlatformInstanceException (PlatformInstanceException::ERROR_INSTANCE_INVALID_APPLICATION);
				} else {
					$application->validate ();
				}
			}
		}

		/**
		 * Para validar los módulos que conforman las aplicaciones que lleva una instancia
		 *
		 * @throws ModuleException
		 * @throws PlatformInstanceException
		 */
		private function validateModules () {
			if (empty ($this->modules)) {
				throw new PlatformInstanceException (PlatformInstanceException::ERROR_INSTANCE_EMPTY_MODULES);
			} else if (!is_array ($this->modules)) {
				throw new PlatformInstanceException (PlatformInstanceException::ERROR_INSTANCE_INVALID_MODULES);
			}

			foreach ($this->modules as $module) {
				if (!($module instanceof Module)) {
					throw new PlatformInstanceException (PlatformInstanceException::ERROR_INSTANCE_INVALID_MODULE);
				} else {
					$module->validate ();
				}
			}
		}

		/**
		 * Para validar los módulos relacionados que conforman las aplicaciones que lleva una instancia
		 *
		 * @throws ModuleRelationshipException
		 * @throws PlatformInstanceException
		 */
		private function validateModuleRelationships () {
			if (empty ($this->moduleRelationships)) {
				return;
			}

			if (!is_array ($this->moduleRelationships)) {
				throw new PlatformInstanceException (PlatformInstanceException::ERROR_INSTANCE_INVALID_MODULE_RELATIONSHIPS);
			}

			foreach ($this->moduleRelationships as $relationship) {
				if (!($relationship instanceof ModuleRelationship)) {
					throw new PlatformInstanceException (PlatformInstanceException::ERROR_INSTANCE_INVALID_MODULE_RELATIONSHIP);
				} else {
					$relationship->validate ();
				}
			}
		}

		/**
		 * Para validar los usuarios que forman parte de una instancia
		 *
		 * @throws PlatformInstanceException
		 * @throws UserException
		 */
		private function validateUsers () {
			if (empty ($this->users)) {
				return;
			}

			foreach ($this->users as $user) {
				if (!($user instanceof User)) {
					throw new PlatformInstanceException (PlatformInstanceException::ERROR_INSTANCE_INVALID_USER);
				} else {
					$user->validate ();
				}
			}
		}

		/**
		 * Para obtener el ID de la instancia
		 *
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * Para obtener el ID de la cuenta asociada a la instancia
		 *
		 * @return integer
		 */
		public function getAccountId () {
			return $this->accountId;
		}

		/**
		 * Para obtener el usuario administrador de la instancia
		 *
		 * @return User
		 */
		public function getAdministrator () {
			return $this->administrator;
		}

		/**
		 * Para obtener el listado de aplicaciones disponibles
		 *
		 * @return Application[]
		 */
		public function getApplications () {
			return $this->applications;
		}

		/**
		 * Para obtener la dirección de facturación del plan que se tiene contratado en la instancia
		 *
		 * @return PlatformBillingPlan
		 */
		public function getBillingPlan () {
			return $this->billingPlan;
		}

		/**
		 * Para obtener el código que se le fue asignado a la instancia
		 *
		 * @return string
		 */
		public function getCode () {
			return $this->code;
		}

		/**
		 * Para obtener el listado de los módulos relacionados en la instancia
		 *
		 * @return ModuleRelationship[]
		 */
		public function getModuleRelationships () {
			return $this->moduleRelationships;
		}
		
		/**
		 * Para obtener el lista de los módulos disponibles en la instancia
		 *
		 * @return Module[]
		 */
		public function getModules () {
			return $this->modules;
		}

		/**
		 * Para obtener el nombre asignado a la instancia
		 *
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * @return string
		 */
		public function getProfileCode () {
			return $this->profileCode;
		}

		/**
		 * Para obtener la fecha en que se registró la instancia
		 *
		 * @return DateTime
		 */
		public function getRegistrationDate () {
			return $this->registrationDate;
		}

		/**
		 * @return string
		 */
		public function getSource () {
			return $this->source;
		}

		/**
		 * Para obtener el estatus de la instancia, siendo paga ó vencida
		 *
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}

		/**
		 * Para obtener los usuarios suscritos
		 *
		 * @return integer
		 */
		public function getSubscribedUsers () {
			return $this->subscribedUsers;
		}
		
		/**
		 * @return Systemalerts[]
		 */
		public function getSystemAlerts () {
			return $this->SystemAlerts;
		}

		/**
		 * @return integer
		 */
		public function getTotalRecords () {
			return $this->totalRecords;
		}

		/**
		 * Para obtener el total de usuarios registrados en la instancia
		 *
		 * @return integer
		 */
		public function getTotalRegisteredUsers () {
			return (count ($this->users) + 1);
		}

		/**
		 * Tiempo de uso transcurrido en segundos
		 *
		 * @return integer
		 */
		public function getUsageTime () {
			return $this->usageTime;
		}

		/**
		 * Para obtener el usuario de la instancia
		 *
		 * @return User[]
		 */
		public function getUsers () {
			return $this->users;
		}

		/**
		 * Para obtener el código de verificación de la instancia
		 *
		 * @return string
		 */
		public function getVerificationCode () {
			return $this->verificationCode;
		}

		/**
		 * Valida si la instancia es patron
		 *
		 * @return boolean
		 */
		public function isPattern () {
			return $this->pattern;
		}

		/**
		 * Establece el ID de la instancia
		 *
		 * @param integer $id
		 *
		 * @return PlatformInstance
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * Establece el ID de la cuenta asociada a la instancia
		 *
		 * @param integer $accountId
		 *
		 * @return PlatformInstance
		 */
		public function setAccountId ($accountId) {
			$this->accountId = $accountId;
			return $this;
		}

		/**
		 * Establece el usuario administrador de la instancia
		 *
		 * @param User $administrator
		 *
		 * @return PlatformInstance
		 */
		public function setAdministrator ($administrator) {
			if ($administrator instanceof User) {
				$this->administrator = $administrator;
			}
			return $this;
		}

		/**
		 * Establece el listado de aplicaciones disponibles
		 *
		 * @param Application[] $applications
		 *
		 * @return PlatformInstance
		 */
		public function setApplications ($applications) {
			$this->applications = $applications;
			return $this;
		}

		/**
		 * Establece la dirección de facturación del plan que se asigna al plan contratado en la instancia
		 *
		 * @param PlatformBillingPlan $billingPlan
		 *
		 * @return PlatformInstance
		 */
		public function setBillingPlan ($billingPlan) {
			if ($billingPlan instanceof PlatformBillingPlan) {
				$this->billingPlan = $billingPlan;
			} else {
				$this->billingPlan = null;
			}
			return $this;
		}

		/**
		 * Establece el código que se le fue asignado a la instancia
		 *
		 * @param string $code
		 *
		 * @return PlatformInstance
		 */
		public function setCode ($code) {
			$this->code = $code;
			return $this;
		}

		/**
		 * Establece los módulos relacionados que se asignan a una aplicacion y los campos para importar
		 *
		 * @param ModuleRelationship[] $moduleRelationships
		 *
		 * @return PlatformInstance
		 */
		public function setModuleRelationships ($moduleRelationships) {
			$this->moduleRelationships = $moduleRelationships;
			return $this;
		}
		
		/**
		 * Establece los modulos que se asignan a una aplicacion
		 *
		 * @param Module[] $modules
		 *
		 * @return PlatformInstance
		 */
		public function setModules ($modules) {
			$this->modules = $modules;
			return $this;
		}

		/**
		 * Establece el nombre asignado a la instancia
		 *
		 * @param string $name
		 *
		 * @return PlatformInstance
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * Establece sí una instancia fue asignada como patron
		 *
		 * @param boolean $isPattern
		 *
		 * @return PlatformInstance
		 */
		public function setPattern ($isPattern) {
			if ((is_bool ($isPattern)) && (boolval ($isPattern) == $isPattern)) {
				$this->pattern = boolval ($isPattern);
			} else {
				$this->pattern = false;
			}
			return $this;
		}

		/**
		 * @param string $profileCode
		 *
		 * @return PlatformInstance
		 */
		public function setProfileCode ($profileCode) {
			$this->profileCode = $profileCode;
			return $this;
		}

		/**
		 * Establece la fecha en que se registró la instancia
		 *
		 * @param DateTime $registrationDate
		 *
		 * @return PlatformInstance
		 */
		public function setRegistrationDate ($registrationDate) {
			if (is_string ($registrationDate)) {
				$date = DateTime::createFromFormat ('Y-m-d', $registrationDate);
				$date = ($date) && ($date->format ('Y-m-d') === $registrationDate) ? $date : null;
			} else if ($registrationDate instanceof DateTime) {
				$date = $registrationDate;
			} else {
				$date = null;
			}
			$this->registrationDate = $date;
			return $this;
		}

		/**
		 * @param string $source
		 *
		 * @return PlatformInstance
		 */
		public function setSource ($source) {
			$this->source = $source;
			return $this;
		}

		/**
		 * Establece el status actual que tiene la instancia
		 *
		 * @param string $status
		 *
		 * @return PlatformInstance
		 */
		public function setStatus ($status) {
			if (in_array ($status, self::getAvailableStatuses ())) {
				$this->status = $status;
			}
			return $this;
		}

		/**
		 * Establece los usuarios suscritos
		 *
		 * @param integer $subscribedUsers
		 *
		 * @return PlatformInstance
		 */
		public function setSubscribedUsers($subscribedUsers) {
			$this->subscribedUsers = $subscribedUsers;
			return $this;
		}
		
		/**
		 * @param Systemalerts[] $SystemAlerts
		 *
		 * @return PlatformInstance
		 */
		public function setSystemAlerts ($SystemAlerts) {
			$this->SystemAlerts = $SystemAlerts;
			return $this;
		}

		/**
		 * @param integer $totalRecords
		 *
		 * @return PlatformInstance
		 */
		public function setTotalRecords ($totalRecords) {
			$this->totalRecords = $totalRecords;
			return $this;
		}

		/**
		 * @param integer $usageTime
		 *
		 * @return PlatformInstance
		 */
		public function setUsageTime ($usageTime) {
			$this->usageTime = $usageTime;
			return $this;
		}

		/**
		 * Establece los usuarios de la instancia
		 *
		 * @param User[] $users
		 *
		 * @return PlatformInstance
		 */
		public function setUsers ($users) {
			$this->users = $users;
			return $this;
		}

		/**
		 * Establece el codigo de verificación que se le fue asignado a la instancia
		 *
		 * @param string $verificationCode
		 *
		 * @return PlatformInstance
		 */
		public function setVerificationCode ($verificationCode) {
			$this->verificationCode = $verificationCode;
			return $this;
		}

		/**
		 * Convierte el objeto Instancia Plataforma a un objeto CRMEntity  para que esté disponible y se consuma por el motor de tareas en segundo plano
		 *
		 * @return CRMEntity|instances
		 */
		public function convertToCrmEntity () {
			require_once ('modules/instances/instances.php');
			$entity                = instances::getInstance ();
			$entity->id            = $this->id;
			$entity->column_fields = array (
				'accountid'        => intval ($this->accountId),
				'activeusers'      => count ($this->users),
				'administrator'    => isset ($this->administrator) ? $this->administrator->getUserName () : null,
				'billingplanid'    => $this->billingPlan->getId (),
				'code'             => $this->code,
				'instanceid'       => intval ($this->id),
				'name'             => $this->name,
				'record_id'        => $this->id,
				'record_module'    => 'instances',
				'status'           => $this->status,
				'totalrecords'     => $this->totalRecords,
				'usagetime'        => $this->usageTime,
				'verificationcode' => $this->verificationCode,
			);
			return $entity;
		}

		/**
		 * Para validar que los parametros suficientes (administrador, usuarios, aplicaciones, módulos) hayan sidos validados
		 *
		 * @param boolean $deepCheck
		 *
		 * @throws PlatformInstanceException
		 * @throws UserException
		 */
		public function validate ($deepCheck = true) {
			if (empty ($this->code)) {
				throw new PlatformInstanceException (PlatformInstanceException::ERROR_INSTANCE_EMPTY_CODE);
			} else if (empty ($this->billingPlan)) {
				throw new PlatformInstanceException (PlatformInstanceException::ERROR_INSTANCE_EMPTY_BILLING_PLAN);
			} else if (empty ($this->name)) {
				throw new PlatformInstanceException (PlatformInstanceException::ERROR_INSTANCE_EMPTY_NAME);
			} else if ($deepCheck) {
				if (!empty ($this->administrator)) {
					$this->administrator->validate ();
				}
				$this->validateApplications ();
				$this->validateModules ();
				$this->validateModuleRelationships ();
				$this->validateUsers ();
			}
		}

		/**
		 * Obtiene los estatus disponibles (verificado o sin verificar) de la instancia
		 *
		 * @return string[]
		 */
		public static function getAvailableStatuses () {
			return array (self::STATUS_UNVERIFIED, self::STATUS_VERIFIED);
		}

		/**
		 * Instanciación de la clase PlatformInstance. Se obtiene un objeto PlatformInstance con los atributos de la clase
		 *
		 * @return PlatformInstance
		 */
		public static function getInstance () {
			return new self ();
		}

	}
