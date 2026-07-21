<?php
	require_once ('include/platzilla/Exceptions/ApplicationSubscriptionException.php');
	require_once ('include/platzilla/Objects/ApplicationSubscriptionInterface.php');

	/**
	 * Class ApplicationSubscription
	 *
	 * En esta clase se define el objeto "Suscripcion Aplicacion" el cual hace referencia a las aplicaciones que un usuario puede dar de alta en una Instancia.
	 **/
	class ApplicationSubscription implements ApplicationSubscriptionInterface {
		/** @var string */
		private $applicationCode;

		/** @var string */
		private $applicationDescription;

		/** @var string */
		private $applicationName;

		/** @var string */
		private $instanceCode;

		/** @var DateTime */
		private $registrationDate;

		/** @var DateTime */
		private $serviceStartDate;

		/** @var DateTime */
		private $serviceEndDate;

		/** @var string */
		private $status;

		/**
		 * Para obtener el codigo de la aplicacion a suscribir
		 *
		 * @return string
		 */
		public function getApplicationCode () {
			return $this->applicationCode;
		}

		/**
		 * Para obtener la descripcion asignada a la aplicacion que se va a suscribir
		 *
		 * @return string
		 */
		public function getApplicationDescription () {
			return $this->applicationDescription;
		}

		/**
		 * Para obtener el nombre de la aplicacion que se va a suscribir
		 *
		 * @return string
		 */
		public function getApplicationName () {
			return $this->applicationName;
		}

		/**
		 * Para obtener el codigo asignado a la instancia
		 *
		 * @return string
		 */
		public function getInstanceCode () {
			return $this->instanceCode;
		}

		/**
		 * Para obtener la fecha en que se registro la aplicacion
		 *
		 * @return DateTime
		 */
		public function getRegistrationDate () {
			return $this->registrationDate;
		}

		/**
		 * Para obtener la fecha de vencimiento de la aplicacion suscrita en el plan contratado en la instancia.
		 *
		 * @return DateTime
		 */
		public function getServiceEndDate () {
			return $this->serviceEndDate;
		}

		/**
		 * Para obtener la fecha de inicio de la aplicacion suscrita en el plan contratado en la instancia.
		 *
		 * @return DateTime
		 */
		public function getServiceStartDate () {
			return $this->serviceStartDate;
		}

		/**
		 * Para obtener el estatus de la aplicacion contratada, siendo paga o vencida
		 *
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}

		/**
		 * Establece el codigo de la aplicacion a suscribir
		 *
		 * @param string $applicationCode
		 *
		 * @return ApplicationSubscription
		 */
		public function setApplicationCode ($applicationCode) {
			if (is_scalar ($applicationCode)) {
				$this->applicationCode = $applicationCode;
			} else {
				$this->applicationCode = null;
			}
			return $this;
		}

		/**
		 * Establece la descripcion asignada a la aplicacion que se va a suscribir
		 *
		 * @param string $applicationDescription
		 *
		 * @return ApplicationSubscription
		 */
		public function setApplicationDescription ($applicationDescription) {
			if (is_scalar ($applicationDescription)) {
				$this->applicationDescription = $applicationDescription;
			} else {
				$this->applicationDescription = null;
			}
			return $this;
		}

		/**
		 * Establece el nombre de la aplicacion que se va a suscribir
		 *
		 * @param string $applicationName
		 *
		 * @return ApplicationSubscription
		 */
		public function setApplicationName ($applicationName) {
			if (is_scalar ($applicationName)) {
				$this->applicationName = $applicationName;
			} else {
				$this->applicationName = null;
			}
			return $this;
		}

		/**
		 * Establece el codigo asignado a la instancia
		 *
		 * @param string $instanceCode
		 *
		 * @return ApplicationSubscription
		 */
		public function setInstanceCode ($instanceCode) {
			if (is_scalar ($instanceCode)) {
				$this->instanceCode = $instanceCode;
			} else {
				$this->instanceCode = null;
			}
			return $this;
		}

		/**
		 * Establece la fecha en que se registro la aplicacion
		 *
		 * @param DateTime|string $registrationDate
		 *
		 * @return ApplicationSubscription
		 */
		public function setRegistrationDate ($registrationDate) {
			if ((!empty ($registrationDate)) && (is_scalar ($registrationDate))) {
				$dummy = date_create ($registrationDate);
			} else if ($registrationDate instanceof DateTime) {
				$dummy = $registrationDate;
			} else {
				$dummy = null;
			}

			if (!empty ($dummy)) {
				$this->registrationDate = $dummy;
			} else {
				$this->registrationDate = null;
			}
			return $this;
		}

		/**
		 * Establece la fecha de vencimiento de la aplicacion suscrita en el plan contratado en la instancia.
		 *
		 * @param DateTime|string $serviceEndDate
		 *
		 * @return ApplicationSubscription
		 */
		public function setServiceEndDate ($serviceEndDate) {
			if ((!empty ($serviceEndDate)) && (is_scalar ($serviceEndDate))) {
				$dummy = date_create ($serviceEndDate);
			} else if ($serviceEndDate instanceof DateTime) {
				$dummy = $serviceEndDate;
			} else {
				$dummy = null;
			}

			if (!empty ($dummy)) {
				$this->serviceEndDate = $dummy;
			} else {
				$this->serviceEndDate = null;
			}
			return $this;
		}

		/**
		 * Establece la fecha de inicio de la aplicacion suscrita en el plan contratado en la instancia.
		 *
		 * @param DateTime|string $serviceStartDate
		 *
		 * @return ApplicationSubscription
		 */
		public function setServiceStartDate ($serviceStartDate) {
			if ((!empty ($serviceStartDate)) && (is_scalar ($serviceStartDate))) {
				$dummy = date_create ($serviceStartDate);
			} else if ($serviceStartDate instanceof DateTime) {
				$dummy = $serviceStartDate;
			} else {
				$dummy = null;
			}

			if (!empty ($dummy)) {
				$this->serviceStartDate = $dummy;
			} else {
				$this->serviceStartDate = null;
			}
			return $this;
		}

		/**
		 * Establece el estatus de la aplicacion contratada, siendo paga o vencida
		 *
		 * @param string $status
		 *
		 * @return ApplicationSubscription
		 */
		public function setStatus ($status) {
			if (in_array ($status, self::getAvailableStatuses (), true)) {
				$this->status = $status;
			} else {
				$this->status = null;
			}
			return $this;
		}

		/**
		 * Valida que la suscripcion de la aplicacion poseea todos los atributos por la cual se define
		 *
		 * @throws ApplicationSubscriptionException
		 */
		public function validate () {
			if (empty ($this->applicationCode)) {
				throw new ApplicationSubscriptionException (ApplicationSubscriptionException::ERROR_APPLICATION_SUBSCRIPTION_EMPTY_APPLICATION_CODE);
			} else if (empty ($this->instanceCode)) {
				throw new ApplicationSubscriptionException (ApplicationSubscriptionException::ERROR_APPLICATION_SUBSCRIPTION_EMPTY_INSTANCE_CODE);
			} else if (empty ($this->registrationDate)) {
				throw new ApplicationSubscriptionException (ApplicationSubscriptionException::ERROR_APPLICATION_SUBSCRIPTION_EMPTY_REGISTRATION_DATE);
			} else if (empty ($this->status)) {
				throw new ApplicationSubscriptionException (ApplicationSubscriptionException::ERROR_APPLICATION_SUBSCRIPTION_EMPTY_STATUS);
			}
		}

		/**
		 * Obtiene los estatus disponibles (Activo o Inactivo) de la aplicacion suscrita
		 *
		 * @return string[]
		 */
		public static function getAvailableStatuses () {
			return array (self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_SUBSCRIBED);
		}

		/**
		 * Instanciacion de la clase ApplicationSubscription. Se obtiene un objeto ApplicationSubscription con los atributos de la clase
		 *
		 * @return ApplicationSubscription
		 */
		public static function getInstance () {
			return new self ();
		}

	}
