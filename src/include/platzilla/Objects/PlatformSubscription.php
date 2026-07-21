<?php
	require_once ('include/Braintree/autoload.php');
	require_once ('include/platzilla/Exceptions/PlatformSubscriptionException.php');
	require_once ('include/platzilla/Objects/ApplicationSubscription.php');
	require_once ('include/platzilla/Objects/ModuleSubscription.php');
	require_once ('include/platzilla/Objects/Payment.php');
	require_once ('include/platzilla/Objects/PlatformBillingPlan.php');
	require_once ('include/platzilla/Objects/PlatformSubscriptionInterface.php');

	/**
	 * Class PlatformSubscription
	 *
	 * En esta clase se define el objeto "Suscripción Plataforma" el cual hace referencia a las suscripciones que realizan los usuarios para dar de alta una "Instancia".
	 **/
	class PlatformSubscription implements PlatformSubscriptionInterface {
		/** @var integer */
		private $accountId;

		/** @var ApplicationSubscription[] */
		private $applicationSubscriptions;

		/** @var PlatformBillingPlan */
		private $billingPlan;

		/** @var Braintree\Customer */
		private $customer;

		/** @var string */
		private $instanceCode;

		/** @var string */
		private $lastGatewayErrorMessage;

		/** @var ModuleSubscription[] */
		private $moduleSubscriptions;

		/** @var integer */
		private $paymentDay;

		/** @var Payment[] */
		private $pendingPayments;

		/** @var integer */
		private $pricebookId;

		/** @var DateTime */
		private $registrationDate;

		/** @var DateTime */
		private $serviceEndDate;

		/** @var DateTime */
		private $serviceStartDate;

		/** @var string */
		private $status;

		/** @var integer */
		private $subscribedUsers;

		/** @var integer */
		private $totalActiveUsers;

		/** @var float */
		private $totalDiskSpace;

		/**
		 * Para validar las aplicaciones suscritas en una instancia
		 *
		 * @throws PlatformSubscriptionException
		 * @throws ApplicationSubscriptionException
		 */
		private function validateApplicationSubscriptions () {
			if (empty ($this->applicationSubscriptions)) {
				return;
			}

			foreach ($this->applicationSubscriptions as $subscription) {
				if (!($subscription instanceof ApplicationSubscription)) {
					throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_INVALID_APPLICATION_SUBSCRIPTION);
				}
				$subscription->validate ();
			}
		}

		/**
		 * Valida los pagos pendientes que hay de la suscripción de la instancia
		 *
		 * @throws PlatformSubscriptionException
		 * @throws PaymentException
		 */
		private function validatePendingPayments () {
			if (empty ($this->pendingPayments)) {
				return;
			}

			foreach ($this->pendingPayments as $payment) {
				if (!($payment instanceof Payment)) {
					throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_INVALID_PAYMENT);
				}
				$payment->validate ();
			}
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
		 * Para obtener el listado de las aplicaciones que están suscritas en la instancia
		 *
		 * @return ApplicationSubscription[]
		 */
		public function getApplicationSubscriptions () {
			return $this->applicationSubscriptions;
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
		 * Para obtener el cliente
		 *
		 * @return \Braintree\Customer
		 */
		public function getCustomer () {
			return $this->customer;
		}

		/**
		 * Para obtener el código asignado a la instancia
		 *
		 * @return string
		 */
		public function getInstanceCode () {
			return $this->instanceCode;
		}

		/**
		 * Para obtener el último mensaje de error obtenido
		 *
		 * @return string
		 */
		public function getLastGatewayErrorMessage () {
			return $this->lastGatewayErrorMessage;
		}

		/**
		 * Para obtener el listado de módulos suscriptos en la instancia
		 *
		 * @return ModuleSubscription[]
		 */
		public function getModuleSubscriptions () {
			return $this->moduleSubscriptions;
		}

		/**
		 * Para obtener el día de pago de la suscripción asociada a la instancia
		 *
		 * @return integer
		 */
		public function getPaymentDay () {
			return $this->paymentDay;
		}

		/**
		 * Para obtener los pagos pendientes
		 *
		 * @return Payment[]
		 */
		public function getPendingPayments () {
			return $this->pendingPayments;
		}

		/**
		 * Para obtener el ID de la lista de precios de los planes asociados a la instancia
		 *
		 * @return integer
		 */
		public function getPricebookId () {
			return $this->pricebookId;
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
		 * Para obtener la fecha de vencimiento del servicio o plan suscrito en la instancia.
		 *
		 * @return DateTime
		 */
		public function getServiceEndDate () {
			return $this->serviceEndDate;
		}

		/**
		 * Para obtener la fecha de inicio del servicio o plan suscrito en la instancia
		 *
		 * @return DateTime
		 */
		public function getServiceStartDate () {
			return $this->serviceStartDate;
		}

		/**
		 * Para obtener el estatus actual en que se encuentra la instancia
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
		 * Para obtener el total de usuarios activos en la instancia de acuerdo con el plan suscrito
		 *
		 * @return integer
		 */
		public function getTotalActiveUsers () {
			return $this->totalActiveUsers;
		}

		/**
		 * Para obtener el tamaño actual de espacio disponible en el plan contratado en la instancia
		 *
		 * @return float
		 */
		public function getTotalDiskSpace () {
			return $this->totalDiskSpace;
		}

		/**
		 * Establece y valida el ID de la cuenta suscrita a la instancia
		 *
		 * @param integer $accountId
		 *
		 * @return PlatformSubscription
		 */
		public function setAccountId ($accountId) {
			if ((is_numeric ($accountId)) && ($accountId > 0) && (intval ($accountId) == $accountId)) {
				$this->accountId = intval ($accountId);
			} else {
				$this->accountId = null;
			}
			return $this;
		}

		/**
		 * Establece las aplicaciones que han sido suscritas en la instancia
		 *
		 * @param ApplicationSubscription[] $applicationSubscriptions
		 *
		 * @return PlatformSubscription
		 */
		public function setApplicationSubscriptions ($applicationSubscriptions) {
			if ((is_array ($applicationSubscriptions)) && (!empty ($applicationSubscriptions))) {
				$this->applicationSubscriptions = $applicationSubscriptions;
			} else {
				$this->applicationSubscriptions = null;
			}
			return $this;
		}

		/**
		 * Establece la dirección de facturación del plan que se asigna al plan contratado en la instancia
		 *
		 * @param PlatformBillingPlan $billingPlan
		 *
		 * @return PlatformSubscription
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
		 * Establece el cliente a ser empleado en la pasarela de pago Braintree
		 *
		 * @param \Braintree\Customer $customer
		 *
		 * @return PlatformSubscription
		 */
		public function setCustomer ($customer) {
			if ($customer instanceof \Braintree\Customer) {
				$this->customer = $customer;
			} else {
				$this->customer = null;
			}
			return $this;
		}

		/**
		 * Establece el código de la instancia a ser empleado para el plan de suscripción
		 *
		 * @param string $instanceCode
		 *
		 * @return PlatformSubscription
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
		 * Establece el último mensaje de error obtenido
		 *
		 * @param string $lastGatewayErrorMessage
		 *
		 * @return PlatformSubscription
		 */
		public function setLastGatewayErrorMessage ($lastGatewayErrorMessage) {
			if (is_scalar ($lastGatewayErrorMessage)) {
				$this->lastGatewayErrorMessage = $lastGatewayErrorMessage;
			} else {
				$this->lastGatewayErrorMessage = null;
			}
			return $this;
		}

		/**
		 * Establece el listado de módulos suscriptos en la instancia
		 *
		 * @param ModuleSubscription[] $moduleSubscriptions
		 *
		 * @return PlatformSubscription
		 */
		public function setModuleSubscriptions ($moduleSubscriptions) {
			if ((is_array ($moduleSubscriptions)) && (!empty ($moduleSubscriptions))) {
				$this->moduleSubscriptions = $moduleSubscriptions;
			} else {
				$this->moduleSubscriptions = null;
			}
			return $this;
		}

		/**
		 * Establece los pagos pendientes
		 *
		 * @param Payment[] $pendingPayments
		 *
		 * @return PlatformSubscription
		 */
		public function setPendingPayments ($pendingPayments) {
			if ((is_array ($pendingPayments)) && (!empty ($pendingPayments))) {
				$this->pendingPayments = $pendingPayments;
			} else {
				$this->pendingPayments = null;
			}
			return $this;
		}

		/**
		 * Establece el ID de la lista de precios de los planes asociados a la instancia
		 *
		 * @param integer $pricebookId
		 *
		 * @return PlatformSubscription
		 */
		public function setPricebookId ($pricebookId) {
			if ((is_numeric ($pricebookId)) && ($pricebookId > 0) && (intval ($pricebookId) == $pricebookId)) {
				$this->pricebookId = intval ($pricebookId);
			} else {
				$this->pricebookId = null;
			}
			return $this;
		}

		/**
		 * Establece la fecha en que se registró la instancia
		 *
		 * @param DateTime|string $registrationDate
		 *
		 * @return PlatformSubscription
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
		 * Establece la fecha de vencimiento del servicio o plan suscrito en la instancia.
		 *
		 * @param DateTime|string $serviceEndDate
		 *
		 * @return PlatformSubscription
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
		 * Establece la fecha de inicio del servicio o plan suscrito en la instancia
		 *
		 * @param DateTime|string $serviceStartDate
		 *
		 * @return PlatformSubscription
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
				$serviceStartDateDay    = intval ($this->serviceStartDate->format ('d'));
				if (($serviceStartDateDay >= 1) && ($serviceStartDateDay <= 28)) {
					$this->paymentDay = $serviceStartDateDay;
				} else {
					$this->paymentDay = 1;
				}
			} else {
				$this->serviceStartDate       = null;
				$this->paymentDay = null;
			}
			return $this;
		}

		/**
		 * Establece el estatus actual en que se encuentra la instancia
		 *
		 * @param string $status
		 *
		 * @return PlatformSubscription
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
		 * Establece los usuarios suscritos
		 *
		 * @param integer $subscribedUsers
		 *
		 * @return PlatformSubscription
		 */
		public function setSubscribedUsers ($subscribedUsers) {
			$this->subscribedUsers = $subscribedUsers;
			return $this;
		}

		/**
		 * Establece el total de usuarios activos en la instancia de acuerdo con el plan suscrito
		 *
		 * @param integer $totalActiveUsers
		 *
		 * @return PlatformSubscription
		 */
		public function setTotalActiveUsers ($totalActiveUsers) {
			if ((is_numeric ($totalActiveUsers)) && ($totalActiveUsers >= 0) && (intval ($totalActiveUsers) == $totalActiveUsers)) {
				$this->totalActiveUsers = intval ($totalActiveUsers);
			} else {
				$this->totalActiveUsers = null;
			}
			return $this;
		}

		/**
		 * Establece el tamaño actual de espacio disponible en el plan contratado en la instancia
		 *
		 * @param float $totalDiskSpace
		 *
		 * @return PlatformSubscription
		 */
		public function setTotalDiskSpace ($totalDiskSpace) {
			if ((is_numeric ($totalDiskSpace)) && ($totalDiskSpace >= 0)) {
				$this->totalDiskSpace = floatval ($totalDiskSpace);
			} else {
				$this->totalDiskSpace = null;
			}
			return $this;
		}

		/**
		 * Para obtener el total de aplicaciones suscritas en el plan contratado en la instancia
		 *
		 * @return integer
		 */
		public function getTotalSubscribedApplications () {
			if (empty ($this->applicationSubscriptions)) {
				return 0;
			}

			$total = 0;
			foreach ($this->applicationSubscriptions as $subscription) {
				if ($subscription->getStatus () == ApplicationSubscription::STATUS_SUBSCRIBED) {
					$total++;
				}
			}
			return $total;
		}

		/**
		 * Para validar que los parametros suficientes (administrador, usuarios, aplicaciones, módulos) de la suscripción estén correctos.
		 *
		 * @throws PlatformSubscriptionException
		 */
		public function validate () {
			if (empty ($this->accountId)) {
				throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_EMPTY_ACCOUNT_ID);
			} else if (empty ($this->instanceCode)) {
				throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_EMPTY_INSTANCE_CODE);
			} else if (empty ($this->registrationDate)) {
				throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_EMPTY_REGISTRATION_DATE);
			} else if ($this->totalActiveUsers === null) {
				throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_EMPTY_TOTAL_ACTIVE_USERS);
			} else if ($this->totalDiskSpace === null) {
				throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_EMPTY_TOTAL_DISK_SPACE);
			} else if (!empty ($this->billingPlan)) {
				$this->billingPlan->validate ();
			}
			$this->validateApplicationSubscriptions ();
			$this->validatePendingPayments ();
		}

		/**
		 * Obtiene los estatus disponibles (Activo o Inactivo) de la instancia
		 *
		 * @return string[]
		 */
		public static function getAvailableStatuses () {
			return array (self::STATUS_ACTIVE, self::STATUS_INACTIVE);
		}

		/**
		 * Instanciación de la clase PlatformSubscription. Se obtiene un objeto PlatformSubscription con los atributos de la clase.
		 *
		 * @return PlatformSubscription
		 */
		public static function getInstance () {
			return new self ();
		}

	}
