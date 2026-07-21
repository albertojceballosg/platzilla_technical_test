<?php
	require_once ('include/Braintree/autoload.php');
	require_once ('include/platzilla/Exceptions/PaymentGatewayException.php');
	require_once ('include/platzilla/Objects/Payment.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/AdbManager.class.php');

	/**
	 * Gestiona las operaciones con la pasarela de pagos
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
	 * @codingStandardsIgnoreEnd
	 */
	class PaymentGatewayManager {
		// El ID del plan como debe estar registrado en Braintree
		const PLAN_ID        = 'platzilla_mensual';
		const PLAN_COURSE_ID = 'platzilla_pago_unico';

		/** @var PearDatabase */
		private $adb;

		/**
		 * Constructor
		 */
		public function __construct () {
			$this->adb = AdbManager::getInstance ()->getMasterAdb ();
			$this->initialize ();
		}

		/**
		 * @param integer $subscriptionId
		 *
		 * @throws PaymentGatewayException
		 */
		private function cancelSubscription ($subscriptionId) {
			$response = Braintree\Subscription::cancel ($subscriptionId);
			if (!$response->success) {
				throw new PaymentGatewayException ($this->getHumanReadableErrorMessage ($response));
			}
		}

		/**
		 * Configura la conexión a Braintree con los parámetros requeridos
		 */
		private function initialize () {
			global $braintreeConfiguration;
			require_once ('config.braintree.php');
			\Braintree\Configuration::environment ($braintreeConfiguration ['environment']);
			\Braintree\Configuration::merchantId ($braintreeConfiguration ['merchantid']);
			\Braintree\Configuration::publicKey ($braintreeConfiguration ['publickey']);
			\Braintree\Configuration::privateKey ($braintreeConfiguration ['privatekey']);
		}

		/**
		 * Determina si la respuesta enviada por Braintree refiere a un error, y de ser así obtiene un mensaje de error legible
		 *
		 * @param \Braintree\Result\Error $response La respuesta de Braintree
		 *
		 * @return string|null Un mensaje de error comprensible o <code>null</code> si la operación fue exitosa
		 */
		private function getHumanReadableErrorMessage ($response) {
			if ($response->success) {
				$errorMessage = null;
			} else if ((isset ($response->transaction)) && ($response->transaction instanceof \Braintree\Transaction)) {
				if ($response->transaction->status == 'processor_declined') {
					$code         = isset ($response->transaction->processorResponseCode) ? "{$response->transaction->processorResponseCode} -" : '';
					$message      = isset ($response->transaction->processorResponseText) ? $response->transaction->processorResponseText : '';
					$details      = isset ($response->transaction->additionalProcessorResponse) ? "({$response->transaction->additionalProcessorResponse})" : '';
					$errorMessage = trim ("La transacción ha sido rechazada por el emisor del método de pago: {$code} {$message} {$details}");
				} else if ($response->transaction->status == 'settlement_declined') {
					$code         = isset ($response->transaction->processorSettlementResponseCode) ? "{$response->transaction->processorSettlementResponseCode} -" : '';
					$message      = isset ($response->transaction->processorSettlementResponseText) ? $response->transaction->processorSettlementResponseText : '';
					$errorMessage = trim ("La transacción ha sido rechazada por el emisor del método de pago: {$code} {$message}");
				} else if ($response->transaction->status == 'gateway_rejected') {
					$message      = isset ($response->transaction->gatewayRejectionReason) ? $response->transaction->gatewayRejectionReason : '';
					$errorMessage = trim ("La transacción ha sido rechazada por la pasarela de pagos: {$message}");
				} else if ((isset ($response->riskData)) && (isset ($response->riskData->decision)) && ($response->riskData->decision == 'Decline')) {
					$errorMessage = trim ('La transacción ha sido rechazada por la pasarela de pagos por riesgos de seguridad');
				} else {
					$errorMessage = null;
				}
			} else if (!empty ($response->errors)) {
				// Errores de validación
				$errors        = $response->errors->deepAll ();
				$errorMessages = array ();
				/** @var \Braintree\Error\Validation $error */
				foreach ($errors as $error) {
					$errorMessages [] = trim ("{$error->attribute}: {$error->code} {$error->message}");
				}
				$errorMessage = trim ("La transacción ha sido rechazada por problemas con el método de pago:\n" . join ("\n", $errorMessages));
			} else if (isset ($response->message)) {
				$errorMessage = $response->message;
			} else {
				$errorMessage = null;
			}
			return $errorMessage;
		}

		/**
		 * Obtiene el status de los objetos Payment equivalente con los estados de una transacción
		 *
		 * @param string $transactionStatus
		 *
		 * @return null|string
		 */
		private function getPaymentStatus ($transactionStatus) {
			if (in_array ($transactionStatus, array ('authorization_expired', 'settlement_declined', 'failed', 'gateway_rejected', 'processor_declined'))) {
				$paymentStatus = Payment::STATUS_REJECTED;
			} else if ($transactionStatus == 'voided') {
				$paymentStatus = Payment::STATUS_CANCELLED;
			} else if (in_array ($transactionStatus, array ('authorized', 'authorizing', 'settlement_pending', 'settling', 'submitted_for_settlement'))) {
				$paymentStatus = Payment::STATUS_SUBMITTED;
			} else if ($transactionStatus == 'settled') {
				$paymentStatus = Payment::STATUS_PAID;
			} else {
				$paymentStatus = null;
			}
			return $paymentStatus;
		}

		/**
		 * Realiza el cargo al método de pago por defecto del cliente por el monto de la suscripción mensual
		 *
		 * @param \Braintree\Subscription $subscription La suscripción en Braintree
		 *
		 * @return \Braintree\Transaction La transacción resultante del cargo
		 *
		 * @throws PaymentGatewayException En caso de presentarse algún error
		 */
		private function chargeSubscription (\Braintree\Subscription $subscription) {
			$response = Braintree\Subscription::retryCharge (isset ($subscription->id) ? $subscription->id : null);
			if (!$response->success) {
				throw new PaymentGatewayException ($this->getHumanReadableErrorMessage ($response));
			}
			$transaction = isset ($response->transaction) ? $response->transaction : null;
			$response    = Braintree\Transaction::submitForSettlement ($transaction->id);
			if (!$response->success) {
				throw new PaymentGatewayException ($this->getHumanReadableErrorMessage ($response));
			}
			return $transaction;
		}

		/**
		 * Realiza el cargo al método de pago por defecto del cliente por el monto de un pago pendiente
		 *
		 * @param string $customerId El ID del cliente en Braintree
		 * @param Payment $payment El pago pendiente por ejecutar
		 *
		 * @return \Braintree\Transaction La transacción resultante del cargo
		 *
		 * @throws PaymentGatewayException En caso de presentarse algún error
		 */
		private function chargePayment ($customerId, $payment) {
			if ($payment->getStatus () == Payment::STATUS_PAID) {
				return null;
			}
			/** @var \Braintree\Result\Error $response */
			$response = Braintree\Transaction::sale (
				array (
					'amount'     => doubleval (round ($payment->getTotalAmount (), 2)),
					'customerId' => $customerId,
					'options'    => array ('submitForSettlement' => true),
				)
			);
			if (!$response->success) {
				throw new PaymentGatewayException ($this->getHumanReadableErrorMessage ($response));
			}
			return isset ($response->transaction) ? $response->transaction : null;
		}

		/**
		 * Elimina un método de pago registrado en Braintree
		 *
		 * @param string $paymentMethodId El ID del método de pago en Braintree
		 *
		 * @throws PaymentGatewayException En caso de presentarse algún error
		 */
		private function deletePaymentMethod ($paymentMethodId) {
			$response = \Braintree\PaymentMethod::delete ($paymentMethodId);
			if (!$response->success) {
				throw new PaymentGatewayException ($response->message);
			}
		}

		/**
		 * Obtiene un cliente registrado en Braintree
		 *
		 * @param string $customerId El ID del cliente
		 *
		 * @return \Braintree\Customer|null El cliente existente en Braintree o <code>null</code> si no está registrado
		 */
		private function fetchCustomerById ($customerId) {
			try {
				$customer = \Braintree\Customer::find ($customerId);
			} catch (\Braintree\Exception\NotFound $ignored) {
				$customer = null;
			}
			return $customer;
		}

		/**
		 * Obtiene el método de pago por defecto del cliente suministrado como parámetro
		 *
		 * @param \Braintree\Customer $customer El cliente registrado en Braintree
		 *
		 * @return \Braintree\CreditCard|null El método de pago por defecto o <code>null</code> en caso de no existir
		 */
		private function getDefaultPaymentMethod (\Braintree\Customer $customer) {
			$creditCards = $customer->paymentMethods;
			if (empty ($creditCards)) {
				return null;
			}

			$defaultPaymentMethod = null;
			/** @var \Braintree\CreditCard $creditCard */
			foreach ($creditCards as $creditCard) {
				if ($creditCard->isDefault ()) {
					$defaultPaymentMethod = $creditCard;
					break;
				}
			}
			return $defaultPaymentMethod;
		}

		/**
		 * Busca entre las suscripciones asociadas al método de pago por defecto la primera suscripción activa
		 *
		 * @param \Braintree\Customer $customer El cliente registrado en Braintree
		 *
		 * @return \Braintree\Subscription|null La suscripción por defecto o <code>null</code> si no existe
		 */
		private function getDefaultSubscription (\Braintree\Customer $customer) {
			$creditCards = $customer->creditCards;
			if (empty ($creditCards)) {
				return null;
			}

			$defaultPaymentMethod = $this->getDefaultPaymentMethod ($customer);
			if (empty ($defaultPaymentMethod)) {
				return null;
			}

			if (!isset ($defaultPaymentMethod->subscriptions)) {
				return null;
			}

			$defaultSubscription = null;
			/** @var \Braintree\Subscription $subscription */
			foreach ($defaultPaymentMethod->subscriptions as $subscription) {
				if ((isset ($subscription->status)) && (!in_array ($subscription->status, array (\Braintree\Subscription::EXPIRED, \Braintree\Subscription::CANCELED)))) {
					$defaultSubscription = $subscription;
					break;
				}
			}
			return $defaultSubscription;
		}

		/**
		 * Obtiene la última transacción asociada a la suscripción
		 *
		 * @param \Braintree\Subscription $subscription
		 *
		 * @return \Braintree\Transaction|null
		 */
		private function getLastSubscriptionTransaction (\Braintree\Subscription $subscription) {
			if ((empty ($subscription->transactions)) || (!is_array ($subscription->transactions))) {
				return null;
			} else {
				return $subscription->transactions [0];
			}
		}

		/**
		 * Registra un cliente en Braintree
		 *
		 * @codingStandardsIgnoreStart
		 *
		 * @param array $arguments Argumentos en el siguiente formato:
		 *      array (
		 *          'id'        => Id de la instancia,
		 *          'company'   => Nombre de la empresa,
		 *          'email'     => Correo electrónico,
		 *          'fax'       => Número de fax de la empresa,
		 *          'firstName' => Nombre del contacto,
		 *          'lastName'  => Apellido del contacto,
		 *          'phone'     => Teléfono de la empresa,
		 *          'website'   => Website de la empresa,
		 *      ))
		 *
		 * @codingStandardsIgnoreEnd
		 *
		 * @return \Braintree\Customer|null El cliente ya creado en Braintree
		 * @throws PaymentGatewayException Si se ha presentado algún error
		 */
		private function registerCustomer ($arguments) {
			$response = \Braintree\Customer::create (
				array (
					'id'        => $arguments ['code'],
					'company'   => $arguments ['accountname'],
					'email'     => $arguments ['email'],
					'fax'       => $arguments ['fax'],
					'firstName' => $arguments ['firstname'],
					'lastName'  => $arguments ['lastname'],
					'phone'     => $arguments ['phone'],
					'website'   => $arguments ['website'],
				)
			);
			if (!$response->success) {
				throw new PaymentGatewayException ($this->getHumanReadableErrorMessage ($response));
			}
			return !empty ($response->customer) ? $response->customer : null;
		}

		/**
		 * Registra un nuevo método de pago para un cliente
		 *
		 * @codingStandardsIgnoreStart
		 *
		 * @param string $customerId El ID del cliente
		 * @param array $arguments Datos necesarios para registrar un método de pago, en uno de los siguientes formatos
		 *  + Para un método de pago asociado a una dirección de cobro previamente registrada:
		 *      array (
		 *          'billingAddressId' => El ID de una dirección existente,
		 *          'isdefault'        => Indica si deben asociarse las suscripciones actuales al método de pago,
		 *          'nonce'            => El nonce relacionado al método de pago generado por Braintree,
		 *      )
		 *  + Para un método de pago asociado a una nueva dirección de cobro
		 *      array (
		 *          'city'               => Ciudad,
		 *          'company'            => Nombre de la compañía,
		 *          'countrycode'        => País (2 letras),
		 *          'extendedaddress'    => Segunda línea de la dirección de facturación,
		 *          'firstname'          => Nombre del cliente,
		 *          'isdefault'          => Indica si deben asociarse las suscripciones actuales al método de pago,
		 *          'lastname'           => Apellido del cliente,
		 *          'nonce'              => El nonce relacionado al método de pago generado por Braintree,
		 *          'paymentmethodnonce' => Nonce generado por Braintree,
		 *          'state'              => Estado,
		 *          'streetaddress'      => Primera línea de la dirección de facturación,
		 *          'zipcode'            => Código postal,
		 *      )
		 *
		 * @codingStandardsIgnoreEnd
		 *
		 * @return \Braintree\PaymentMethod|null El método de pago
		 * @throws PaymentGatewayException Si algo sale mal
		 */
		private function registerPaymentMethod ($customerId, $arguments) {
			if (!empty ($arguments ['addressid'])) {
				$billingAddressArguments = array ('billingAddressId' => $arguments ['addressid']);
			} else {
				$billingAddressArguments = array (
					'billingAddress' => array (
						'company'           => $arguments ['company'],
						'countryCodeAlpha2' => $arguments ['countrycode'],
						'extendedAddress'   => $arguments ['extendedaddress'],
						'firstName'         => $arguments ['firstname'],
						'lastName'          => $arguments ['lastname'],
						'locality'          => $arguments ['city'],
						'postalCode'        => $arguments ['zipcode'],
						'region'            => $arguments ['state'],
						'streetAddress'     => $arguments ['streetaddress'],
					),
				);
			}

			$parameters = array (
				'customerId'         => $customerId,
				'paymentMethodNonce' => $arguments ['nonce'],
				'options'            => array (
					'failOnDuplicatePaymentMethod' => true,
					'makeDefault'                  => !!$arguments ['isdefault'],
					'verifyCard'                   => true,
				),
			);

			$response = \Braintree\PaymentMethod::create (array_merge ($parameters, $billingAddressArguments));
			if (!$response->success) {
				throw new PaymentGatewayException ($this->getHumanReadableErrorMessage ($response));
			}
			return !empty ($response->paymentMethod) ? $response->paymentMethod : null;
		}

		/**
		 * Registra una nueva suscripción en Braintree asociada al método de pago suministrado, con el precio y la fecha de pago suministrados como parámetros
		 *
		 * @param string $paymentMethodId El ID del método de pago
		 * @param float $price El precio
		 * @param integer $billingDayOfMonth El día de inicio
		 * @param string|null $planId Plan de subscripción
		 *
		 * @return \Braintree\Subscription|null La suscripción recién registrada en Braintree
		 *
		 * @throws PaymentGatewayException En caso de presentarse algún error
		 */
		private function registerSubscription ($paymentMethodId, $price, $billingDayOfMonth, $planId = null) {
			$arguments = array (
				'neverExpires'       => true,
				'paymentMethodToken' => $paymentMethodId,
				'planId'             => (empty ($planId)) ? self::PLAN_ID : $planId,
				'price'              => $price,
				'trialPeriod'        => false,
			);
			$day       = intval (date_create ()->format ('d'));
			if (in_array ($day, array (29, 30, 31))) {
				$arguments ['billingDayOfMonth'] = $billingDayOfMonth;
			} else {
				$arguments ['options'] = array ('startImmediately' => true);
			}
			$response = \Braintree\Subscription::create ($arguments);
			if (!$response->success) {
				throw new PaymentGatewayException ($this->getHumanReadableErrorMessage ($response));
			}
			return !empty ($response->subscription) ? $response->subscription : null;
		}

		/**
		 * Establece el método de pago por defecto, cambiando el método de pago de la suscripción por defecto
		 *
		 * @param \Braintree\Customer $customer El cliente registrado en Braintree
		 * @param string $paymentMethodId El ID del método de pago
		 *
		 * @throws PaymentGatewayException En caso de presentarse algún error
		 */
		private function setDefaultPaymentMethod (\Braintree\Customer $customer, $paymentMethodId) {
			$subscription = $this->getDefaultSubscription ($customer);
			if (empty ($subscription)) {
				return;
			}

			try {
				$paymentMethod = \Braintree\PaymentMethod::find ($paymentMethodId);
			} catch (\Braintree\Exception\NotFound $nfe) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_INVALID_PAYMENT_METHOD);
			}

			$response = \Braintree\PaymentMethod::update (
				$paymentMethodId,
				array (
					'billingAddressId' => $paymentMethod->billingAddress->id,
					'options'          => array (
						'makeDefault' => true,
						'verifyCard'  => false,
					),
				)
			);
			if (!$response->success) {
				throw new PaymentGatewayException ($this->getHumanReadableErrorMessage ($response));
			}

			$response = \Braintree\Subscription::update (isset ($subscription->id) ? $subscription->id : null, array ('paymentMethodToken' => $paymentMethodId));
			if (!$response->success) {
				throw new PaymentGatewayException ($this->getHumanReadableErrorMessage ($response));
			}
		}

		/**
		 * Actualiza el precio de la suscripción con el ID suministrado como parámetro
		 *
		 * @param string $subscriptionId EL ID de la suscripción
		 * @param float $price El nuevo precio de la suscripción
		 *
		 * @return \Braintree\Subscription|null La suscripción actualizada
		 *
		 * @throws PaymentGatewayException En caso de presentarse algún error
		 */
		private function updateSubscription ($subscriptionId, $price) {
			$response = Braintree\Subscription::update ($subscriptionId, array ('price' => $price, 'options' => array ('prorateCharges' => true)));
			if (!$response->success) {
				throw new PaymentGatewayException ($this->getHumanReadableErrorMessage ($response));
			}
			return !empty ($response->subscription) ? $response->subscription : null;
		}

		/**
		 * Valida si una dirección está registrada para el cliente en Braintree
		 *
		 * @param \Braintree\Address[] $addresses
		 * @param string $addressId
		 *
		 * @throws PaymentGatewayException En caso que la dirección no esté registrada
		 */
		private function validatePaymentMethodAddress ($addresses, $addressId) {
			$found = false;
			foreach ($addresses as $address) {
				if ($addressId == $address->id) {
					$found = true;
					break;
				}
			}
			if (!$found) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_UNKNOWN_ADDRESS);
			}
		}

		/**
		 * Valida que los argumentos necesarios para registrar un pago en Braintree son válidos
		 *
		 * @codingStandardsIgnoreStart
		 *
		 * @param \Braintree\Customer $customer El cliente
		 * @param array $arguments Datos necesarios para registrar un método de pago, en uno de los siguientes formatos
		 *  + Para un método de pago asociado a una dirección de cobro previamente registrada:
		 *      array (
		 *          'billingAddressId' => El ID de una dirección existente,
		 *          'isdefault'        => Indica si deben asociarse las suscripciones actuales al método de pago,
		 *          'nonce'            => El nonce relacionado al método de pago generado por Braintree,
		 *      )
		 *  + Para un método de pago asociado a una nueva dirección de cobro
		 *      array (
		 *          'city'               => Ciudad,
		 *          'company'            => Nombre de la compañía,
		 *          'countrycode'        => País (2 letras),
		 *          'extendedaddress'    => Segunda línea de la dirección de facturación,
		 *          'firstname'          => Nombre del cliente,
		 *          'isdefault'          => Indica si deben asociarse las suscripciones actuales al método de pago,
		 *          'lastname'           => Apellido del cliente,
		 *          'nonce'              => El nonce relacionado al método de pago generado por Braintree,
		 *          'paymentmethodnonce' => Nonce generado por Braintree,
		 *          'state'              => Estado,
		 *          'streetaddress'      => Primera línea de la dirección de facturación,
		 *          'zipcode'            => Código postal,
		 *      )
		 *
		 * @codingStandardsIgnoreEnd
		 *
		 * @throws PaymentGatewayException
		 */
		private function validatePaymentMethodArguments (\Braintree\Customer $customer, $arguments) {
			if (empty ($arguments)) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_EMPTY_ARGUMENTS);
			} else if (empty ($arguments ['nonce'])) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_EMPTY_NONCE);
			} else if (!empty ($arguments ['addressid'])) {
				$this->validatePaymentMethodAddress ($customer->addresses, $arguments ['addressid']);
			} else if (empty ($arguments ['firstname'])) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_EMPTY_FIRST_NAME);
			} else if (empty ($arguments ['lastname'])) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_EMPTY_LAST_NAME);
			} else if (empty ($arguments ['streetaddress'])) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_EMPTY_ADDRESS);
			} else if (empty ($arguments ['zipcode'])) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_EMPTY_ZIPCODE);
			} else if (empty ($arguments ['countrycode'])) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_EMPTY_COUNTRY);
			}
		}

		/**
		 * Realiza el cargo al método de pago por defecto del cliente por todos los pagos pendientes
		 *
		 * @param string $instanceCode Código de la instancia, que será el ID del cliente en Braintree
		 * @param Payment[] $payments Pagos que el cliente tiene pendientes por realizar
		 *
		 * @return Payment[]|null Resultados de los cargos realizados al cliente. Cada elemento del arreglo  corresponde al pago procesado con el ID de la pasarela de pagos
		 *                        y el status o el mensaje de error actualizados
		 *
		 * @throws PaymentGatewayException En caso de presentarse algún error
		 */
		public function chargeInstanceCustomerPayments ($instanceCode, $payments) {
			if (empty ($payments)) {
				return null;
			} else if (empty ($instanceCode)) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_EMPTY_INSTANCE_CODE);
			}

			$customer = $this->fetchCustomerById ($instanceCode);
			if (!empty ($customer)) {
				$paymentMethod = $this->getDefaultPaymentMethod ($customer);
			} else {
				$paymentMethod = null;
			}

			$results = array ();
			foreach ($payments as $payment) {
				try {
					if (empty ($customer)) {
						throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_CUSTOMER_NOT_REGISTERED);
					} else if ((empty ($paymentMethod)) || (!isset ($paymentMethod->token))) {
						throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_DEFAULT_PAYMENT_METHOD_NOT_REGISTERED);
					}

					if ($payment->getType () == Payment::TYPE_SUBSCRIPTION) {
						$subscription = $this->getDefaultSubscription ($customer);
						$transaction  = $this->chargeSubscription ($subscription);
						$payment->setId ($transaction->id)->setStatus (Payment::STATUS_SUBMITTED);
					} else if ($payment->getType () == Payment::TYPE_TRANSACTION) {
						$transaction = $this->chargePayment ($customer->id, $payment);
						$payment->setId ($transaction->id)->setStatus (Payment::STATUS_SUBMITTED);
					}
					$results [] = $payment->setLastErrorMessage (null);
				} catch (Exception $e) {
					$results [] = $payment->setLastErrorMessage ($e->getMessage ());
				}
			}
			return $results;
		}

		/**
		 * @param string $instanceCode
		 *
		 * @throws PaymentGatewayException
		 */
		public function deleteInstanceCustomer ($instanceCode) {
			if (empty ($instanceCode)) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_EMPTY_INSTANCE_CODE);
			}

			try {
				$response = Braintree\Customer::delete ($instanceCode);
				if (!$response->success) {
					throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_UNABLE_TO_DELETE_CUSTOMER);
				}
			} catch (\Braintree\Exception\NotFound $ignored) {
				// Do nothing
			}
		}

		/**
		 * Elimina el método de pago con el ID suministrado de la lista de métodos de pago registrados para el cliente
		 *
		 * @param string $instanceCode Código de la instancia, que será el ID del cliente en Braintree
		 * @param string $paymentMethodId El ID del método de pago
		 *
		 * @throws PaymentGatewayException Si se ha presentado algún error
		 */
		public function deleteInstancePaymentMethod ($instanceCode, $paymentMethodId) {
			if (empty ($instanceCode)) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_EMPTY_INSTANCE_CODE);
			}
			$customer = $this->fetchCustomerById ($instanceCode);
			if (empty ($customer)) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_CUSTOMER_NOT_REGISTERED);
			} else if (empty ($customer->creditCards)) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_DEFAULT_PAYMENT_METHOD_NOT_REGISTERED);
			}
			/** @var \Braintree\CreditCard $firstNonDefaultPaymentMethod */
			$firstNonDefaultPaymentMethod = null;
			/** @var \Braintree\CreditCard $selectedPaymentMethod */
			$selectedPaymentMethod = null;
			foreach ($customer->creditCards as $creditCard) {
				if ($creditCard->token == $paymentMethodId) {
					$selectedPaymentMethod = $creditCard;
				} else if ((empty ($firstNonDefaultPaymentMethod)) && (!$creditCard->expired)) {
					$firstNonDefaultPaymentMethod = $creditCard;
				}
			}
			if (empty ($selectedPaymentMethod)) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_INVALID_PAYMENT_METHOD);
			} else if (!empty ($firstNonDefaultPaymentMethod)) {
				$this->setDefaultPaymentMethod ($customer, $firstNonDefaultPaymentMethod->token);
			}
			$this->deletePaymentMethod ($paymentMethodId);
		}

		/**
		 * Obtiene la información de un cliente registrado en la pasarela de pagos
		 *
		 * @param string $instanceCode Código de la instancia, que será el ID del cliente en Braintree
		 *
		 * @return \Braintree\Customer|null El objeto con la información del cliente en la pasarela de pagos o <code>null</code> si no está registrado
		 * @throws PaymentGatewayException Si se ha presentado algún error
		 */
		public function fetchInstanceCustomer ($instanceCode) {
			if (empty ($instanceCode)) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_EMPTY_INSTANCE_CODE);
			}

			return $this->fetchCustomerById ($instanceCode);
		}

		/**
		 * Obtiene el último pago cargado a la tarjeta de crédito para la suscripción por defecto de la instancia suministrada como parámetro
		 *
		 * @param string $instanceCode
		 * @param integer $taxPercentage
		 *
		 * @return null|Payment
		 * @throws PaymentGatewayException
		 */
		public function fetchLastSubscriptionPayment ($instanceCode, $taxPercentage = 0) {
			$customer = $this->fetchCustomerById ($instanceCode);
			if (empty ($customer)) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_CUSTOMER_NOT_REGISTERED);
			}

			$subscription = $this->getDefaultSubscription ($customer);
			if (empty ($subscription)) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_DEFAULT_SUBSCRIPTION_NOT_REGISTERED);
			}

			$transaction = $this->getLastSubscriptionTransaction ($subscription);
			if (isset ($transaction)) {
				/** @noinspection PhpUndefinedFieldInspection */
				$payment = Payment::getInstance ()
					->setDueDate ($transaction->createdAt)
					->setId ($transaction->id)
					->setInstanceCode ($instanceCode)
					->setLastErrorMessage ($transaction->status)
					->setServiceEndDate ($subscription->billingPeriodEndDate)
					->setServiceStartDate ($subscription->billingPeriodStartDate)
					->setStatus ($this->getPaymentStatus ($transaction->status))
					->setSubTotal ((100 * $transaction->amount) / (100 + $taxPercentage))
					->setTaxPercentage ($taxPercentage)
					->setType (Payment::TYPE_SUBSCRIPTION);
			} else {
				$payment = null;
			}
			return $payment;
		}

		/**
		 * Genera un token para ser utilizado por la interfaz de usuario
		 *
		 * @return array El token
		 */
		public function generateClientToken () {
			return \Braintree\ClientToken::generate ();
		}

		/**
		 * @param \Braintree\Customer $customer
		 *
		 * @return \Braintree\CreditCard|null
		 * @throws PaymentGatewayException
		 */
		public function hasDefaultPaymentMethod ($customer) {
			if (empty ($customer)) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_CUSTOMER_NOT_REGISTERED);
			}
			return $this->getDefaultPaymentMethod ($customer);
		}

		/**
		 * Registra un cliente en la pasarela de pagos a partir de la información de la instancia
		 *
		 * @param string $instanceCode Código de la instancia, que será el ID del cliente en Braintree
		 *
		 * @return \Braintree\Customer|null El cliente ya creado en Braintree, o <code>null</code> si la instancia dada como parámetro no está registrada
		 * @throws PaymentGatewayException Si se ha presentado algún error
		 */
		public function registerInstanceCustomer ($instanceCode) {
			if (empty ($instanceCode)) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_EMPTY_INSTANCE_CODE);
			}

			$result = $this->adb->pquery (
				'SELECT
					i.code,
					i.administrator AS email,
					a.nombre_comercial AS accountname,
					NULL AS fax,
					a.telefono AS phone,
					NULL AS website,
					c.nombre AS firstname,
					c.apellidos AS lastname
				FROM
					vtiger_instances i
					INNER JOIN vtiger_clientes a ON a.clientesid=i.accountid
					INNER JOIN vtiger_contactos c ON c.email=i.administrator
				WHERE
					i.code=?',
				array ($instanceCode)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$customer = $this->registerCustomer ($row);
			} else {
				$e        = new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_INVALID_INSTANCE_CODE . " {$instanceCode}");
				$customer = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
			return $customer;
		}

		/**
		 * @codingStandardsIgnoreStart
		 * Registra un método de pago asociado al cliente suministrado como parámetro
		 *
		 * @param string $instanceCode Código de la instancia, que será el ID del cliente en Braintree
		 * @param array $arguments Datos necesarios para registrar un método de pago, en uno de los siguientes formatos
		 *  + Para un método de pago asociado a una dirección de cobro previamente registrada:
		 *      array (
		 *          'billingAddressId' => El ID de una dirección existente,
		 *          'isdefault'        => Indica si deben asociarse las suscripciones actuales al método de pago,
		 *          'nonce'            => El nonce relacionado al método de pago generado por Braintree,
		 *      )
		 *  + Para un método de pago asociado a una nueva dirección de cobro
		 *      array (
		 *          'city'               => Ciudad,
		 *          'company'            => Nombre de la compañía,
		 *          'countrycode'        => País (2 letras),
		 *          'extendedaddress'    => Segunda línea de la dirección de facturación,
		 *          'firstname'          => Nombre del cliente,
		 *          'isdefault'          => Indica si deben asociarse las suscripciones actuales al método de pago,
		 *          'lastname'           => Apellido del cliente,
		 *          'nonce'              => El nonce relacionado al método de pago generado por Braintree,
		 *          'paymentmethodnonce' => Nonce generado por Braintree,
		 *          'state'              => Estado,
		 *          'streetaddress'      => Primera línea de la dirección de facturación,
		 *          'zipcode'            => Código postal,
		 *      )
		 *
		 * @return \Braintree\PaymentMethod|null El método de pago o <code>null</code> en caso de que la pasarela de pagos de una respuesta incorrecta.
		 * @throws PaymentGatewayException Si se ha presentado algún error
		 * @codingStandardsIgnoreEnd
		 */
		public function registerInstancePaymentMethod ($instanceCode, $arguments) {
			if (empty ($instanceCode)) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_EMPTY_INSTANCE_CODE);
			}
			$customer = $this->fetchCustomerById ($instanceCode);
			if (empty ($customer)) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_CUSTOMER_NOT_REGISTERED);
			}

			$this->validatePaymentMethodArguments ($customer, $arguments);
			$isDefault     = (empty ($customer->paymentMethods)) || ((isset ($arguments ['isdefault'])) && (!empty ($arguments ['isdefault']))) ? true : false;
			$paymentMethod = $this->registerPaymentMethod ($customer->id, $arguments);
			if ($isDefault) {
				$this->setDefaultPaymentMethod ($customer, isset ($paymentMethod->token) ? $paymentMethod->token : null);
			}

			return $paymentMethod;
		}

		/**
		 * Establece el método de pago por defecto asociado al cliente, cambiando el método de pago de la suscripción por defecto
		 *
		 * @param string $instanceCode Código de la instancia, que será el ID del cliente en Braintree
		 * @param string $paymentMethodId El ID del método de pago
		 *
		 * @throws PaymentGatewayException En caso de presentarse algún error
		 */
		public function setInstanceDefaultPaymentMethod ($instanceCode, $paymentMethodId) {
			if (empty ($instanceCode)) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_EMPTY_INSTANCE_CODE);
			}

			$customer = $this->fetchCustomerById ($instanceCode);
			if (empty ($customer)) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_CUSTOMER_NOT_REGISTERED);
			}

			$subscription = $this->getDefaultSubscription ($customer);
			if (empty ($subscription)) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_EMPTY_SUBSCRIPTION);
			}

			/** @var \Braintree\CreditCard $selectedPaymentMethod */
			$selectedPaymentMethod = null;
			foreach ($customer->paymentMethods as $paymentMethod) {
				if ($paymentMethod->token == $paymentMethodId) {
					$selectedPaymentMethod = $paymentMethod;
					break;
				}
			}
			if (empty ($selectedPaymentMethod)) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_INVALID_PAYMENT_METHOD);
			}

			$this->setDefaultPaymentMethod ($customer, $paymentMethodId);
		}

		/**
		 * @param string $instanceCode
		 * @param integer $productId
		 * @param float $productName
		 * @param string $listPrice
		 * @param float $taxPercentage
		 * @param integer $billingDayOfMonth
		 *
		 * @return Payment|null
		 * @throws PaymentGatewayException
		 */
		public function updateInstanceSubscription ($instanceCode, $productId, $productName, $listPrice, $taxPercentage, $billingDayOfMonth) {
			$customer = $this->fetchCustomerById ($instanceCode);
			if (empty ($customer)) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_CUSTOMER_NOT_REGISTERED);
			}

			$paymentMethod = $this->getDefaultPaymentMethod ($customer);
			if (empty ($customer)) {
				throw new PaymentGatewayException (PaymentGatewayException::ERROR_PAYMENT_GATEWAY_DEFAULT_PAYMENT_METHOD_NOT_REGISTERED);
			}

			$subscription         = $this->getDefaultSubscription ($customer);
			$oldSubscriptionPrice = isset ($subscription->price) ? round (doubleval ($subscription->price), 2) : 0.0;
			$newSubscriptionPrice = round (($listPrice * (1 + ($taxPercentage / 100))), 2);
			if ($newSubscriptionPrice == 0) {
				$this->cancelSubscription (isset ($subscription->id) ? $subscription->id : null);
				$type = Payment::TYPE_TRANSACTION;
			} else if (empty ($subscription)) {
				$subscription = $this->registerSubscription ($paymentMethod->token, $newSubscriptionPrice, $billingDayOfMonth);
				$transaction  = $this->getLastSubscriptionTransaction ($subscription);
				$type         = Payment::TYPE_SUBSCRIPTION;
			} else if ($oldSubscriptionPrice != $newSubscriptionPrice) {
				$subscription = $this->updateSubscription (isset ($subscription->id) ? $subscription->id : null, $newSubscriptionPrice);
				$transaction  = $this->getLastSubscriptionTransaction ($subscription);
				$type         = Payment::TYPE_TRANSACTION;
			}

			if (isset ($transaction)) {
				/** @noinspection PhpUndefinedFieldInspection */
				$payment = Payment::getInstance ()
					->setDueDate ($transaction->createdAt)
					->setId ($transaction->id)
					->setInstanceCode ($instanceCode)
					->setLastErrorMessage ($transaction->status)
					->setProductId ($productId)
					->setProductName ($productName)
					->setServiceEndDate ($subscription->billingPeriodEndDate)
					->setServiceStartDate ($subscription->billingPeriodStartDate)
					->setStatus ($this->getPaymentStatus ($transaction->status))
					->setSubTotal ((100 * $transaction->amount) / (100 + $taxPercentage))
					->setTaxPercentage ($taxPercentage)
					->setType (isset ($type) ? $type : Payment::TYPE_TRANSACTION);
			} else {
				$payment = null;
			}
			return $payment;
		}

		/**
		 * Crea un objeto PaymentGatewayManager
		 *
		 * @return PaymentGatewayManager
		 */
		public static function getInstance () {
			return new self ();
		}

	}
