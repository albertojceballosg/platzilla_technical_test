<?php
	require_once ('include/Braintree/autoload.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('modules/store/lib/Payment.class.php');

	/**
	 * Gestiona las operaciones con la pasarela de pagos
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
	 * @codingStandardsIgnoreEnd
	 */
	class PaymentGatewayManager {
		// El ID del plan como debe estar registrado en Braintree
		const PLAN_ID = 'platzilla_mensual';

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
		 * @throws Exception En caso de presentarse algún error
		 */
		private function chargeSubscription (\Braintree\Subscription $subscription) {
			$response = Braintree\Subscription::retryCharge (isset ($subscription->id) ? $subscription->id : null);
			if (!$response->success) {
				throw new Exception ($this->getHumanReadableErrorMessage ($response));
			}
			$transaction = isset ($response->transaction) ? $response->transaction : null;
			$response    = Braintree\Transaction::submitForSettlement ($transaction->id);
			if (!$response->success) {
				throw new Exception ($this->getHumanReadableErrorMessage ($response));
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
		 * @throws Exception En caso de presentarse algún error
		 */
		private function chargePayment ($customerId, $payment) {
			if ($payment->getStatus () == Payment::STATUS_PAID) {
				return null;
			}
			/** @var \Braintree\Result\Error $response */
			$response = Braintree\Transaction::sale (
				array (
					'amount'     => doubleval (round ($payment->getAmount (), 2)),
					'customerId' => $customerId,
					'options'    => array ('submitForSettlement' => true),
				)
			);
			if (!$response->success) {
				throw new Exception ($this->getHumanReadableErrorMessage ($response));
			}
			return isset ($response->transaction) ? $response->transaction : null;
		}

		/**
		 * Elimina un método de pago registrado en Braintree
		 *
		 * @param string $paymentMethodId El ID del método de pago en Braintree
		 *
		 * @throws Exception En caso de presentarse algún error
		 */
		private function deletePaymentMethod ($paymentMethodId) {
			$response = \Braintree\PaymentMethod::delete ($paymentMethodId);
			if (!$response->success) {
				throw new Exception ($response->message);
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
		 * Obtiene la información de una transacción registrada en la pasarela de pagos o <code>null</code> si no está registrada
		 *
		 * @param string $transactionId
		 *
		 * @return \Braintree\Transaction|null
		 */
		private function fetchTransactionById ($transactionId) {
			if (empty ($transactionId)) {
				return null;
			}
			try {
				$transaction = Braintree\Transaction::find ($transactionId);
			} catch (Braintree\NotFound $ignored) {
				$transaction = null;
			}
			return $transaction;
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
		 * Obtiene la última transacción asociada a la suscripción con la fecha de vencimiento suministrada como parámetro
		 *
		 * @param \Braintree\Subscription $subscription
		 *
		 * @return \Braintree\Transaction|null
		 * @throws Exception
		 */
		private function getCurrentBillingPeriodLastSubscriptionTransaction (\Braintree\Subscription $subscription) {
			if (empty ($subscription->transactions)) {
				return null;
			}

			$transaction = null;
			/** @var Braintree\Transaction $transaction */
			foreach ($subscription->transactions as $subscriptionTransaction) {
				if (
					(!isset ($subscription->billingPeriodStartDate)) &&
					(!isset ($subscription->billingPeriodEndDate)) &&
					(!isset ($subscriptionTransaction->subscriptionDetails)) &&
					(!isset ($subscriptionTransaction->subscriptionDetails->billingPeriodStartDate)) &&
					(!isset ($subscriptionTransaction->subscriptionDetails->billingPeriodEndDate))
				) {
					continue;
				}

				if (
					($subscriptionTransaction->subscriptionDetails->billingPeriodStartDate == $subscription->billingPeriodStartDate) &&
					($subscriptionTransaction->subscriptionDetails->billingPeriodEndDate == $subscription->billingPeriodEndDate)
				) {
					$transaction = $subscriptionTransaction;
					break;
				}
			}
			return $transaction;
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
		 * @throws Exception Si se ha presentado algún error
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
				throw new Exception ($this->getHumanReadableErrorMessage ($response));
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
		 * @throws Exception Si algo sale mal
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
				throw new Exception ($this->getHumanReadableErrorMessage ($response));
			}
			return !empty ($response->paymentMethod) ? $response->paymentMethod : null;
		}

		/**
		 * Registra una nueva suscripción en Braintree asociada al método de pago suministrado, con el precio y la fecha de pago suministrados como parámetros
		 *
		 * @param string $paymentMethodId El ID del método de pago
		 * @param float $price El precio
		 * @param int $billingDayOfMonth El día de inicio
		 *
		 * @return \Braintree\Subscription|null La suscripción recién registrada en Braintree
		 *
		 * @throws Exception En caso de presentarse algún error
		 */
		private function registerSubscription ($paymentMethodId, $price, $billingDayOfMonth) {
			// Braintree sólo acepta que los días de suscripción sean del 1 al 28 ó el 31
			$billingDayOfMonth = $billingDayOfMonth <= 28 ? $billingDayOfMonth : 31;
			$response          = \Braintree\Subscription::create (
				array (
					'billingDayOfMonth'  => $billingDayOfMonth,
					'neverExpires'       => true,
					'paymentMethodToken' => $paymentMethodId,
					'planId'             => self::PLAN_ID,
					'price'              => $price,
					'trialPeriod'        => false,
				)
			);
			if (!$response->success) {
				throw new Exception ($this->getHumanReadableErrorMessage ($response));
			}
			return !empty ($response->subscription) ? $response->subscription : null;
		}

		/**
		 * Establece el método de pago por defecto, cambiando el método de pago de la suscripción por defecto
		 *
		 * @param \Braintree\Customer $customer El cliente registrado en Braintree
		 * @param string $paymentMethodId El ID del método de pago
		 *
		 * @throws Exception EN caso de presentarse algún error
		 */
		private function setDefaultPaymentMethod (\Braintree\Customer $customer, $paymentMethodId) {
			$subscription = $this->getDefaultSubscription ($customer);
			if (empty ($subscription)) {
				return;
			}

			$response = \Braintree\Subscription::update (isset ($subscription->id) ? $subscription->id : null, array ('paymentMethodToken' => $paymentMethodId));
			if (!$response->success) {
				throw new Exception ($this->getHumanReadableErrorMessage ($response));
			}

			$response = \Braintree\PaymentMethod::update ($paymentMethodId, array ('options' => array ('makeDefault' => true)));
			if (!$response->success) {
				throw new Exception ($this->getHumanReadableErrorMessage ($response));
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
		 * @throws Exception En caso de presentarse algún error
		 */
		private function updateSubscription ($subscriptionId, $price) {
			$response = Braintree\Subscription::update ($subscriptionId, array ('price' => $price));
			if (!$response->success) {
				throw new Exception ($this->getHumanReadableErrorMessage ($response));
			}
			return !empty ($response->subscription) ? $response->subscription : null;
		}

		/**
		 * Actualiza el pago del tipo Payment::TYPE_SUBSCRIPTION con la información de la pasarela de pagos:
		 * 1. Si la suscripción no existe, la crea
		 * 2. Si la suscripción en la pasarela tiene un monto diferente al del pago, la actualiza
		 * 3. Actualiza el objeto Payment suministrado como parámetro
		 *
		 * @param \Braintree\Customer $customer
		 * @param Payment $payment
		 *
		 * @return Payment
		 * @throws Exception
		 */
		private function updateSubscriptionPayment (\Braintree\Customer $customer, Payment $payment) {
			$subscription = $this->getDefaultSubscription ($customer);
			if (empty ($subscription)) {
				throw new Exception ('El cliente no tiene una suscripción asociada');
			}

			$today       = date_create ('today');
			$transaction = $this->getCurrentBillingPeriodLastSubscriptionTransaction ($subscription);
			if ((isset ($transaction)) && (isset ($transaction->status))) {
				$payment->setGatewayId ($transaction->id)
					->setLastErrorMessage ($transaction->status)
					->setStatus ($this->getPaymentStatus ($transaction->status));
			} else if ($payment->getDueDate () < $today) {
				$payment->setStatus (Payment::STATUS_PAST_DUE);
			}
			return $payment;
		}

		/**
		 * Actualiza el pago del tipo Payment::TYPE_TRANSACTION con la información de la pasarela de pagos:
		 * 1. Si el pago no ha sido enviado o está pendiente, lo envía
		 * 2. Actualiza el objeto Payment suministrado como parámetro
		 *
		 * @param \Braintree\Customer $customer
		 * @param Payment $payment
		 *
		 * @return Payment
		 * @throws Exception
		 */
		private function updateTransactionPayment (\Braintree\Customer $customer, Payment $payment) {
			$paymentGatewayId = $payment->getGatewayId ();
			$paymentStatus    = $payment->getStatus ();

			if ((empty ($paymentGatewayId)) || ($paymentStatus != Payment::STATUS_SUBMITTED)) {
				// Transacción no enviada o rechazada previamente. Enviar
				$transaction = $this->chargePayment ($customer->id, $payment);
			} else {
				// Transacción existente, obtener el estado actual de la misma
				$transaction = $this->fetchTransactionById ($paymentGatewayId);
			}

			if ((isset ($transaction)) && (isset ($transaction->status))) {
				$status = $this->getPaymentStatus ($transaction->status);
				$payment->setGatewayId ($transaction->id)->setStatus ($status)->setLastErrorMessage ($transaction->status);
			}
			return $payment;
		}

		/**
		 * Valida si una dirección está registrada para el cliente en Braintree
		 *
		 * @param \Braintree\Address[] $addresses
		 * @param string $addressId
		 *
		 * @throws Exception En caso que la dirección no esté registrada
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
				throw new Exception ('La dirección suministrada no está registrada');
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
		 * @throws Exception
		 */
		private function validatePaymentMethodArguments (\Braintree\Customer $customer, $arguments) {
			if (empty ($arguments)) {
				throw new Exception ('No se han suministrado los argumentos del método de pago');
			} else if (empty ($arguments ['nonce'])) {
				throw new Exception ('No se ha suministrado el token generado por el proveedor de cobros');
			} else if (!empty ($arguments ['addressid'])) {
				$this->validatePaymentMethodAddress ($customer->addresses, $arguments ['addressid']);
			} else if (empty ($arguments ['firstname'])) {
				throw new Exception ('No se ha suministrado el nombre del cliente');
			} else if (empty ($arguments ['lastname'])) {
				throw new Exception ('No se han suministrado los apellidos del cliente');
			} else if (empty ($arguments ['streetaddress'])) {
				throw new Exception ('No se ha suministrado la dirección de cobro');
			} else if (empty ($arguments ['zipcode'])) {
				throw new Exception ('No se ha suministrado el código postal');
			} else if (empty ($arguments ['countrycode'])) {
				throw new Exception ('No se ha suministrado el país');
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
		 * @throws Exception En caso de presentarse algún error
		 */
		public function chargeInstanceCustomerPayments ($instanceCode, $payments) {
			if (empty ($payments)) {
				return null;
			}

			if (empty ($instanceCode)) {
				throw new Exception ('No se ha suministrado el código de la instancia');
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
						throw new Exception ('El cliente no está registrado en la pasarela de pagos');
					} else if ((empty ($paymentMethod)) || (!isset ($paymentMethod->token))) {
						throw new Exception ('El cliente no tiene asociado un método de pago por defecto');
					}

					if ($payment->getType () == Payment::TYPE_SUBSCRIPTION) {
						$subscription = $this->getDefaultSubscription ($customer);
						$transaction  = $this->chargeSubscription ($subscription);
						$payment->setGatewayId ($transaction->id)->setStatus (Payment::STATUS_SUBMITTED);
					} else if ($payment->getType () == Payment::TYPE_TRANSACTION) {
						$transaction = $this->chargePayment ($customer->id, $payment);
						$payment->setGatewayId ($transaction->id)->setStatus (Payment::STATUS_SUBMITTED);
					}
					$results [] = $payment->setLastErrorMessage (null);
				} catch (Exception $e) {
					$results [] = $payment->setLastErrorMessage ($e->getMessage ());
				}
			}
			return $results;
		}

		/**
		 * Elimina el método de pago con el ID suministrado de la lista de métodos de pago registrados para el cliente
		 *
		 * @param string $instanceCode Código de la instancia, que será el ID del cliente en Braintree
		 * @param string $paymentMethodId El ID del método de pago
		 *
		 * @throws Exception Si se ha presentado algún error
		 */
		public function deleteInstancePaymentMethod ($instanceCode, $paymentMethodId) {
			if (empty ($instanceCode)) {
				throw new Exception ('No se ha suministrado el código de la instancia');
			}
			$customer = $this->fetchCustomerById ($instanceCode);
			if (empty ($customer)) {
				throw new Exception ('El cliente no está registrado en la pasarela de pagos');
			} else if (empty ($customer->creditCards)) {
				throw new Exception ('El cliente suministrado no tiene métodos de pago asociados');
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
				throw new Exception ('El método de pago suministrado no está asociado al cliente');
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
		 * @throws Exception Si se ha presentado algún error
		 */
		public function fetchInstanceCustomer ($instanceCode) {
			if (empty ($instanceCode)) {
				throw new Exception ('No se ha suministrado el código de la instancia');
			}

			return $this->fetchCustomerById ($instanceCode);
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
		 * Registra un cliente en la pasarela de pagos a partir de la información de la instancia
		 *
		 * @param string $instanceCode Código de la instancia, que será el ID del cliente en Braintree
		 *
		 * @return \Braintree\Customer|null El cliente ya creado en Braintree, o <code>null</code> si la instancia dada como parámetro no está registrada
		 * @throws Exception Si se ha presentado algún error
		 */
		public function registerInstanceCustomer ($instanceCode) {
			if (empty ($instanceCode)) {
				throw new Exception ('No se ha suministrado el código de la instancia');
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
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				throw new Exception ("No se encuentra registrada la instancia con el código {$instanceCode}");
			}

			$row = $this->adb->fetchByAssoc ($result, -1, false);
			return $this->registerCustomer ($row);
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
		 * @throws Exception Si se ha presentado algún error
		 * @codingStandardsIgnoreEnd
		 */
		public function registerInstancePaymentMethod ($instanceCode, $arguments) {
			if (empty ($instanceCode)) {
				throw new Exception ('No se ha suministrado el código de la instancia');
			}
			$customer = $this->fetchCustomerById ($instanceCode);
			if (empty ($customer)) {
				throw new Exception ('El cliente no está registrado en la pasarela de pagos');
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
		 * @throws Exception En caso de presentarse algún error
		 */
		public function setInstanceDefaultPaymentMethod ($instanceCode, $paymentMethodId) {
			if (empty ($instanceCode)) {
				throw new Exception ('No se ha suministrado el código de la instancia');
			}

			$customer = $this->fetchCustomerById ($instanceCode);
			if (empty ($customer)) {
				throw new Exception ('El cliente no está registrado en la pasarela de pagos');
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
				throw new Exception ('El método de pago suministrado no está asociado al cliente');
			}

			$this->setDefaultPaymentMethod ($customer, $paymentMethodId);
		}

		/**
		 * @param string $instanceCode
		 * @param Payment[] $payments
		 *
		 * @return Payment[]
		 *
		 */
		public function synchronizeInstancePayments ($instanceCode, $payments) {
			if (empty ($payments)) {
				return $payments;
			}

			$customer = $this->fetchCustomerById ($instanceCode);
			if (!empty ($customer)) {
				$paymentMethod = $this->getDefaultPaymentMethod ($customer);
			} else {
				$paymentMethod = null;
			}

			$today = date_create ('today');
			$results = array ();
			foreach ($payments as $payment) {
				try {
					if (empty ($customer)) {
						throw new Exception ('El cliente no está registrado en la pasarela de pagos');
					} else if (!isset ($paymentMethod->token)) {
						throw new Exception ('El cliente no tiene asociado un método de pago por defecto');
					}

					$paymentType = $payment->getType ();
					if ($paymentType == Payment::TYPE_TRANSACTION) {
						$result = $this->updateTransactionPayment ($customer, $payment);
					} else if ($paymentType == Payment::TYPE_SUBSCRIPTION) {
						$result = $this->updateSubscriptionPayment ($customer, $payment);
					} else {
						$result = $payment;
					}
					$results [] = $result;
				} catch (Exception $e) {
					if ($payment->getDueDate () < $today) {
						$payment->setStatus (Payment::STATUS_PAST_DUE);
					}
					$results [] = $payment->setLastErrorMessage ($e->getMessage ());
				}
			}
			return $results;
		}

		/**
		 * @param string $instanceCode
		 * @param float $price
		 * @param integer $billingDayOfMonth
		 *
		 * @return \Braintree\Subscription|null
		 * @throws Exception
		 */
		public function updateInstanceSubscription ($instanceCode, $price, $billingDayOfMonth) {
			$customer = $this->fetchCustomerById ($instanceCode);
			if (empty ($customer)) {
				throw new Exception ('El cliente no está registrado en la pasarela de pagos');
			}

			$paymentMethod = $this->getDefaultPaymentMethod ($customer);
			if (empty ($customer)) {
				throw new Exception ('El cliente no tiene asociado un método de pago por defecto');
			}

			$subscription      = $this->getDefaultSubscription ($customer);
			$subscriptionPrice = isset ($subscription->price) ? round (doubleval ($subscription->price), 2) : 0.0;
			$paymentAmount     = round ($price, 2);
			if (empty ($subscription)) {
				return $this->registerSubscription ($paymentMethod->token, $paymentAmount, $billingDayOfMonth);
			} else if ($subscriptionPrice != $paymentAmount) {
				return $this->updateSubscription (isset ($subscription->id) ? $subscription->id : null, $paymentAmount);
			} else {
				return $subscription;
			}
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
